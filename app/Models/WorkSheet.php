<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSheet extends Model
{
    protected $fillable = [
        'order_id', 'code', 'deed_date', 'deed_number', 'deed_name', 'party_one', 'party_two',
        'certificate_details', 'transaction_value', 'pbb_amount', 'bphtb_amount', 'pph_amount',
    ];

    protected function casts(): array
    {
        return [
            'deed_date' => 'date',
            'party_one' => 'array',
            'party_two' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(WorkSheetFile::class);
    }
}
