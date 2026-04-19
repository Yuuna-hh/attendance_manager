<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAttendanceController extends Controller
{
    public function list(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $current = Carbon::parse($date);
        $prevDate = $current->copy()->subDay()->toDateString();
        $nextDate = $current->copy()->addDay()->toDateString();

        $users = User::where('role', 'general')
            ->orderBy('name')
            ->get();

        $attendanceMap = Attendance::with(['breaks', 'user'])
            ->whereDate('work_date', $current->toDateString())
            ->get()
            ->keyBy('user_id');

        $attendances = $users->map(function ($user) use ($attendanceMap) {
            $attendance = $attendanceMap->get($user->id);

            if (!$attendance) {
                return (object) [
                    'id' => null,
                    'user' => $user,
                    'clock_in' => null,
                    'clock_out' => null,
                    'break_time' => '',
                    'total_time' => '',
                ];
            }

            $breakSeconds = $attendance->breaks->sum(function ($break) {
                if (!$break->break_end) {
                    return 0;
                }

                return Carbon::parse($break->break_start)
                    ->diffInSeconds(Carbon::parse($break->break_end));
            });

            $breakMinutes = floor($breakSeconds / 60);

            $breakTime = $breakMinutes > 0
                ? sprintf('%02d:%02d', floor($breakMinutes / 60), $breakMinutes % 60)
                : '';

            $totalTime = '';
            if ($attendance->clock_in && $attendance->clock_out) {
                $workMinutes = Carbon::parse($attendance->clock_in)
                    ->diffInMinutes(Carbon::parse($attendance->clock_out));

                $totalMinutes = max(0, $workMinutes - $breakMinutes);

                $totalTime = sprintf(
                    '%02d:%02d',
                    floor($totalMinutes / 60),
                    $totalMinutes % 60
                );
            }

            $attendance->break_time = $breakTime;
            $attendance->total_time = $totalTime;

            return $attendance;
        });

        return view('admin.admin_attendance_list', [
            'attendances' => $attendances,
            'currentDate' => $current->toDateString(),
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
        ]);
    }

    public function detail($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);

        return view('admin.admin_attendance_detail', [
            'attendance' => $attendance,
            'user' => $attendance->user,
            'breaks' => $attendance->breaks,
        ]);
    }

    public function update(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        DB::transaction(function () use ($request, $attendance) {
            $workDate = Carbon::parse($attendance->work_date)->format('Y-m-d');

            $attendance->update([
                'clock_in' => Carbon::parse($workDate . ' ' . $request->clock_in),
                'clock_out' => Carbon::parse($workDate . ' ' . $request->clock_out),
                'note' => $request->note,
            ]);

            $submittedBreaks = collect($request->input('breaks', []))
                ->filter(function ($break) {
                    return !empty($break['break_start']) || !empty($break['break_end']);
                })
                ->values();

            BreakTime::where('attendance_id', $attendance->id)->delete();

            foreach ($submittedBreaks as $break) {
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => Carbon::parse($workDate . ' ' . $break['break_start']),
                    'break_end' => !empty($break['break_end'])
                        ? Carbon::parse($workDate . ' ' . $break['break_end'])
                        : null,
                ]);
            }
        });

        return redirect()->route('admin_attendance_list')->with('message', '勤怠を修正しました。');
    }

    public function staffList(Request $request, $id)
    {
        $staff = User::where('role', 'general')->findOrFail($id);

        $month = $request->input('month', now()->format('Y-m'));

        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $staff->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->with('breaks')
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)->format('Y-m-d');
            });

        $days = [];

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $attendance = $attendances->get($date->format('Y-m-d'));

            $breakTime = '';
            $totalTime = '';

            if ($attendance) {
                $breakSeconds = $attendance->breaks->sum(function ($break) {
                    if (!$break->break_end) {
                        return 0;
                    }

                    return Carbon::parse($break->break_start)
                        ->diffInSeconds(Carbon::parse($break->break_end));
                });

                $breakMinutes = floor($breakSeconds / 60);

                $breakTime = $breakMinutes > 0
                    ? sprintf('%02d:%02d', floor($breakMinutes / 60), $breakMinutes % 60)
                    : '';

                if ($attendance->clock_in && $attendance->clock_out) {
                    $workMinutes = Carbon::parse($attendance->clock_in)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out));

                    $totalMinutes = max(0, $workMinutes - $breakMinutes);

                    $totalTime = sprintf(
                        '%02d:%02d',
                        floor($totalMinutes / 60),
                        $totalMinutes % 60
                    );
                }
            }

            $days[] = [
                'date' => $date->copy(),
                'attendance' => $attendance,
                'break_time' => $breakTime,
                'total_time' => $totalTime,
            ];
        }

        $prevMonth = $start->copy()->subMonth()->format('Y-m');
        $nextMonth = $start->copy()->addMonth()->isFuture()
            ? null
            : $start->copy()->addMonth()->format('Y-m');

        return view('admin.admin_staff_attendance_list', [
            'staff' => $staff,
            'days' => $days,
            'currentMonth' => $month,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    public function exportCsv(Request $request, $id)
    {
        $staff = User::where('role', 'general')->findOrFail($id);

        $month = $request->input('month', now()->format('Y-m'));

        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $staff->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->with('breaks')
            ->orderBy('work_date')
            ->get();

        $fileName = $staff->name . '_' . $month . '_attendance.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return response()->stream(function () use ($attendances) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $attendance) {
                $breakSeconds = $attendance->breaks->sum(function ($break) {
                    if (!$break->break_end) {
                        return 0;
                    }

                    return Carbon::parse($break->break_start)
                        ->diffInSeconds(Carbon::parse($break->break_end));
                });

                $breakMinutes = floor($breakSeconds / 60);
                $breakTime = $breakMinutes > 0
                    ? sprintf('%02d:%02d', floor($breakMinutes / 60), $breakMinutes % 60)
                    : '';

                $totalTime = '';
                if ($attendance->clock_in && $attendance->clock_out) {
                    $workMinutes = Carbon::parse($attendance->clock_in)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out));

                    $totalMinutes = max(0, $workMinutes - $breakMinutes);

                    $totalTime = sprintf(
                        '%02d:%02d',
                        floor($totalMinutes / 60),
                        $totalMinutes % 60
                    );
                }

                fputcsv($handle, [
                    Carbon::parse($attendance->work_date)->format('Y/m/d'),
                    optional($attendance->clock_in)->format('H:i') ?? '',
                    optional($attendance->clock_out)->format('H:i') ?? '',
                    $breakTime,
                    $totalTime,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}