<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProductPrice extends Model
{
    protected $fillable = [
        'shop_id',
        'customer_id',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
