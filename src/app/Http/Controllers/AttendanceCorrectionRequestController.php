<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrection;

class AttendanceCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->isAdmin()) {
            return app(AdminCorrectionRequestController::class)->index($request);
        }

        $status = $request->input('status', 'pending');

        $requests = AttendanceCorrection::with(['user', 'attendance'])
            ->where('user_id', auth()->id())
            ->where('status', $status === 'approved' ? 'approved' : 'pending')
            ->latest()
            ->get();

        return view('general.correction_request_list', compact('requests', 'status'));
    }

    public function show($attendance_correct_request_id)
    {
        $correction = AttendanceCorrection::with([
            'user',
            'attendance',
            'breakCorrections',
        ])
            ->where('user_id', auth()->id())
            ->findOrFail($attendance_correct_request_id);

        return view('general.correction_request_detail', compact('correction'));
    }
}