<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shop_id',
        'supplier_id',
        'purchase_number',
        'total_amount',
        'discount',
        'paid_amount',
        'payment_type',
        'purchase_date',
        'status',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::deleting(function ($purchase) {
            if ($purchase->isForceDeleting()) {
                throw new \Exception("Hard delete is blocked for posted purchase bills. Please cancel the purchase instead.");
            }
        });
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
