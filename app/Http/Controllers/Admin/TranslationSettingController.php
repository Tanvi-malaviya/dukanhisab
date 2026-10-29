<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\AuditLog;
use App\Services\TranslationService;

class TranslationSettingController extends Controller
{
    /**
     * All 22 official languages supported by DukanHisab platform.
     */
    public const LANGUAGES = [
        'en'  => ['name' => 'English',    'native' => 'English'],
        'gu'  => ['name' => 'Gujarati',   'native' => 'ગુજરાતી'],
        'hi'  => ['name' => 'Hindi',      'native' => 'हिन्दी'],
        'mr'  => ['name' => 'Marathi',    'native' => 'मराठी'],
        'bn'  => ['name' => 'Bengali',    'native' => 'বাংলা'],
        'ta'  => ['name' => 'Tamil',      'native' => 'தமிழ்'],
        'te'  => ['name' => 'Telugu',     'native' => 'తెలుగు'],
        'kn'  => ['name' => 'Kannada',    'native' => 'ಕನ್ನಡ'],
        'ml'  => ['name' => 'Malayalam',  'native' => 'മലയാളം'],
        'pa'  => ['name' => 'Punjabi',    'native' => 'ਪੰਜਾਬੀ'],
        'or'  => ['name' => 'Odia',       'native' => 'ଓଡ଼ିଆ'],
        'ur'  => ['name' => 'Urdu',       'native' => 'اردو'],
        'ne'  => ['name' => 'Nepali',     'native' => 'नेपाली'],
        'sa'  => ['name' => 'Sanskrit',   'native' => 'संस्कृतम्'],
        'sd'  => ['name' => 'Sindhi',     'native' => 'سنڌي'],
        'kok' => ['name' => 'Konkani',    'native' => 'कोंकणी'],
        'mai' => ['name' => 'Maithili',   'native' => 'मैथिली'],
        'ks'  => ['name' => 'Kashmiri',   'native' => 'کٲشُر'],
        'doi' => ['name' => 'Dogri',      'native' => 'डोगरी'],
        'mni' => ['name' => 'Manipuri',   'native' => 'মৈতৈলোন্'],
        'sat' => ['name' => 'Santali',    'native' => 'ᱥᱟᱱᱛᱟᱲᱤ'],
        'brx' => ['name' => 'Bodo',       'native' => 'बर\''],
    ];

    /**
     * Get path for a specific language file.
     */
    protected function getLangPath(string $code): string
    {
        return base_path("lang/{$code}.json");
    }

    /**
     * Read all combined translations (base files + database overrides).
     */
    protected function loadAllTranslations(): array
    {
        $all = [];
        foreach (self::LANGUAGES as $code => $meta) {
            $all[$code] = TranslationService::getCombined($code);
        }
        return $all;
    }

    /**
     * Save translations array back to JSON files with 2-space indentation if writable.
     */
    protected function saveJsonFile(string $filePath, array $data): bool
    {
        try {
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            // Convert 4-space to 2-space indentation to match repository conventions
            $json = preg_replace_callback('/^( +)/m', function ($matches) {
                return str_repeat(' ', (int) (strlen($matches[1]) / 2));
            }, $json);

            return @file_put_contents($filePath, $json . "\n", LOCK_EX) !== false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Display list of translations with search, language selector, pagination and stats.
     */
    public function index(Request $request)
    {
        $allTranslations = $this->loadAllTranslations();
        $overridesMap = TranslationService::getAllOverridesMap();

        // Get union of all keys across all languages
        $allKeys = [];
        foreach ($allTranslations as $code => $trans) {
            $allKeys = array_merge($allKeys, array_keys($trans));
        }
        $allKeys = array_unique($allKeys);
        sort($allKeys);

        // Determine active languages to display in table columns (beside English base)
        $langsInput = $request->input('langs');
        if ($langsInput === 'all') {
            $activeLangs = array_values(array_filter(array_keys(self::LANGUAGES), fn($c) => $c !== 'en'));
        } elseif (is_string($langsInput) && !empty(trim($langsInput))) {
            $parts = array_filter(array_map('trim', explode(',', $langsInput)));
            $activeLangs = array_values(array_intersect($parts, array_keys(self::LANGUAGES)));
            if (empty($activeLangs)) {
                $activeLangs = ['gu', 'hi'];
            }
        } elseif (is_array($langsInput)) {
            $activeLangs = array_values(array_intersect($langsInput, array_keys(self::LANGUAGES)));
            if (empty($activeLangs)) {
                $activeLangs = ['gu', 'hi'];
            }
        } else {
            $activeLangs = ['gu', 'hi'];
        }

        // Always ensure English is available as reference
        $en = $allTranslations['en'] ?? [];

        $search = trim($request->input('search', ''));
        $filter = $request->input('filter', 'all');
        $perPage = (int) $request->input('per_page', 50);
        if (!in_array($perPage, [25, 50, 100, 200])) {
            $perPage = 50;
        }

        // Build filtered rows
        $rows = [];
        $searchLower = mb_strtolower($search);

        foreach ($allKeys as $k) {
            $enVal = $en[$k] ?? '';

            // Values for active displayed languages
            $vals = [];
            foreach ($activeLangs as $langCode) {
                $vals[$langCode] = $allTranslations[$langCode][$k] ?? '';
            }

            // Apply filter: missing in any active language
            if ($filter === 'missing') {
                $hasMissing = ($enVal === '');
                if (!$hasMissing) {
                    foreach ($vals as $v) {
                        if (trim((string)$v) === '') {
                            $hasMissing = true;
                            break;
                        }
                    }
                }
                if (!$hasMissing) {
                    continue;
                }
            }

            // Apply search filter (searches key, English, active languages, or all languages)
            if ($search !== '') {
                $match = str_contains(mb_strtolower($k), $searchLower)
                    || str_contains(mb_strtolower($enVal), $searchLower);

                if (!$match) {
                    // Check active languages first
                    foreach ($vals as $v) {
                        if (str_contains(mb_strtolower($v), $searchLower)) {
                            $match = true;
                            break;
                        }
                    }
                }

                // If still not matched, check all 22 languages so admin can find any word in any language
                if (!$match) {
                    foreach ($allTranslations as $code => $trans) {
                        if (isset($trans[$k]) && str_contains(mb_strtolower($trans[$k]), $searchLower)) {
                            $match = true;
                            break;
                        }
                    }
                }

                if (!$match) {
                    continue;
                }
            }

            $rows[] = [
                'key'    => $k,
                'en'     => $enVal,
                'values' => $vals,
            ];
        }

        // Pagination
        $page = LengthAwarePaginator::resolveCurrentPage();
        $total = count($rows);
        $offset = ($page - 1) * $perPage;
        $slicedItems = array_slice($rows, $offset, $perPage);

        $paginated = new LengthAwarePaginator(
            $slicedItems,
            $total,
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        // Stats calculation for all 22 languages
        $totalKeyCount = count($allKeys);
        $stats = [
            'total_keys' => $totalKeyCount,
            'languages'  => [],
        ];

        foreach (self::LANGUAGES as $code => $meta) {
            $trans = $allTranslations[$code] ?? [];
            $count = count(array_filter($trans, fn($v) => trim((string)$v) !== ''));
            $pct = $totalKeyCount > 0 ? round(($count / $totalKeyCount) * 100) : 0;

            $stats['languages'][$code] = [
                'name'    => $meta['name'],
                'native'  => $meta['native'],
                'count'   => $count,
                'percent' => $pct,
            ];
        }

        // Backward compatibility stats fields for EN, GU, HI
        $stats['en_count'] = $stats['languages']['en']['count'] ?? 0;
        $stats['gu_count'] = $stats['languages']['gu']['count'] ?? 0;
        $stats['hi_count'] = $stats['languages']['hi']['count'] ?? 0;

        return view('admin.settings.translations', [
            'translations'      => $paginated,
            'stats'             => $stats,
            'languages'         => self::LANGUAGES,
            'activeLangs'       => $activeLangs,
            'overridesMap'      => $overridesMap,
            'search'            => $search,
            'filter'            => $filter,
            'perPage'           => $perPage,
            'currentLangsParam' => implode(',', $activeLangs),
        ]);
    }

    /**
     * Bulk update translations submitted from the current page.
     */
    public function update(Request $request)
    {
        $request->validate([
            'translations' => 'required|array',
            'translations.*.key' => 'required|string',
        ]);

        $submitted = $request->input('translations', []);
        if (empty($submitted)) {
            return back()->with('info', 'No translations submitted.');
        }

        // Discover which languages are present in the submitted payload
        $touchedLangs = [];
        $sample = reset($submitted);
        if (isset($sample['values']) && is_array($sample['values'])) {
            $touchedLangs = array_keys($sample['values']);
        } else {
            foreach ($sample as $col => $val) {
                if ($col !== 'key' && isset(self::LANGUAGES[$col])) {
                    $touchedLangs[] = $col;
                }
            }
        }
        if (isset($sample['en'])) {
            $touchedLangs[] = 'en';
        }
        $touchedLangs = array_unique(array_intersect($touchedLangs, array_keys(self::LANGUAGES)));

        $bulkData = [];
        $updatedCount = 0;

        foreach ($submitted as $row) {
            $key = trim($row['key'] ?? '');
            if ($key === '') continue;

            // Handle en if submitted
            if (isset($row['en'])) {
                $bulkData['en'][$key] = $row['en'] ?? '';
            }

            // Handle values array format: row[values][gu] => ...
            if (isset($row['values']) && is_array($row['values'])) {
                foreach ($row['values'] as $langCode => $val) {
                    if (isset(self::LANGUAGES[$langCode])) {
                        $bulkData[$langCode][$key] = $val ?? '';
                    }
                }
            } else {
                // Direct column format: row[gu] => ...
                foreach ($touchedLangs as $langCode) {
                    if (isset($row[$langCode])) {
                        $bulkData[$langCode][$key] = $row[$langCode] ?? '';
                    }
                }
            }

            $updatedCount++;
        }

        // 1. Save to Database overrides (100% persistent across git pull/deployments)
        TranslationService::saveBulkOverrides($bulkData);

        // 2. Also try writing to local lang/*.json files if writable
        $allTranslations = $this->loadAllTranslations();
        foreach ($touchedLangs as $langCode) {
            $path = $this->getLangPath($langCode);
            if (is_writable($path) || (!file_exists($path) && is_writable(dirname($path)))) {
                $this->saveJsonFile($path, $allTranslations[$langCode] ?? []);
            }
        }

        $langNames = implode(', ', array_map(fn($c) => self::LANGUAGES[$c]['name'], $touchedLangs));
        AuditLog::log("Updated {$updatedCount} platform translation entries in [{$langNames}]", [
            'count'     => $updatedCount,
            'languages' => $touchedLangs,
        ]);

        return back()->with('success', "Successfully saved {$updatedCount} translations across [{$langNames}] to database.");
    }

    /**
     * AJAX update for a single translation row across displayed languages.
     */
    public function updateSingle(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
        ]);

        $key = trim($request->input('key'));
        $values = $request->input('values', []);

        // Also check direct language inputs: en, gu, hi, mr, etc.
        foreach (self::LANGUAGES as $code => $meta) {
            if ($request->has($code) && !isset($values[$code])) {
                $values[$code] = $request->input($code);
            }
        }

        if (empty($values)) {
            return response()->json(['status' => 'error', 'message' => 'No language values provided.'], 422);
        }

        $bulkData = [];
        foreach ($values as $code => $val) {
            if (isset(self::LANGUAGES[$code])) {
                $bulkData[$code][$key] = $val ?? '';
            }
        }

        // 1. Save to database overrides
        TranslationService::saveBulkOverrides($bulkData);

        // 2. Also mirror to JSON files if writable
        foreach ($values as $code => $val) {
            if (isset(self::LANGUAGES[$code])) {
                $path = $this->getLangPath($code);
                if (is_writable($path) || (!file_exists($path) && is_writable(dirname($path)))) {
                    $combined = TranslationService::getCombined($code);
                    $this->saveJsonFile($path, $combined);
                }
            }
        }

        AuditLog::log("Updated translation for key: {$key}", [
            'key'       => $key,
            'languages' => array_keys($values),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Saved successfully for '{$key}'!",
            'key'     => $key,
        ]);
    }

    /**
     * AJAX fetch all 22 translations for a specific key.
     */
    public function getKeyAll(Request $request)
    {
        $key = trim($request->input('key', ''));
        if ($key === '') {
            return response()->json(['status' => 'error', 'message' => 'Key is required.'], 400);
        }

        $allTranslations = $this->loadAllTranslations();
        $translations = [];

        foreach (self::LANGUAGES as $code => $meta) {
            $translations[$code] = [
                'code'   => $code,
                'name'   => $meta['name'],
                'native' => $meta['native'],
                'value'  => $allTranslations[$code][$key] ?? '',
            ];
        }

        return response()->json([
            'status'       => 'success',
            'key'          => $key,
            'translations' => $translations,
        ]);
    }

    /**
     * AJAX update all 22 translations for a specific key.
     */
    public function updateKeyAll(Request $request)
    {
        $request->validate([
            'key'          => 'required|string',
            'translations' => 'required|array',
        ]);

        $key = trim($request->input('key'));
        $submitted = $request->input('translations', []);
        $targetCodes = array_keys($submitted);

        $bulkData = [];
        foreach ($submitted as $code => $val) {
            if (isset(self::LANGUAGES[$code])) {
                $bulkData[$code][$key] = $val ?? '';
            }
        }

        // 1. Save to Database overrides
        TranslationService::saveBulkOverrides($bulkData);

        // 2. Also try writing to JSON files if writable
        foreach ($submitted as $code => $val) {
            if (isset(self::LANGUAGES[$code])) {
                $path = $this->getLangPath($code);
                if (is_writable($path) || (!file_exists($path) && is_writable(dirname($path)))) {
                    $combined = TranslationService::getCombined($code);
                    $this->saveJsonFile($path, $combined);
                }
            }
        }

        AuditLog::log("Updated all translations for key: {$key}", [
            'key'       => $key,
            'languages' => $targetCodes,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Successfully saved all translations for '{$key}'!",
            'key'     => $key,
        ]);
    }

    /**
     * Add a brand new translation key across languages.
     */
    public function storeKey(Request $request)
    {
        $request->validate([
            'new_key' => 'required|string|regex:/^[a-zA-Z0-9_\-\.]+$/|max:100',
            'new_en'  => 'nullable|string',
        ]);

        $key = trim($request->input('new_key'));
        $enVal = $request->input('new_en', '') ?? '';

        // Collect translations from new_translations array or direct inputs
        $translationsInput = $request->input('new_translations', []);
        if ($request->has('new_gu')) {
            $translationsInput['gu'] = $request->input('new_gu');
        }
        if ($request->has('new_hi')) {
            $translationsInput['hi'] = $request->input('new_hi');
        }

        $bulkData = [
            'en' => [$key => $enVal],
        ];

        foreach (self::LANGUAGES as $code => $meta) {
            if ($code === 'en') continue;
            $val = $translationsInput[$code] ?? $enVal;
            $bulkData[$code][$key] = $val ?? '';
        }

        // Save to Database overrides
        TranslationService::saveBulkOverrides($bulkData);

        // Also write to files if writable
        foreach ($bulkData as $code => $items) {
            $path = $this->getLangPath($code);
            if (is_writable($path) || (!file_exists($path) && is_writable(dirname($path)))) {
                $combined = TranslationService::getCombined($code);
                $this->saveJsonFile($path, $combined);
            }
        }

        AuditLog::log("Added new translation key: {$key} across all 22 languages");

        return back()->with('success', "New translation key '{$key}' created successfully across all 22 languages.");
    }

    /**
     * Clear all translation cache and reload fresh from disk.
     */
    public function clearCache()
    {
        TranslationService::clearAllCache();
        return back()->with('success', 'Translation cache refreshed successfully from disk and database.');
    }

    /**
     * AJAX reset an override back to the base Git default value.
     */
    public function destroyOverride(Request $request)
    {
        $request->validate([
            'key'  => 'required|string',
            'lang' => 'required|string',
        ]);

        $key = trim($request->input('key'));
        $lang = trim($request->input('lang'));

        TranslationService::deleteOverride($lang, $key);

        AuditLog::log("Reset translation override for key '{$key}' in [{$lang}] back to Git default");

        return response()->json([
            'status'     => 'success',
            'message'    => "Reset '{$key}' in [{$lang}] to default.",
            'base_value' => TranslationService::getBase($lang)[$key] ?? '',
        ]);
    }
}
