<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemTranslation extends Model
{
    protected $table = 'system_translations';

    protected $fillable = [
        'locale',
        'key',
        'value',
    ];

    /**
     * Scope query to a specific locale.
     */
    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }
}
