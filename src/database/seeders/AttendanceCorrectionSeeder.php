<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceCorrectionSeeder extends Seeder
{
    public function run(): void
    {
        $generalUsers = User::where('role', 'general')->get();
        $admin = User::where('role', 'admin')->first();

        if ($generalUsers->isEmpty()) {
            return;
        }

        $firstUserAttendance = Attendance::where('user_id', $generalUsers[0]->id)
            ->orderBy('work_date')
            ->first();

        $secondUserAttendance = Attendance::where('user_id', $generalUsers[1]->id ?? $generalUsers[0]->id)
            ->orderBy('work_date')
            ->skip(1)
            ->first();

        if ($firstUserAttendance) {
            AttendanceCorrection::create([
                'attendance_id' => $firstUserAttendance->id,
                'user_id' => $firstUserAttendance->user_id,
                'requested_clock_in' => Carbon::parse($firstUserAttendance->work_date)->setTime(9, 30),
                'requested_clock_out' => Carbon::parse($firstUserAttendance->work_date)->setTime(18, 30),
                'requested_note' => '打刻漏れのため修正申請',
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
            ]);
        }

        if ($secondUserAttendance) {
            AttendanceCorrection::create([
                'attendance_id' => $secondUserAttendance->id,
                'user_id' => $secondUserAttendance->user_id,
                'requested_clock_in' => Carbon::parse($secondUserAttendance->work_date)->setTime(8, 45),
                'requested_clock_out' => Carbon::parse($secondUserAttendance->work_date)->setTime(17, 45),
                'requested_note' => '業務開始時刻の修正',
                'status' => 'approved',
                'approved_by' => $admin?->id,
                'approved_at' => now(),
            ]);
        }
    }
}