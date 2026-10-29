<?php

namespace App\Services;

use App\Models\SystemTranslation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class TranslationService
{
    protected const CACHE_PREFIX = 'system_translations_';

    /**
     * Get base translations from Git-tracked lang/{$locale}.json.
     */
    public static function getBase(string $locale): array
    {
        $path = base_path("lang/{$locale}.json");
        if (!file_exists($path)) {
            return [];
        }

        $content = @file_get_contents($path);
        if ($content === false) {
            return [];
        }

        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get database overrides for a locale, cached for maximum performance.
     */
    public static function getOverrides(string $locale): array
    {
        return Cache::rememberForever(self::CACHE_PREFIX . "overrides_{$locale}", function () use ($locale) {
            try {
                if (!Schema::hasTable('system_translations')) {
                    return [];
                }

                return SystemTranslation::where('locale', $locale)
                    ->pluck('value', 'key')
                    ->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    /**
     * Get combined translations (base file merged with database overrides).
     * Database overrides always take precedence over static base files.
     */
    public static function getCombined(string $locale): array
    {
        return Cache::rememberForever(self::CACHE_PREFIX . "combined_{$locale}", function () use ($locale) {
            $base = self::getBase($locale);
            $overrides = self::getOverrides($locale);

            $merged = array_merge($base, $overrides);

            // Persist to storage/app/translations/ as an offline disk mirror
            self::saveToStorageMirror($locale, $merged);

            return $merged;
        });
    }

    /**
     * Get all active database overrides grouped by locale and key.
     */
    public static function getAllOverridesMap(): array
    {
        return Cache::remember(self::CACHE_PREFIX . 'all_overrides_map', 3600, function () {
            try {
                if (!Schema::hasTable('system_translations')) {
                    return [];
                }

                $all = SystemTranslation::all(['locale', 'key', 'value', 'updated_at']);
                $map = [];
                foreach ($all as $item) {
                    $map[$item->locale][$item->key] = [
                        'value'      => $item->value,
                        'updated_at' => $item->updated_at,
                    ];
                }
                return $map;
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    /**
     * Save a single translation override to database.
     */
    public static function saveOverride(string $locale, string $key, ?string $value): void
    {
        SystemTranslation::updateOrCreate(
            ['locale' => $locale, 'key' => $key],
            ['value' => $value ?? '']
        );

        self::clearLocaleCache($locale);
    }

    /**
     * Save multiple overrides across languages in a single batch.
     * $data format: ['gu' => ['key1' => 'val1', 'key2' => 'val2'], 'hi' => [...]]
     */
    public static function saveBulkOverrides(array $dataByLocale): void
    {
        \DB::transaction(function () use ($dataByLocale) {
            foreach ($dataByLocale as $locale => $keys) {
                if (!is_array($keys)) continue;

                foreach ($keys as $key => $val) {
                    $key = trim((string) $key);
                    if ($key === '') continue;

                    SystemTranslation::updateOrCreate(
                        ['locale' => $locale, 'key' => $key],
                        ['value' => (string) ($val ?? '')]
                    );
                }
            }
        });

        foreach (array_keys($dataByLocale) as $locale) {
            self::clearLocaleCache($locale);
        }
    }

    /**
     * Delete an override to revert back to the base Git translation.
     */
    public static function deleteOverride(string $locale, string $key): bool
    {
        $deleted = SystemTranslation::where('locale', $locale)->where('key', $key)->delete() > 0;
        if ($deleted) {
            self::clearLocaleCache($locale);
        }
        return $deleted;
    }

    /**
     * Load database overrides directly into Laravel's active translator component for JSON keys.
     */
    public static function loadIntoTranslator(string $locale): void
    {
        $overrides = self::getOverrides($locale);
        if (empty($overrides) || !app()->bound('translator')) {
            return;
        }

        try {
            $translator = app('translator');
            // Ensure base JSON strings are loaded first
            $translator->load('*', '*', $locale);

            // Merge database overrides directly into Laravel's loaded JSON dictionary
            $injectOverrides = \Closure::bind(function ($loc, $items) {
                $existing = $this->loaded['*']['*'][$loc] ?? [];
                $this->loaded['*']['*'][$loc] = array_merge($existing, $items);
            }, $translator, $translator);

            $injectOverrides($locale, $overrides);
        } catch (\Throwable $e) {
            // Graceful fallback
        }
    }

    /**
     * Clear caches when translations are updated.
     */
    public static function clearLocaleCache(string $locale): void
    {
        Cache::forget(self::CACHE_PREFIX . "overrides_{$locale}");
        Cache::forget(self::CACHE_PREFIX . "combined_{$locale}");
        Cache::forget(self::CACHE_PREFIX . 'all_overrides_map');

        // Re-generate the storage mirror file
        $base = self::getBase($locale);
        $overrides = self::getOverrides($locale);
        self::saveToStorageMirror($locale, array_merge($base, $overrides));

        // Reload into active translator if applicable
        if (app()->getLocale() === $locale) {
            self::loadIntoTranslator($locale);
        }
    }

    /**
     * Clear all translation caches.
     */
    public static function clearAllCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . 'all_overrides_map');
        foreach (\App\Http\Controllers\Admin\TranslationSettingController::LANGUAGES as $code => $meta) {
            Cache::forget(self::CACHE_PREFIX . "overrides_{$code}");
            Cache::forget(self::CACHE_PREFIX . "combined_{$code}");
        }
    }

    /**
     * Mirror combined translations to storage/app/translations/{$locale}.json.
     * This storage directory is .gitignored, so it survives git pull/checkout cleanly.
     */
    protected static function saveToStorageMirror(string $locale, array $data): void
    {
        try {
            $dir = storage_path('app/translations');
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true, true);
            }

            $filePath = $dir . DIRECTORY_SEPARATOR . "{$locale}.json";
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            @file_put_contents($filePath, $json, LOCK_EX);
        } catch (\Throwable $e) {
            // Non-critical mirror failure
        }
    }
}
