<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = ['Konsultasi', 'Dalam proses', 'Selesai'];

    protected $fillable = [
        'client_profile_id', 'service_id', 'other_service', 'created_by', 'client_name_snapshot',
        'service_code_snapshot', 'service_name_snapshot',
        'client_nik_snapshot', 'client_type_snapshot', 'represented_party_snapshot',
        'client_phone_snapshot', 'client_email_snapshot', 'client_address_snapshot', 'title', 'description',
        'object_location', 'related_parties', 'internal_notes', 'status',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class, 'client_profile_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function files(): HasMany
    {
        return $this->hasMany(OrderFile::class);
    }

    public function workSheet(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WorkSheet::class);
    }
}
