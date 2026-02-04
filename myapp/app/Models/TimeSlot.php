<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'is_active' => 'boolean',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function disabledDates(): HasMany
    {
        return $this->hasMany(DisabledDate::class);
    }

    public function isAvailableOn(string $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $isDisabled = DisabledDate::where('date', $date)
            ->where(function ($query) {
                $query->whereNull('time_slot_id')
                    ->orWhere('time_slot_id', $this->id);
            })
            ->exists();

        if ($isDisabled) {
            return false;
        }

        $hasApprovedAppointment = Appointment::where('time_slot_id', $this->id)
            ->whereDate('appointment_date', $date)
            ->where('status', 'approved')
            ->exists();

        return !$hasApprovedAppointment;
    }

    public function getFormattedTimeAttribute(): string
    {
        return $this->start_time->format('h:i A') . ' - ' . $this->end_time->format('h:i A');
    }
}
