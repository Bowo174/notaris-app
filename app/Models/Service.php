<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'code',
        'name',
        'base_price',
        'estimated_days',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'estimated_days' => 'integer',
        ];
    }
}
