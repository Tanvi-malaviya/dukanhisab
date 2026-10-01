<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\AuditLog;

class TranslationSettingController extends Controller
{
    protected string $enPath;
    protected string $guPath;
    protected string $hiPath;

    public function __construct()
    {
        $this->enPath = base_path('lang/en.json');
        $this->guPath = base_path('lang/gu.json');
        $this->hiPath = base_path('lang/hi.json');
    }

    /**
     * Read all translations from JSON files.
     */
    protected function loadTranslations(): array
    {
        $en = file_exists($this->enPath) ? json_decode(file_get_contents($this->enPath), true) ?? [] : [];
        $gu = file_exists($this->guPath) ? json_decode(file_get_contents($this->guPath), true) ?? [] : [];
        $hi = file_exists($this->hiPath) ? json_decode(file_get_contents($this->hiPath), true) ?? [] : [];

        return [$en, $gu, $hi];
    }

    /**
     * Save translations array back to JSON files with 2-space indentation.
     */
    protected function saveJsonFile(string $filePath, array $data): bool
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        // Convert 4-space to 2-space indentation to match repository conventions
        $json = preg_replace_callback('/^( +)/m', function ($matches) {
            return str_repeat(' ', (int) (strlen($matches[1]) / 2));
        }, $json);

        return @file_put_contents($filePath, $json . "\n", LOCK_EX) !== false;
    }

    /**
     * Return the translation files the web server cannot write to.
     */
    protected function unwritableFiles(): array
    {
        return array_values(array_map('basename', array_filter(
            [$this->enPath, $this->guPath, $this->hiPath],
            fn ($path) => file_exists($path) ? !is_writable($path) : !is_writable(dirname($path))
        )));
    }

    protected function unwritableMessage(array $files): string
    {
        return 'Cannot save: ' . implode(', ', $files) . ' in the lang/ folder is not writable by the web server. Fix the file permissions on the server and try again.';
    }

    /**
     * Display list of translations with search, pagination and stats.
     */
    public function index(Request $request)
    {
        [$en, $gu, $hi] = $this->loadTranslations();

        // Get union of all keys
        $allKeys = array_unique(array_merge(array_keys($en), array_keys($gu), array_keys($hi)));
        sort($allKeys);

        $search = trim($request->input('search', ''));
        $filter = $request->input('filter', 'all');
        $perPage = (int) $request->input('per_page', 50);
        if (!in_array($perPage, [25, 50, 100, 200])) {
            $perPage = 50;
        }

        // Map into collection
        $rows = [];
        foreach ($allKeys as $k) {
            $enVal = $en[$k] ?? '';
            $guVal = $gu[$k] ?? '';
            $hiVal = $hi[$k] ?? '';

            // Apply filter
            if ($filter === 'missing') {
                if ($enVal !== '' && $guVal !== '' && $hiVal !== '') {
                    continue;
                }
            }

            // Apply search filter
            if ($search !== '') {
                $searchLower = mb_strtolower($search);
                $kMatch = str_contains(mb_strtolower($k), $searchLower);
                $enMatch = str_contains(mb_strtolower($enVal), $searchLower);
                $guMatch = str_contains(mb_strtolower($guVal), $searchLower);
                $hiMatch = str_contains(mb_strtolower($hiVal), $searchLower);

                if (!$kMatch && !$enMatch && !$guMatch && !$hiMatch) {
                    continue;
                }
            }

            $rows[] = [
                'key' => $k,
                'en'  => $enVal,
                'gu'  => $guVal,
                'hi'  => $hiVal,
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

        $stats = [
            'total_keys' => count($allKeys),
            'en_count'   => count(array_filter($en, fn($v) => trim($v) !== '')),
            'gu_count'   => count(array_filter($gu, fn($v) => trim($v) !== '')),
            'hi_count'   => count(array_filter($hi, fn($v) => trim($v) !== '')),
        ];

        return view('admin.settings.translations', [
            'translations' => $paginated,
            'stats'        => $stats,
            'search'       => $search,
            'filter'       => $filter,
            'perPage'      => $perPage,
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
            'translations.*.en' => 'nullable|string',
            'translations.*.gu' => 'nullable|string',
            'translations.*.hi' => 'nullable|string',
        ]);

        if ($unwritable = $this->unwritableFiles()) {
            return back()->withInput()->with('error', $this->unwritableMessage($unwritable));
        }

        [$en, $gu, $hi] = $this->loadTranslations();

        $submitted = $request->input('translations', []);
        $updatedCount = 0;

        foreach ($submitted as $row) {
            $key = trim($row['key']);
            if ($key === '') continue;

            $en[$key] = $row['en'] ?? '';
            $gu[$key] = $row['gu'] ?? '';
            $hi[$key] = $row['hi'] ?? '';
            $updatedCount++;
        }

        $this->saveJsonFile($this->enPath, $en);
        $this->saveJsonFile($this->guPath, $gu);
        $this->saveJsonFile($this->hiPath, $hi);

        AuditLog::log("Updated {$updatedCount} platform translation entries (Bulk)", [
            'count' => $updatedCount
        ]);

        return back()->with('success', "Successfully saved {$updatedCount} translations.");
    }

    /**
     * AJAX update for a single translation row.
     */
    public function updateSingle(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'en'  => 'nullable|string',
            'gu'  => 'nullable|string',
            'hi'  => 'nullable|string',
        ]);

        if ($unwritable = $this->unwritableFiles()) {
            return response()->json([
                'status'  => 'error',
                'message' => $this->unwritableMessage($unwritable),
            ], 500);
        }

        $key = trim($request->input('key'));
        [$en, $gu, $hi] = $this->loadTranslations();

        $en[$key] = $request->input('en', '') ?? '';
        $gu[$key] = $request->input('gu', '') ?? '';
        $hi[$key] = $request->input('hi', '') ?? '';

        $this->saveJsonFile($this->enPath, $en);
        $this->saveJsonFile($this->guPath, $gu);
        $this->saveJsonFile($this->hiPath, $hi);

        AuditLog::log("Updated translation for key: {$key}", [
            'key' => $key,
            'en'  => $en[$key],
            'gu'  => $gu[$key],
            'hi'  => $hi[$key],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Saved successfully for '{$key}'!",
            'key'     => $key,
        ]);
    }

    /**
     * Add a brand new translation key.
     */
    public function storeKey(Request $request)
    {
        $request->validate([
            'new_key' => 'required|string|regex:/^[a-zA-Z0-9_\-\.]+$/|max:100',
            'new_en'  => 'nullable|string',
            'new_gu'  => 'nullable|string',
            'new_hi'  => 'nullable|string',
        ]);

        if ($unwritable = $this->unwritableFiles()) {
            return back()->withInput()->with('error', $this->unwritableMessage($unwritable));
        }

        $key = trim($request->input('new_key'));
        [$en, $gu, $hi] = $this->loadTranslations();

        $en[$key] = $request->input('new_en', '') ?? '';
        $gu[$key] = $request->input('new_gu', '') ?? '';
        $hi[$key] = $request->input('new_hi', '') ?? '';

        $this->saveJsonFile($this->enPath, $en);
        $this->saveJsonFile($this->guPath, $gu);
        $this->saveJsonFile($this->hiPath, $hi);

        AuditLog::log("Added new translation key: {$key}");

        return back()->with('success', "New translation key '{$key}' created successfully.");
    }
}
