<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DisabledDate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisabledDateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DisabledDate::with('timeSlot');

        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('date', [$request->from_date, $request->to_date]);
        }

        $disabledDates = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'disabled_dates' => $disabledDates,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time_slot_id' => ['nullable', 'exists:time_slots,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $exists = DisabledDate::where('date', $validated['date'])
            ->where('time_slot_id', $validated['time_slot_id'] ?? null)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This date/time slot combination is already disabled',
            ], 422);
        }

        $disabledDate = DisabledDate::create($validated);
        $disabledDate->load('timeSlot');

        return response()->json([
            'message' => 'Date disabled successfully',
            'disabled_date' => $disabledDate,
        ], 201);
    }

    public function destroy(DisabledDate $disabledDate): JsonResponse
    {
        $disabledDate->delete();

        return response()->json([
            'message' => 'Date enabled successfully',
        ]);
    }

    public function disableFullDay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $exists = DisabledDate::where('date', $validated['date'])
            ->whereNull('time_slot_id')
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This date is already disabled',
            ], 422);
        }

        DisabledDate::where('date', $validated['date'])->delete();

        $disabledDate = DisabledDate::create([
            'date' => $validated['date'],
            'time_slot_id' => null,
            'reason' => $validated['reason'] ?? null,
        ]);

        return response()->json([
            'message' => 'Full day disabled successfully',
            'disabled_date' => $disabledDate,
        ], 201);
    }
}
