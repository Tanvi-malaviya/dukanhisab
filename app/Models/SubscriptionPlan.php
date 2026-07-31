<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_period',
        'features',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'plan_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'active_plan_id');
    }

    public function shops()
    {
        return $this->hasMany(User::class, 'active_plan_id');
    }
}
