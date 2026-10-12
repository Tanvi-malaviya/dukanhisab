<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppWallet extends Model
{
    protected $table = 'whatsapp_wallets';

    protected $fillable = [
        'shop_id',
        'balance',
    ];

    protected $casts = [
        'balance' => 'integer',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
