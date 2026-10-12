<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentLink extends Model
{
    protected $fillable = [
        'token',
        'shop_id',
        'customer_id',
        'amount',
        'expires_at',
        'status',
        'claimed_utr',
        'claimed_amount',
        'claimed_at',
        'resolved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'claimed_amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'claimed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public const VALID_DAYS = 7;

    public static function issue(Shop $shop, Customer $customer, float $amount): self
    {
        return self::create([
            'token' => Str::random(40),
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'amount' => $amount,
            'expires_at' => now()->addDays(self::VALID_DAYS),
            'status' => 'open',
        ]);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /** upi:// intent that opens GPay / PhonePe / Paytm with the shop's UPI ID and amount filled in. */
    public function upiUri(float $amount): string
    {
        return 'upi://pay?' . http_build_query([
            'pa' => $this->shop->upi_id,
            'pn' => $this->shop->name,
            'am' => number_format($amount, 2, '.', ''),
            'cu' => 'INR',
            'tn' => 'Payment to ' . $this->shop->name,
        ], '', '&', PHP_QUERY_RFC3986);
    }
}
