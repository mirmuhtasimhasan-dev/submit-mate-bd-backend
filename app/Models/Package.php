<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'tier',
        'name',
        'slug',
        'description',
        'price',
        'turnaround_hours',
        'delivery_days',
        'features',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'turnaround_hours' => 'integer',
        'delivery_days' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getDeliveryTextAttribute(): string
    {
        if (! $this->turnaround_hours) {
            return $this->delivery_days ? $this->delivery_days . ' days' : 'Custom';
        }

        if ($this->turnaround_hours === 24) {
            return '1 day';
        }

        return $this->turnaround_hours . ' hours';
    }
}