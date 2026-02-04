<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'time_slot_id' => ['required', 'exists:time_slots,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'time_slot_id.required' => 'Please select a time slot.',
            'time_slot_id.exists' => 'The selected time slot is invalid.',
            'appointment_date.required' => 'Please select a date.',
            'appointment_date.after_or_equal' => 'Appointments cannot be scheduled in the past.',
        ];
    }
}
