<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:check
                            {lang? : Language code to inspect in detail (e.g. gu, brx, mr)}
                            {--export= : Export pending keys for a specific language code to JSON}
                            {--export-all : Export pending keys for all languages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit translation files and list pending / untranslated keys';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $langDir = base_path('lang');
        $enFile = $langDir . DIRECTORY_SEPARATOR . 'en.json';

        if (!file_exists($enFile)) {
            $this->error("Error: Base file {$enFile} not found!");
            return 1;
        }

        $enTranslations = json_decode(file_get_contents($enFile), true);
        if (!is_array($enTranslations)) {
            $this->error("Error: Failed to parse {$enFile} as JSON!");
            return 1;
        }

        $totalEnKeys = count($enTranslations);

        $languageMeta = [
            'en'  => ['name' => 'English',    'native' => 'English'],
            'gu'  => ['name' => 'Gujarati',   'native' => 'ગુજરાતી'],
            'hi'  => ['name' => 'Hindi',      'native' => 'हिन्दी'],
            'bn'  => ['name' => 'Bengali',    'native' => 'বাংলা'],
            'brx' => ['name' => 'Bodo',       'native' => 'बर\''],
            'doi' => ['name' => 'Dogri',      'native' => 'डोगरी'],
            'kn'  => ['name' => 'Kannada',    'native' => 'ಕನ್ನಡ'],
            'ks'  => ['name' => 'Kashmiri',   'native' => 'کٲشُر'],
            'kok' => ['name' => 'Konkani',    'native' => 'कोंकणी'],
            'mai' => ['name' => 'Maithili',   'native' => 'मैथिली'],
            'ml'  => ['name' => 'Malayalam',  'native' => 'മലയാളം'],
            'mni' => ['name' => 'Manipuri',   'native' => 'মৈতৈলোন্'],
            'mr'  => ['name' => 'Marathi',    'native' => 'मराठी'],
            'ne'  => ['name' => 'Nepali',     'native' => 'नेपाली'],
            'or'  => ['name' => 'Odia',       'native' => 'ଓଡ଼ିଆ'],
            'pa'  => ['name' => 'Punjabi',    'native' => 'ਪੰਜਾਬੀ'],
            'sa'  => ['name' => 'Sanskrit',   'native' => 'संस्कृतम्'],
            'sat' => ['name' => 'Santali',    'native' => 'ᱥᱟᱱᱛᱟᱲᱤ'],
            'sd'  => ['name' => 'Sindhi',     'native' => 'سنڌي'],
            'ta'  => ['name' => 'Tamil',      'native' => 'தமிழ்'],
            'te'  => ['name' => 'Telugu',     'native' => 'తెలుగు'],
            'ur'  => ['name' => 'Urdu',       'native' => 'اردو'],
        ];

        $whitelist = [
            'pos', 'gst', 'gstin', 'upi', 'sms', 'otp', 'id', 'hsn', 'sku', 'url', 'pdf',
            'app', 'whatsapp', 'facebook', 'instagram', 'twitter', 'razorpay', '2mb', 'rs'
        ];

        $files = glob($langDir . DIRECTORY_SEPARATOR . '*.json');
        $langCodes = [];
        foreach ($files as $file) {
            $code = basename($file, '.json');
            if (!str_starts_with($code, 'pending_')) {
                $langCodes[] = $code;
            }
        }
        sort($langCodes);

        $report = [];
        foreach ($langCodes as $code) {
            $filePath = $langDir . DIRECTORY_SEPARATOR . "{$code}.json";
            $content = json_decode(file_get_contents($filePath), true) ?? [];
            
            $missingKeys = [];
            $pendingKeys = [];
            $translatedKeys = [];

            foreach ($enTranslations as $key => $enVal) {
                if (!array_key_exists($key, $content)) {
                    $missingKeys[$key] = $enVal;
                } else {
                    $val = trim((string) $content[$key]);
                    if ($val === '') {
                        $missingKeys[$key] = $enVal;
                    } elseif ($code !== 'en' && $val === trim((string) $enVal) && !in_array(strtolower(trim($enVal)), $whitelist, true) && !in_array($key, ['upi_id', 'gstin_label', 'ifsc_code'], true) && !preg_match('/^[\d\s\p{P}₹$€%+\-*\/]+$/u', $enVal)) {
                        $pendingKeys[$key] = $enVal;
                    } else {
                        $translatedKeys[$key] = $val;
                    }
                }
            }

            $totalKeys = count($content);
            $translatedCount = count($translatedKeys);
            $pendingCount = count($pendingKeys);
            $missingCount = count($missingKeys);
            $percent = $totalEnKeys > 0 ? round(($translatedCount / $totalEnKeys) * 100, 1) : 0;

            $report[$code] = [
                'code'         => $code,
                'name'         => $languageMeta[$code]['name'] ?? ucfirst($code),
                'native'       => $languageMeta[$code]['native'] ?? $code,
                'total_keys'   => $totalKeys,
                'translated'   => $translatedCount,
                'pending'      => $pendingCount,
                'missing'      => $missingCount,
                'percent'      => $percent,
                'missing_keys' => $missingKeys,
                'pending_keys' => $pendingKeys,
            ];
        }

        if ($this->option('export-all')) {
            $count = 0;
            foreach ($report as $code => $data) {
                if ($code === 'en') continue;
                $combined = array_merge($data['missing_keys'], $data['pending_keys']);
                $outFile = $langDir . DIRECTORY_SEPARATOR . "pending_{$code}.json";
                file_put_contents($outFile, json_encode($combined, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $count++;
            }
            $this->info("✔ Exported pending translation files for {$count} languages to lang/pending_{code}.json!");
            return 0;
        }

        $exportCode = $this->option('export');
        if ($exportCode) {
            if (!isset($report[$exportCode])) {
                $this->error("Language code '{$exportCode}' not found!");
                return 1;
            }
            $combined = array_merge($report[$exportCode]['missing_keys'], $report[$exportCode]['pending_keys']);
            $outFile = $langDir . DIRECTORY_SEPARATOR . "pending_{$exportCode}.json";
            file_put_contents($outFile, json_encode($combined, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info("✔ Exported " . count($combined) . " pending keys for {$report[$exportCode]['name']} to {$outFile}");
            return 0;
        }

        $target = $this->argument('lang');
        if ($target) {
            if (!isset($report[$target])) {
                $this->error("Language code '{$target}' not found!");
                return 1;
            }
            $d = $report[$target];
            $this->line('');
            $this->info("TRANSLATION AUDIT: {$d['name']} ({$d['native']} - {$d['code']})");
            $this->line("Total Keys: {$totalEnKeys} | Translated: {$d['translated']} | Pending: {$d['pending']} | Missing: {$d['missing']} | Completion: {$d['percent']}%");
            $this->line(str_repeat('-', 70));

            if (!empty($d['missing_keys'])) {
                $this->warn("Missing Keys (" . count($d['missing_keys']) . "):");
                foreach ($d['missing_keys'] as $k => $v) {
                    $this->line("  - {$k} => \"{$v}\"");
                }
            }

            if (!empty($d['pending_keys'])) {
                $this->comment("Pending Keys (" . count($d['pending_keys']) . "):");
                $i = 0;
                foreach ($d['pending_keys'] as $k => $v) {
                    $i++;
                    if ($i <= 50) {
                        $this->line("  - {$k} => \"{$v}\"");
                    }
                }
                if ($i > 50) {
                    $this->line("  ... and " . ($i - 50) . " more pending keys. (Use --export={$d['code']} to export)");
                }
            }
            $this->line('');
            return 0;
        }

        // Table
        $tableRows = [];
        foreach ($report as $d) {
            $blocks = (int) round($d['percent'] / 10);
            $bar = str_repeat('█', $blocks) . str_repeat('░', 10 - $blocks);
            $tableRows[] = [
                $d['code'],
                $d['name'],
                $d['native'],
                $d['translated'],
                $d['pending'],
                $d['missing'],
                "{$d['percent']}% {$bar}",
            ];
        }

        $this->table(
            ['Code', 'Language', 'Native Name', 'Translated', 'Pending', 'Missing', 'Completion'],
            $tableRows
        );

        $this->comment("Run 'php artisan translations:check <lang>' to view pending keys (e.g. php artisan translations:check mr)");
        return 0;
    }
}
