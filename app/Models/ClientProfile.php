<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientProfile extends Model
{
    protected $fillable = [
        'name', 'nik', 'client_type', 'represented_party_name', 'phone', 'email', 'address',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
