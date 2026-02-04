<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        $slots = [
            ['start_time' => '08:00', 'end_time' => '09:00'],
            ['start_time' => '09:00', 'end_time' => '10:00'],
            ['start_time' => '10:00', 'end_time' => '11:00'],
            ['start_time' => '11:00', 'end_time' => '12:00'],
            ['start_time' => '13:00', 'end_time' => '14:00'],
            ['start_time' => '14:00', 'end_time' => '15:00'],
            ['start_time' => '15:00', 'end_time' => '16:00'],
            ['start_time' => '16:00', 'end_time' => '17:00'],
        ];

        foreach ($slots as $slot) {
            TimeSlot::create([
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'is_active' => true,
            ]);
        }
    }
}
