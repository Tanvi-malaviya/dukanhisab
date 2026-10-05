<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'category_id',
        'name',
        'barcode',
        'selling_price',
        'purchase_price',
        'stock',
        'low_stock_threshold',
        'available_for_sale',
        'available_for_purchase',
        'container_type_id',
        'containers_per_unit',
    ];

    protected $casts = [
        'available_for_sale' => 'boolean',
        'available_for_purchase' => 'boolean',
        'containers_per_unit' => 'integer',
    ];

    /**
     * The API accepts these blank (the web form shows a 0 purchase price as an empty box), but the
     * columns are NOT NULL, so a blank used to fail the save with a 500. Blank means the column default.
     */
    public function setPurchasePriceAttribute($value): void
    {
        $this->attributes['purchase_price'] = $value ?? 0;
    }

    public function setStockAttribute($value): void
    {
        $this->attributes['stock'] = $value ?? 0;
    }

    public function setLowStockThresholdAttribute($value): void
    {
        $this->attributes['low_stock_threshold'] = $value ?? 5;
    }

    /** Forms send null when no container is linked; the column is NOT NULL with a default of 1. */
    public function setContainersPerUnitAttribute($value): void
    {
        $this->attributes['containers_per_unit'] = max(1, (int) ($value ?? 1));
    }

    public function containerType()
    {
        return $this->belongsTo(ContainerType::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
