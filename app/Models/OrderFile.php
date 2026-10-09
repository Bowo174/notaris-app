<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFile extends Model
{
    protected $fillable = ['label', 'original_name', 'path', 'mime_type', 'size'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
