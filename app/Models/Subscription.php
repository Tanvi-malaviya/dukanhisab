<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    // Admin-only bookkeeping — never exposed to the shop-owner app/web (see AuthApiController,
    // SubscriptionApiController::current, DashboardApiController): from the user's side an
    // admin-granted subscription looks exactly like a purchased one.
    protected $hidden = ['granted_by_admin', 'admin_note'];

    protected $fillable = [
        'user_id',
        'shop_id',
        'plan_id',
        'status',
        'razorpay_subscription_id',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'granted_by_admin',
        'admin_note',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'granted_by_admin' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
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
