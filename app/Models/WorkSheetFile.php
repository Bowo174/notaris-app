<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkSheetFile extends Model
{
    protected $fillable = ['category', 'label', 'original_name', 'path', 'mime_type', 'size'];

    public function workSheet(): BelongsTo
    {
        return $this->belongsTo(WorkSheet::class);
    }
}
