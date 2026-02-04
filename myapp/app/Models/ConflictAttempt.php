<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConflictAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'time_slot_id',
        'attempted_date',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'attempted_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }
}
