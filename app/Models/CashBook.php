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
}
