<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BreakTimeSeeder extends Seeder
{
    public function run(): void
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            $workDate = Carbon::parse($attendance->work_date)->toDateString();

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => Carbon::parse($workDate . ' 12:00'),
                'break_end' => Carbon::parse($workDate . ' 13:00'),
            ]);

            if ($attendance->id % 3 === 0) {
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => Carbon::parse($workDate . ' 15:00'),
                    'break_end' => Carbon::parse($workDate . ' 15:15'),
                ]);
            }
        }
    }
}