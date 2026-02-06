<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\DisabledDate;
use App\Models\TimeSlot;
use App\Models\Appointment;
use App\Models\ConflictAttempt;
use App\Models\User;

echo "=== Testing Appointment Logic on Disabled Date ===" . PHP_EOL;

$user = User::where('is_admin', false)->first();
if (!$user) {
    $user = User::first();
}
echo "Using user: " . $user->email . " (ID: " . $user->id . ")" . PHP_EOL;

$timeSlot = TimeSlot::where('is_active', true)->first();
echo "Using time slot ID: " . $timeSlot->id . PHP_EOL;

$testDate = '2026-02-08';
echo "Testing date: " . $testDate . PHP_EOL;

$isDateDisabled = DisabledDate::whereDate('date', $testDate)
    ->where(function ($query) use ($timeSlot) {
        $query->whereNull('time_slot_id')
            ->orWhere('time_slot_id', $timeSlot->id);
    })
    ->exists();

echo "Is date disabled? " . ($isDateDisabled ? 'YES' : 'NO') . PHP_EOL;
echo "Conflict count before: " . ConflictAttempt::count() . PHP_EOL;

if ($isDateDisabled) {
    echo PHP_EOL . "Simulating API call - creating conflict attempt..." . PHP_EOL;
    ConflictAttempt::create([
        'user_id' => $user->id,
        'time_slot_id' => $timeSlot->id,
        'attempted_date' => $testDate,
        'reason' => 'Date is disabled',
    ]);
    echo "Conflict logged!" . PHP_EOL;
    echo "Response would be: 422 - This date is disabled and not available for appointments." . PHP_EOL;
} else {
    echo "Date is NOT disabled - appointment would be created normally." . PHP_EOL;
}

echo PHP_EOL . "Conflict count after: " . ConflictAttempt::count() . PHP_EOL;

$latestConflict = ConflictAttempt::latest()->first();
if ($latestConflict) {
    echo PHP_EOL . "Latest conflict attempt:" . PHP_EOL;
    echo "  ID: " . $latestConflict->id . PHP_EOL;
    echo "  Date: " . $latestConflict->attempted_date . PHP_EOL;
    echo "  Reason: " . $latestConflict->reason . PHP_EOL;
}

