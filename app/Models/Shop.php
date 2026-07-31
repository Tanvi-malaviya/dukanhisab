<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'logo',
        'signature',
        'email',
        'mobile',
        'address',
        'city',
        'state',
        'pincode',
        'gst_number',
        'invoice_prefix',
        'currency',
        'upi_id',
        'bank_details',
        'invoice_footer',
        'status',
        'active_plan_id',
        'website_settings',
    ];

    protected $casts = [
        'website_settings' => 'array',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function activePlan()
    {
        return $this->hasOneThrough(SubscriptionPlan::class, User::class, 'id', 'id', 'owner_id', 'active_plan_id');
    }

    public function subscriptions()
    {
        return $this->hasManyThrough(Subscription::class, User::class, 'id', 'user_id', 'owner_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function currentSubscription()
    {
        return $this->hasOneThrough(Subscription::class, User::class, 'id', 'user_id', 'owner_id', 'id')->latestOfMany('id');
    }
}
