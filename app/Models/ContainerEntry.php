<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContainerEntry extends Model
{
    protected $fillable = [
        'shop_id',
        'entry_number',
        'type',
        'customer_id',
        'sale_id',
        'deposit_amount',
        'refund_amount',
        'forfeit_amount',
        'net_amount',
        'settlement_method',
        'note',
        'entry_date',
        'reversal_of_id',
        'reversed_at',
        'created_by',
    ];

    protected $casts = [
        'deposit_amount' => 'float',
        'refund_amount' => 'float',
        'forfeit_amount' => 'float',
        'net_amount' => 'float',
        'entry_date' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function movements()
    {
        return $this->hasMany(ContainerMovement::class);
    }

    public function reversalOf()
    {
        return $this->belongsTo(ContainerEntry::class, 'reversal_of_id');
    }

    /** Entries that still count: not reversed, and not themselves a reversal. */
    public function scopeEffective($query)
    {
        return $query->whereNull('reversed_at')->where('type', '!=', 'reversal');
    }
}
