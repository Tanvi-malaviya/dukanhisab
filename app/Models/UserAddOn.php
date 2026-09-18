<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddOn extends Model
{
    protected $fillable = [
        'user_id',
        'shop_id',
        'add_on_id',
        'quantity',
        'status',
        'starts_at',
        'ends_at',
        'auto_renew',
        'razorpay_subscription_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'auto_renew' => 'boolean',
        'quantity' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function addOn()
    {
        return $this->belongsTo(AddOn::class, 'add_on_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_add_on_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->ends_at !== null && $this->ends_at->isPast());
    }
}
