<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'time_slot_id' => $this->time_slot_id,
            'appointment_date' => $this->appointment_date->format('Y-m-d'),
            'status' => $this->status,
            'notes' => $this->notes,
            'rejection_reason' => $this->rejection_reason,
            'approved_at' => $this->approved_at?->toISOString(),
            'rejected_at' => $this->rejected_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'user' => new UserResource($this->whenLoaded('user')),
            'time_slot' => new TimeSlotResource($this->whenLoaded('timeSlot')),
            'can_be_modified' => $this->canBeModified(),
            'is_past' => $this->isPast(),
        ];
    }
}
