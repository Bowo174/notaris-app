<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'code',
        'service_type',
        'name',
        'base_price',
        'estimated_duration',
        'estimate_unit',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'estimated_duration' => 'integer',
        ];
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function workOrders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_services')
            ->withPivot(['service_code_snapshot', 'service_name_snapshot'])
            ->withTimestamps();
    }
}
