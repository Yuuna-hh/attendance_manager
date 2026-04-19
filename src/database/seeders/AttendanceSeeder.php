<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'general')->get();

        foreach ($users as $index => $user) {
            for ($i = 1; $i <= 5; $i++) {
                $workDate = Carbon::now()->subDays($i)->toDateString();

                Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $workDate,
                    'clock_in' => Carbon::parse($workDate . ' 09:00'),
                    'clock_out' => Carbon::parse($workDate . ' 18:00'),
                    'status' => 'finished',
                    'note' => $i === 2 && $index === 0 ? '電車遅延のため少し遅れて出勤' : null,
                ]);
            }
        }
    }
}