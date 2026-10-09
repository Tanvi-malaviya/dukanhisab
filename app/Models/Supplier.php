<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shop_id',
        'name',
        'mobile',
        'email',
        'due_amount',
        'whatsapp_opt_out',
    ];

    protected $casts = [
        'whatsapp_opt_out' => 'boolean',
        'last_whatsapp_reminder_at' => 'datetime',
    ];

    protected $appends = ['net_balance'];

    /**
     * Net supplier position:
     * > 0: shop owes the supplier (Due)
     * < 0: shop has paid advance to supplier
     * = 0: settled
     */
    public function getNetBalanceAttribute(): float
    {
        return round((float) ($this->due_amount ?? 0), 2);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function customProductPrices()
    {
        return $this->hasMany(SupplierProductPrice::class);
    }
}
