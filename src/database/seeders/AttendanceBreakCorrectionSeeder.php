<?php

namespace Database\Seeders;

use App\Models\AttendanceBreakCorrection;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceBreakCorrectionSeeder extends Seeder
{
    public function run(): void
    {
        $corrections = AttendanceCorrection::with('attendance')->get();

        foreach ($corrections as $correction) {
            $workDate = Carbon::parse($correction->attendance->work_date)->toDateString();

            AttendanceBreakCorrection::create([
                'correction_request_id' => $correction->id,
                'requested_break_start' => Carbon::parse($workDate . ' 12:00'),
                'requested_break_end' => Carbon::parse($workDate . ' 13:00'),
            ]);

            if ($correction->status === 'approved') {
                AttendanceBreakCorrection::create([
                    'correction_request_id' => $correction->id,
                    'requested_break_start' => Carbon::parse($workDate . ' 15:00'),
                    'requested_break_end' => Carbon::parse($workDate . ' 15:10'),
                ]);
            }
        }
    }
}