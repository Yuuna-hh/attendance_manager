<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceBreakCorrection;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', now()->toDateString())
            ->with('breaks')
            ->first();

        return view('general.attendance_index', compact('attendance'));
    }

    public function store(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', now()->toDateString())
            ->first();

        if (!$attendance) {
            $attendance = Attendance::create([
                'user_id' => auth()->id(),
                'work_date' => now()->toDateString(),
                'status' => Attendance::STATUS_OFF_DUTY,
            ]);
        }

        switch ($request->input('action')) {
            case 'clock_in':
                if ($attendance->clock_in) {
                    return redirect()->route('attendance_index');
                }

                $attendance->update([
                    'clock_in' => now(),
                    'status' => Attendance::STATUS_WORKING,
                ]);
                break;

            case 'clock_out':
                if ($attendance->clock_out) {
                    return redirect()->route('attendance_index');
                }

                $attendance->update([
                    'clock_out' => now(),
                    'status' => Attendance::STATUS_FINISHED,
                ]);

                return redirect()
                    ->route('attendance_index')
                    ->with('message', 'お疲れ様でした。');

            case 'break_start':
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => now(),
                ]);

                $attendance->update([
                    'status' => Attendance::STATUS_ON_BREAK,
                ]);
                break;

            case 'break_end':
                $break = BreakTime::where('attendance_id', $attendance->id)
                    ->whereNull('break_end')
                    ->latest()
                    ->first();

                if ($break) {
                    $break->update([
                        'break_end' => now(),
                    ]);

                    $attendance->update([
                        'status' => Attendance::STATUS_WORKING,
                    ]);
                }
                break;
        }

        return redirect()->route('attendance_index');
    }

    public function list(Request $request)
    {
        $user = auth()->user();
        $month = $request->input('month', now()->format('Y-m'));

        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->with('breaks')
            ->get()
            ->keyBy(fn ($attendance) => Carbon::parse($attendance->work_date)->format('Y-m-d'));

        $days = [];

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $attendance = $attendances->get($date->format('Y-m-d'));

            $breakTime = '';
            $totalTime = '';

            if ($attendance) {
                $breakSeconds = $attendance->breaks->sum(function ($break) {
                    if (is_null($break->break_end)) {
                        return 0;
                    }

                    return Carbon::parse($break->break_start)
                        ->diffInSeconds(Carbon::parse($break->break_end));
                });

                $breakMinutes = floor($breakSeconds / 60);

                if ($breakMinutes > 0) {
                    $breakTime = sprintf('%02d:%02d', floor($breakMinutes / 60), $breakMinutes % 60);
                }

                if ($attendance->clock_in && $attendance->clock_out) {
                    $workMinutes = Carbon::parse($attendance->clock_in)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out));

                    $totalMinutes = max(0, $workMinutes - $breakMinutes);

                    $totalTime = sprintf('%02d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);
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

        return view('general.attendance_list', [
            'days' => $days,
            'currentMonth' => $month,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    public function detail($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $pendingCorrection = \App\Models\AttendanceCorrection::where('attendance_id', $attendance->id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->latest()
            ->first();

        return view('general.attendance_detail', [
            'attendance' => $attendance,
            'user' => $attendance->user,
            'breaks' => $pendingCorrection?->breakCorrections ?? $attendance->breaks,
            'pendingCorrection' => $pendingCorrection,
            'isPendingCorrection' => (bool) $pendingCorrection,
            'displayNote' => $pendingCorrection?->requested_note ?? $attendance->note,
        ]);
    }

    public function update(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $pendingExists = AttendanceCorrection::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingExists) {
            return redirect()
                ->route('attendance_detail', ['id' => $attendance->id])
                ->with('error', '承認待ちのため修正はできません。');
        }

        DB::transaction(function () use ($request, $attendance) {
            $workDate = Carbon::parse($attendance->work_date)->format('Y-m-d');

            $correction = AttendanceCorrection::create([
                'attendance_id' => $attendance->id,
                'user_id' => auth()->id(),
                'requested_clock_in' => Carbon::parse($workDate . ' ' . $request->clock_in),
                'requested_clock_out' => Carbon::parse($workDate . ' ' . $request->clock_out),
                'requested_note' => $request->note,
                'status' => 'pending',
            ]);

            foreach ($request->input('breaks', []) as $break) {
                $breakStart = $break['break_start'] ?? null;
                $breakEnd = $break['break_end'] ?? null;

                if (empty($breakStart) && empty($breakEnd)) {
                    continue;
                }

                AttendanceBreakCorrection::create([
                    'correction_request_id' => $correction->id,
                    'requested_break_start' => $breakStart
                        ? Carbon::parse($workDate . ' ' . $breakStart)
                        : null,
                    'requested_break_end' => $breakEnd
                        ? Carbon::parse($workDate . ' ' . $breakEnd)
                        : null,
                ]);
            }
        });

        return redirect()->route('correction_request_list');
    }
}