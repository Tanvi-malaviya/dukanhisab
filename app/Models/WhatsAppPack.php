<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppPack extends Model
{
    protected $table = 'whatsapp_packs';

    protected $fillable = [
        'name',
        'credits',
        'price',
        'status',
    ];

    protected $casts = [
        'credits' => 'integer',
        'price' => 'decimal:2',
    ];
}
