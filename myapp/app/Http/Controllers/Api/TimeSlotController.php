<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use App\Models\DisabledDate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    public function index(): JsonResponse
    {
        $timeSlots = TimeSlot::where('is_active', true)
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'time_slots' => $timeSlots,
        ]);
    }

    public function available(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $date = $validated['date'];

        $isFullDayDisabled = DisabledDate::where('date', $date)
            ->whereNull('time_slot_id')
            ->exists();

        if ($isFullDayDisabled) {
            return response()->json([
                'available_slots' => [],
                'message' => 'This date is not available for appointments',
            ]);
        }

        $timeSlots = TimeSlot::where('is_active', true)
            ->orderBy('start_time')
            ->get()
            ->filter(function ($slot) use ($date) {
                return $slot->isAvailableOn($date);
            })
            ->values();

        return response()->json([
            'available_slots' => $timeSlots,
        ]);
    }

    public function disabledDates(Request $request): JsonResponse
    {
        $fromDate = $request->input('from_date', now()->toDateString());
        $toDate = $request->input('to_date', now()->addMonths(3)->toDateString());

        $disabledDates = DisabledDate::whereNull('time_slot_id')
            ->whereBetween('date', [$fromDate, $toDate])
            ->pluck('date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->values();

        return response()->json([
            'disabled_dates' => $disabledDates,
        ]);
    }
}
