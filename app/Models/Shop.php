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
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function activePlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'active_plan_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function currentSubscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }
}
