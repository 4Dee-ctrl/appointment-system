<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    public function index(): JsonResponse
    {
        $timeSlots = TimeSlot::orderBy('start_time')->get();

        return response()->json([
            'time_slots' => $timeSlots,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['boolean'],
        ]);

        $timeSlot = TimeSlot::create($validated);

        return response()->json([
            'message' => 'Time slot created successfully',
            'time_slot' => $timeSlot,
        ], 201);
    }

    public function show(TimeSlot $timeSlot): JsonResponse
    {
        return response()->json([
            'time_slot' => $timeSlot,
        ]);
    }

    public function update(Request $request, TimeSlot $timeSlot): JsonResponse
    {
        $validated = $request->validate([
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i'],
            'is_active' => ['boolean'],
        ]);

        if (isset($validated['start_time']) && isset($validated['end_time'])) {
            if ($validated['end_time'] <= $validated['start_time']) {
                return response()->json([
                    'message' => 'End time must be after start time',
                ], 422);
            }
        }

        $timeSlot->update($validated);

        return response()->json([
            'message' => 'Time slot updated successfully',
            'time_slot' => $timeSlot,
        ]);
    }

    public function destroy(TimeSlot $timeSlot): JsonResponse
    {
        $hasAppointments = $timeSlot->appointments()->exists();

        if ($hasAppointments) {
            return response()->json([
                'message' => 'Cannot delete time slot with existing appointments. Consider deactivating it instead.',
            ], 422);
        }

        $timeSlot->delete();

        return response()->json([
            'message' => 'Time slot deleted successfully',
        ]);
    }

    public function toggle(TimeSlot $timeSlot): JsonResponse
    {
        $timeSlot->update([
            'is_active' => !$timeSlot->is_active,
        ]);

        return response()->json([
            'message' => 'Time slot status updated successfully',
            'time_slot' => $timeSlot,
        ]);
    }
}
