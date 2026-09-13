<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'customer_id',
        'sale_number',
        'subtotal',
        'discount',
        'grand_total',
        'paid_amount',
        'store_credit',
        'payment_type',
        'status',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
        'sale_date',
        'paid_date',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'paid_date' => 'datetime',
    ];

    protected static function booted()
    {
        static::deleting(function ($sale) {
            if ($sale->isForceDeleting()) {
                throw new \Exception("Hard delete is blocked for posted sales invoices. Please cancel the sale instead.");
            }
        });
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class);
    }
}
