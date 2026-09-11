<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'customer_id',
        'sale_id',
        'credit_note_number',
        'total_amount',
        'used_amount',
        'remaining_balance',
        'status',
        'reason',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'used_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
