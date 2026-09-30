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
        'expense_category_id',
        'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function expenseCategory()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Scope for operational business expenses.
     * Excludes system-generated cash_out transactions such as purchases,
     * sales returns, cancellation reversals, customer/supplier payments, and bank contra transfers.
     */
    public function scopeExpenses($query)
    {
        return $query->where('type', 'cash_out')
            ->where(function ($q) {
                $q->where('reference_type', 'expense')
                  ->orWhere(function ($sub) {
                      $sub->whereNull('reference_type')
                          ->where('description', 'not like', 'Purchase:%')
                          ->where('description', 'not like', 'Return:%')
                          ->where('description', 'not like', 'Partial Return:%')
                          ->where('description', 'not like', 'Reversal%')
                          ->where('description', 'not like', 'Supplier Payment:%')
                          ->where('description', 'not like', 'Customer Payment:%')
                          ->where('description', 'not like', 'Bank Transfer:%')
                          ->where('description', 'not like', 'Contra:%');
                  });
            });
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
