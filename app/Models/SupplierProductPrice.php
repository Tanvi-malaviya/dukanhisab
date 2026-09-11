<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierProductPrice extends Model
{
    protected $fillable = [
        'shop_id',
        'supplier_id',
        'product_id',
        'custom_price',
    ];

    protected $casts = [
        'custom_price' => 'decimal:2',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
