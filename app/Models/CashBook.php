<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashBook extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shop_id',
        'type', // cash_in, cash_out
        'amount',
        'payment_method', // cash, bank, upi
        'bank_account_id',
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

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    protected static function booted(): void
    {
        // Every Sale/Purchase/due-payment/bank-transfer call site that creates a bank or UPI entry
        // would otherwise need to remember to pass bank_account_id — one central default here
        // means none of them have to, and a future call site can't forget it either. An explicit
        // bank_account_id (e.g. a deliberate transfer against a specific account) is never overridden.
        static::creating(function (CashBook $cashBook) {
            if ($cashBook->bank_account_id === null && in_array($cashBook->payment_method, ['bank', 'upi'], true) && $cashBook->shop_id) {
                $cashBook->bank_account_id = BankAccount::defaultForShop($cashBook->shop_id)->id;
            }
        });
    }
}
