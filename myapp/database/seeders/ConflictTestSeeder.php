<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\DisabledDate;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ConflictTestSeeder extends Seeder
{
    public function run(): void
    {
        $approvedDate = Carbon::now()->addDays(7)->toDateString();
        $pendingDate1 = Carbon::now()->addDays(8)->toDateString();
        $pendingDate2 = Carbon::now()->addDays(9)->toDateString();
        $disabledDate = Carbon::now()->addDays(10)->toDateString();

        $testUser1 = User::firstOrCreate(
            ['email' => 'testuser1@test.com'],
            [
                'name' => 'Test User 1',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ]
        );

        $testUser2 = User::firstOrCreate(
            ['email' => 'testuser2@test.com'],
            [
                'name' => 'Test User 2',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ]
        );

        $timeSlots = TimeSlot::where('is_active', true)->get();

        if ($timeSlots->isEmpty()) {
            $timeSlots = collect([
                TimeSlot::create(['start_time' => '08:00', 'end_time' => '09:00', 'is_active' => true]),
                TimeSlot::create(['start_time' => '09:00', 'end_time' => '10:00', 'is_active' => true]),
                TimeSlot::create(['start_time' => '10:00', 'end_time' => '11:00', 'is_active' => true]),
                TimeSlot::create(['start_time' => '11:00', 'end_time' => '12:00', 'is_active' => true]),
                TimeSlot::create(['start_time' => '13:00', 'end_time' => '14:00', 'is_active' => true]),
                TimeSlot::create(['start_time' => '14:00', 'end_time' => '15:00', 'is_active' => true]),
                TimeSlot::create(['start_time' => '15:00', 'end_time' => '16:00', 'is_active' => true]),
                TimeSlot::create(['start_time' => '16:00', 'end_time' => '17:00', 'is_active' => true]),
            ]);
        }

        $slot1 = $timeSlots->first();
        $slot2 = $timeSlots->skip(1)->first();
        $slot3 = $timeSlots->skip(2)->first();

        Appointment::updateOrCreate(
            [
                'user_id' => $testUser1->id,
                'time_slot_id' => $slot1->id,
                'appointment_date' => $approvedDate,
            ],
            [
                'status' => 'approved',
                'notes' => 'Test: Already approved slot for conflict test',
            ]
        );

        Appointment::updateOrCreate(
            [
                'user_id' => $testUser2->id,
                'time_slot_id' => $slot2->id,
                'appointment_date' => $pendingDate1,
            ],
            [
                'status' => 'pending',
                'notes' => 'Test User 2 - Pending appointment 1',
            ]
        );

        Appointment::updateOrCreate(
            [
                'user_id' => $testUser2->id,
                'time_slot_id' => $slot3->id,
                'appointment_date' => $pendingDate2,
            ],
            [
                'status' => 'pending',
                'notes' => 'Test User 2 - Pending appointment 2',
            ]
        );

        DisabledDate::updateOrCreate(
            ['date' => $disabledDate],
            [
                'time_slot_id' => null,
                'reason' => 'Test: Disabled date for conflict test',
            ]
        );

        $inactiveSlot = TimeSlot::firstOrCreate(
            ['start_time' => '17:00', 'end_time' => '18:00'],
            ['is_active' => false]
        );

        if ($inactiveSlot->is_active) {
            $inactiveSlot->update(['is_active' => false]);
        }

        $this->command->info('');
        $this->command->info('=== CONFLICT TEST SCENARIOS CREATED ===');
        $this->command->info('');
        $this->command->info('TEST 1: Book an already approved slot');
        $this->command->info('  - Login: testuser2@test.com / password123');
        $this->command->info('  - Book: ' . $approvedDate . ' at ' . $slot1->start_time . '-' . $slot1->end_time);
        $this->command->info('  - Expected: "Time slot already booked"');
        $this->command->info('');
        $this->command->info('TEST 2: Exceed pending appointment limit');
        $this->command->info('  - Login: testuser2@test.com / password123');
        $this->command->info('  - This user already has 2 pending appointments');
        $this->command->info('  - Try to book any new appointment');
        $this->command->info('  - Expected: "Max pending appointments reached"');
        $this->command->info('');
        $this->command->info('TEST 3: Book on a disabled date');
        $this->command->info('  - Login: testuser1@test.com / password123');
        $this->command->info('  - Book: ' . $disabledDate . ' (any time slot)');
        $this->command->info('  - Expected: "Date or time slot is disabled"');
        $this->command->info('');
        $this->command->info('TEST 4: Book inactive time slot');
        $this->command->info('  - The slot 17:00-18:00 is inactive');
        $this->command->info('  - This requires API call since inactive slots are hidden');
        $this->command->info('');
        $this->command->info('ADMIN LOGIN: admin@admin.com / password');
        $this->command->info('Check conflicts at: /admin/conflicts');
        $this->command->info('');
    }
}
