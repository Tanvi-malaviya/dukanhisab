<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    protected $fillable = [
        'type', // banner, interstitial, native, announcement
        'title',
        'image_url',
        'target_url',
        'script_code',
        'status', // active, inactive
        'clicks',
        'views',
    ];

    protected $casts = [
        'clicks' => 'integer',
        'views' => 'integer',
    ];

    public function incrementViews(): void
    {
        $this->increment('views');
    }

    public function incrementClicks(): void
    {
        $this->increment('clicks');
    }
}
