<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Append-only. Never updated or deleted once written — a correction is a new offsetting row,
 * matching how the app's own ledger/stock conventions already work everywhere else.
 */
class StockMovement extends Model
{
    protected $fillable = [
        'shop_id',
        'product_id',
        'type',
        'quantity_change',
        'resulting_stock',
        'reference_type',
        'reference_id',
        'note',
        'created_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
