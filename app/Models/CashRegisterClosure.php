<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegisterClosure extends Model
{
    protected $fillable = [
        'shop_id',
        'closed_by_user_id',
        'closing_date',
        'opening_balance',
        'cash_in',
        'cash_out',
        'expected_cash',
        'actual_cash',
        'difference',
        'denominations',
        'note',
    ];

    protected $casts = [
        'closing_date' => 'date',
        'opening_balance' => 'float',
        'cash_in' => 'float',
        'cash_out' => 'float',
        'expected_cash' => 'float',
        'actual_cash' => 'float',
        'difference' => 'float',
        'denominations' => 'array',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }
}
