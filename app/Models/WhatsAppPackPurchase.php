<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppPackPurchase extends Model
{
    protected $table = 'whatsapp_pack_purchases';

    protected $fillable = [
        'shop_id',
        'user_id',
        'whatsapp_pack_id',
        'pack_name',
        'credits',
        'amount',
        'razorpay_order_id',
        'razorpay_payment_id',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'credits' => 'integer',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
