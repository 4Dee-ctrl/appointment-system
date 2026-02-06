<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConflictAttempt;
use App\Models\DisabledDate;
use App\Models\TimeSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $appointments = $request->user()
            ->appointments()
            ->with('timeSlot')
            ->orderBy('appointment_date', 'desc')
            ->get();

        return response()->json([
            'appointments' => $appointments,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'time_slot_id' => ['required', 'exists:time_slots,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $timeSlot = TimeSlot::findOrFail($validated['time_slot_id']);
        $date = $validated['appointment_date'];

        $pendingCount = $user->pendingAppointments()->count();
        if ($pendingCount >= 2) {
            $this->logConflict($user->id, $timeSlot->id, $date, 'Max pending appointments reached');
            return response()->json([
                'message' => 'You can only have a maximum of 2 pending appointments',
            ], 422);
        }

        if (!$timeSlot->is_active) {
            $this->logConflict($user->id, $timeSlot->id, $date, 'Time slot is inactive');
            return response()->json([
                'message' => 'This time slot is not available',
            ], 422);
        }

        $isDateDisabled = DisabledDate::where('date', $date)
            ->where(function ($query) use ($timeSlot) {
                $query->whereNull('time_slot_id')
                    ->orWhere('time_slot_id', $timeSlot->id);
            })
            ->exists();

        if ($isDateDisabled) {
            $this->logConflict($user->id, $timeSlot->id, $date, 'Date is disabled');
            return response()->json([
                'message' => 'This date is disabled and not available for appointments.',
            ], 422);
        }

        $hasApprovedAppointment = Appointment::where('time_slot_id', $timeSlot->id)
            ->where('appointment_date', $date)
            ->where('status', 'approved')
            ->exists();

        if ($hasApprovedAppointment) {
            $this->logConflict($user->id, $timeSlot->id, $date, 'Time slot already booked');
            return response()->json([
                'message' => 'This time slot is already booked for the selected date',
            ], 422);
        }

        $appointment = DB::transaction(function () use ($user, $validated) {
            return Appointment::create([
                'user_id' => $user->id,
                'time_slot_id' => $validated['time_slot_id'],
                'appointment_date' => $validated['appointment_date'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
            ]);
        });

        $appointment->load('timeSlot');

        return response()->json([
            'message' => 'Appointment request submitted successfully',
            'appointment' => $appointment,
        ], 201);
    }

    public function show(Request $request, Appointment $appointment): JsonResponse
    {
        if ($appointment->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $appointment->load('timeSlot');

        return response()->json([
            'appointment' => $appointment,
        ]);
    }

    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        if ($appointment->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!$appointment->canBeModified()) {
            return response()->json([
                'message' => 'This appointment cannot be modified',
            ], 422);
        }

        $validated = $request->validate([
            'time_slot_id' => ['sometimes', 'exists:time_slots,id'],
            'appointment_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if (isset($validated['time_slot_id']) || isset($validated['appointment_date'])) {
            $timeSlotId = $validated['time_slot_id'] ?? $appointment->time_slot_id;
            $date = $validated['appointment_date'] ?? $appointment->appointment_date->toDateString();
            $timeSlot = TimeSlot::findOrFail($timeSlotId);

            if (!$timeSlot->isAvailableOn($date)) {
                return response()->json([
                    'message' => 'The selected time slot is not available for this date',
                ], 422);
            }
        }

        $appointment->update($validated);
        $appointment->load('timeSlot');

        return response()->json([
            'message' => 'Appointment updated successfully',
            'appointment' => $appointment,
        ]);
    }

    public function destroy(Request $request, Appointment $appointment): JsonResponse
    {
        if ($appointment->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!$appointment->canBeModified()) {
            return response()->json([
                'message' => 'This appointment cannot be deleted',
            ], 422);
        }

        $appointment->delete();

        return response()->json([
            'message' => 'Appointment cancelled successfully',
        ]);
    }

    private function logConflict(int $userId, int $timeSlotId, string $date, string $reason): void
    {
        ConflictAttempt::create([
            'user_id' => $userId,
            'time_slot_id' => $timeSlotId,
            'attempted_date' => $date,
            'reason' => $reason,
        ]);
    }
}
