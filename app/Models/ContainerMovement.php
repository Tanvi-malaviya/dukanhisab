<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContainerMovement extends Model
{
    protected $fillable = [
        'shop_id',
        'container_entry_id',
        'customer_id',
        'container_type_id',
        'sale_id',
        'lot_id',
        'kind',
        'quantity',
        'closed_quantity',
        'deposit_per_unit',
        'amount',
        'forfeit_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'closed_quantity' => 'integer',
        'deposit_per_unit' => 'float',
        'amount' => 'float',
        'forfeit_amount' => 'float',
    ];

    protected $appends = ['pending_quantity'];

    public function entry()
    {
        return $this->belongsTo(ContainerEntry::class, 'container_entry_id');
    }

    public function containerType()
    {
        return $this->belongsTo(ContainerType::class)->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function lot()
    {
        return $this->belongsTo(ContainerMovement::class, 'lot_id');
    }

    /** Containers from this lot still with the customer (issue lines only). */
    public function getPendingQuantityAttribute(): int
    {
        return $this->kind === 'issue' ? max(0, $this->quantity - $this->closed_quantity) : 0;
    }

    /** Issue lots still (partly) with the customer, from entries that have not been reversed. */
    public function scopeOpenLots($query)
    {
        return $query->where('kind', 'issue')
            ->whereColumn('closed_quantity', '<', 'quantity')
            ->whereHas('entry', fn ($q) => $q->whereNull('reversed_at'));
    }
}
