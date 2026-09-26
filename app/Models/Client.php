<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'service_id',
        'other_service',
        'deed_title',
        'nik',
        'client_type',
        'phone',
        'email',
        'address',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ClientFile::class);
    }
}
