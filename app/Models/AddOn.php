<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddOn extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'type',
        'image',
        'description',
        'price',
        'billing_period',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) return null;
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }
        return url('storage/' . ltrim($this->image, '/'));
    }

    public function userAddOns()
    {
        return $this->hasMany(UserAddOn::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'add_on_id');
    }
}
