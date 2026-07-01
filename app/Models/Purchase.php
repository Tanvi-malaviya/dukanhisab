<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'shop_id',
        'supplier_id',
        'purchase_number',
        'total_amount',
        'payment_type',
        'purchase_date',
        'status',
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
    ];

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
