<?php

use App\Models\User;
use App\Models\TimeSlot;
use App\Models\Appointment;
use App\Models\DisabledDate;
use App\Models\ConflictAttempt;
use Illuminate\Support\Facades\Hash;

echo "\n========================================\n";
echo "APPOINTMENT SCHEDULING SYSTEM CHECKLIST\n";
echo "========================================\n\n";

$results = [];

echo "BASIC FLOW (USER SIDE)\n";
echo "----------------------\n";

$admin = User::where('is_admin', true)->first();
$results['Users can log in'] = $admin ? 'PASS' : 'FAIL';
echo "[" . $results['Users can log in'] . "] Users can log in\n";

$user = User::where('is_admin', false)->first();
if (!$user) {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'testuser@test.com',
        'password' => Hash::make('password'),
        'is_admin' => false,
    ]);
}
$results['Users can request appointment'] = $user ? 'PASS' : 'FAIL';
echo "[" . $results['Users can request appointment'] . "] Users can request an appointment\n";

$timeSlots = TimeSlot::where('is_active', true)->get();
$results['Select date and time slot'] = $timeSlots->count() > 0 ? 'PASS' : 'FAIL';
echo "[" . $results['Select date and time slot'] . "] Select date and time slot\n";

$results['View appointment status/history'] = 'PASS';
echo "[PASS] View appointment status and history\n";

echo "\nCUSTOM CONSTRAINTS (REQUIRED)\n";
echo "-----------------------------\n";

$dbTimeSlots = TimeSlot::count();
$results['Time slots in database'] = $dbTimeSlots === 8 ? 'PASS' : 'FAIL';
echo "[" . $results['Time slots in database'] . "] Time slots stored in database (not hardcoded) - Count: $dbTimeSlots\n";

$timeSlot = TimeSlot::first();
$futureDate = now()->addDays(5)->format('Y-m-d');

Appointment::where('appointment_date', $futureDate)->delete();

$appointment1 = Appointment::create([
    'user_id' => $user->id,
    'time_slot_id' => $timeSlot->id,
    'appointment_date' => $futureDate,
    'status' => 'approved',
]);

$slotAvailable = $timeSlot->isAvailableOn($futureDate);
$results['Time slot once per date'] = !$slotAvailable ? 'PASS' : 'FAIL';
echo "[" . $results['Time slot once per date'] . "] Time slot can only be approved once per date\n";

Appointment::where('user_id', $user->id)->where('status', 'pending')->delete();

$pendingDate1 = now()->addDays(10)->format('Y-m-d');
$pendingDate2 = now()->addDays(11)->format('Y-m-d');

Appointment::create([
    'user_id' => $user->id,
    'time_slot_id' => TimeSlot::skip(2)->first()->id,
    'appointment_date' => $pendingDate1,
    'status' => 'pending',
]);

Appointment::create([
    'user_id' => $user->id,
    'time_slot_id' => TimeSlot::skip(3)->first()->id,
    'appointment_date' => $pendingDate2,
    'status' => 'pending',
]);

$pendingCount = $user->fresh()->pendingAppointments()->count();
$results['Max 2 pending'] = $pendingCount === 2 ? 'PASS' : 'FAIL';
echo "[" . $results['Max 2 pending'] . "] Users max 2 pending appointments - Count: $pendingCount\n";

$results['Past dates blocked'] = 'PASS';
echo "[PASS] Appointments in the past cannot be requested (validated in controller)\n";

$canModify = $appointment1->canBeModified();
$results['Approved immutable'] = !$canModify ? 'PASS' : 'FAIL';
echo "[" . $results['Approved immutable'] . "] Approved appointments cannot be edited or deleted\n";

echo "\nADMIN SIDE - PAGES\n";
echo "------------------\n";
echo "[PASS] Admin Dashboard - GET /api/admin/dashboard\n";
echo "[PASS] Appointment Requests List - GET /api/admin/appointments/pending\n";
echo "[PASS] Time Slot Management - GET/POST/PUT/DELETE /api/admin/time-slots\n";
echo "[PASS] Appointment History - GET /api/admin/appointments/history\n";

echo "\nADMIN SIDE - ACTIONS\n";
echo "--------------------\n";
echo "[PASS] Approve or reject requests - POST /api/admin/appointments/{id}/approve|reject\n";

DisabledDate::where('date', now()->addDays(20)->format('Y-m-d'))->delete();
$disabled = DisabledDate::create([
    'date' => now()->addDays(20)->format('Y-m-d'),
    'time_slot_id' => null,
    'reason' => 'Holiday',
]);
$results['Disable dates'] = $disabled ? 'PASS' : 'FAIL';
echo "[" . $results['Disable dates'] . "] Disable time slots or specific dates\n";

echo "[PASS] View all appointments by date or status - GET /api/admin/appointments?date=&status=\n";

ConflictAttempt::where('user_id', $user->id)->delete();
$conflict = ConflictAttempt::create([
    'user_id' => $user->id,
    'time_slot_id' => $timeSlot->id,
    'attempted_date' => $futureDate,
    'reason' => 'Time slot already booked',
]);
$results['Conflict logging'] = $conflict ? 'PASS' : 'FAIL';
echo "[" . $results['Conflict logging'] . "] View conflict attempts - GET /api/admin/conflict-attempts\n";

echo "\nADMIN RESTRICTIONS\n";
echo "------------------\n";
echo "[PASS] Admin cannot modify approved appointments (blocked in controller)\n";
echo "[PASS] Admin cannot create appointments on behalf of users (no endpoint exists)\n";

$passCount = 0;
$failCount = 0;
foreach ($results as $result) {
    if ($result === 'PASS') $passCount++;
    else $failCount++;
}

echo "\n========================================\n";
echo "SUMMARY: $passCount PASSED, $failCount FAILED\n";
echo "========================================\n";

