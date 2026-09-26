<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientFile extends Model
{
    protected $fillable = [
        'label',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
