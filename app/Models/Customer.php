<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shop_id',
        'name',
        'mobile',
        'email',
        'due_amount',
        'credit_balance',
    ];

    protected $appends = ['net_balance'];

    /**
     * Net customer position:
     * > 0: customer owes the shop (Due)
     * < 0: customer has advance / store credit
     * = 0: settled
     */
    /** A blank amount from a form means 0; the column is NOT NULL, so null used to fail the save with a 500. */
    public function setDueAmountAttribute($value): void
    {
        $this->attributes['due_amount'] = $value ?? 0;
    }

    public function getNetBalanceAttribute(): float
    {
        return round((float) ($this->due_amount ?? 0) - (float) ($this->credit_balance ?? 0), 2);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class);
    }

    public function customProductPrices()
    {
        return $this->hasMany(CustomerProductPrice::class);
    }

    public function containerMovements()
    {
        return $this->hasMany(ContainerMovement::class);
    }
}
