<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConflictAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConflictAttemptController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ConflictAttempt::with(['user', 'timeSlot']);

        if ($request->has('date')) {
            $query->where('attempted_date', $request->date);
        }

        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('attempted_date', [$request->from_date, $request->to_date]);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $conflicts = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($conflicts);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total' => ConflictAttempt::count(),
            'today' => ConflictAttempt::whereDate('created_at', now()->toDateString())->count(),
            'this_week' => ConflictAttempt::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count(),
            'by_reason' => ConflictAttempt::selectRaw('reason, count(*) as count')
                ->groupBy('reason')
                ->get(),
        ];

        return response()->json([
            'stats' => $stats,
        ]);
    }
}
