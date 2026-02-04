<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisabledDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'time_slot_id',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function isFullDayDisabled(): bool
    {
        return is_null($this->time_slot_id);
    }
}
