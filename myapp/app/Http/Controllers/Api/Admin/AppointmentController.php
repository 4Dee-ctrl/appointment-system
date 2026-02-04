<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Appointment::with(['user', 'timeSlot']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->where('appointment_date', $request->date);
        }

        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('appointment_date', [$request->from_date, $request->to_date]);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($appointments);
    }

    public function pending(): JsonResponse
    {
        $appointments = Appointment::with(['user', 'timeSlot'])
            ->pending()
            ->orderBy('appointment_date')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'appointments' => $appointments,
        ]);
    }

    public function approve(Appointment $appointment): JsonResponse
    {
        if ($appointment->isApproved()) {
            return response()->json([
                'message' => 'Appointment is already approved',
            ], 422);
        }

        if ($appointment->isPast()) {
            return response()->json([
                'message' => 'Cannot approve past appointments',
            ], 422);
        }

        $existingApproved = Appointment::where('time_slot_id', $appointment->time_slot_id)
            ->where('appointment_date', $appointment->appointment_date)
            ->where('status', 'approved')
            ->where('id', '!=', $appointment->id)
            ->exists();

        if ($existingApproved) {
            return response()->json([
                'message' => 'Another appointment is already approved for this time slot and date',
            ], 422);
        }

        $appointment->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $appointment->load(['user', 'timeSlot']);

        return response()->json([
            'message' => 'Appointment approved successfully',
            'appointment' => $appointment,
        ]);
    }

    public function reject(Request $request, Appointment $appointment): JsonResponse
    {
        if ($appointment->isApproved()) {
            return response()->json([
                'message' => 'Cannot reject approved appointments',
            ], 422);
        }

        if ($appointment->isRejected()) {
            return response()->json([
                'message' => 'Appointment is already rejected',
            ], 422);
        }

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $appointment->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'rejected_at' => now(),
        ]);

        $appointment->load(['user', 'timeSlot']);

        return response()->json([
            'message' => 'Appointment rejected successfully',
            'appointment' => $appointment,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $query = Appointment::with(['user', 'timeSlot'])
            ->whereIn('status', ['approved', 'rejected']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($appointments);
    }

    public function show(Appointment $appointment): JsonResponse
    {
        $appointment->load(['user', 'timeSlot']);

        return response()->json([
            'appointment' => $appointment,
        ]);
    }
}
