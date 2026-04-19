<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceBreakCorrection;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $requests = AttendanceCorrection::with(['attendance.user'])
            ->where('status', $status === 'approved' ? 'approved' : 'pending')
            ->latest()
            ->get();

        return view('admin.admin_correction_request_list', compact('requests', 'status'));
    }

    public function show($id)
    {
        $correction = AttendanceCorrection::with([
            'attendance.user',
            'breakCorrections',
        ])->findOrFail($id);

        return view('admin.admin_correction_request_approve', compact('correction'));
    }

    public function update(Request $request, $id)
    {
        $correction = AttendanceCorrection::with([
            'attendance.breaks',
            'breakCorrections',
        ])->findOrFail($id);

        if ($correction->status === 'approved') {
            return redirect()
                ->route('admin_correction_request_approve', [
                    'attendance_correct_request_id' => $correction->id,
                ])
                ->with('error', 'この申請はすでに承認済みです。');
        }

        DB::transaction(function () use ($correction) {
            $attendance = $correction->attendance;

            $attendance->update([
                'clock_in' => $correction->requested_clock_in,
                'clock_out' => $correction->requested_clock_out,
                'note' => $correction->requested_note,
            ]);

            BreakTime::where('attendance_id', $attendance->id)->delete();

            foreach ($correction->breakCorrections as $breakCorrection) {
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $breakCorrection->requested_break_start,
                    'break_end' => $breakCorrection->requested_break_end,
                ]);
            }

            $correction->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('admin_correction_request_approve', [
            'attendance_correct_request_id' => $correction->id,
        ])->with('message', '承認しました。');
    }
}