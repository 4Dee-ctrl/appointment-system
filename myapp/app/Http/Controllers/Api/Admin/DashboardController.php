<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConflictAttempt;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $stats = [
            'total_users' => User::where('is_admin', false)->count(),
            'pending_appointments' => Appointment::pending()->count(),
            'approved_appointments' => Appointment::approved()->count(),
            'rejected_appointments' => Appointment::rejected()->count(),
            'today_appointments' => Appointment::forDate(now()->toDateString())->approved()->count(),
            'upcoming_appointments' => Appointment::upcoming()->approved()->count(),
            'conflict_attempts_today' => ConflictAttempt::whereDate('created_at', now()->toDateString())->count(),
        ];

        $recentAppointments = Appointment::with(['user', 'timeSlot'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentConflicts = ConflictAttempt::with(['user', 'timeSlot'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_appointments' => $recentAppointments,
            'recent_conflicts' => $recentConflicts,
        ]);
    }
}
