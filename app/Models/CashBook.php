<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashBook extends Model
{
    protected $fillable = [
        'shop_id',
        'type', // cash_in, cash_out
        'amount',
        'payment_method', // cash, bank, upi
        'description',
        'reference_id',
        'reference_type',
        'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
