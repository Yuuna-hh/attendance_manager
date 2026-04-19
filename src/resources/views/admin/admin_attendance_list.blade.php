@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<div class="attendanceList">

    <h2 class="pageTitle">
        <span class="pageTitle__bar"></span>
        勤怠一覧
    </h2>

    <div class="attendanceList__monthNav">
        <a
            href="{{ route('admin_attendance_list', ['date' => $prevDate]) }}"
            class="attendanceList__monthBtn"
        >
            ← 前日
        </a>

        <div class="attendanceList__month">
            <label class="attendanceList__calendar">
                <img
                    src="{{ asset('images/calendar.png') }}"
                    class="attendanceList__calendarIcon"
                    alt="calendar"
                >
                <input
                    type="date"
                    value="{{ $currentDate }}"
                    class="attendanceList__monthInput"
                    onchange="location.href='{{ route('admin_attendance_list') }}?date=' + this.value"
                >
            </label>
            {{ \Carbon\Carbon::parse($currentDate)->format('Y/m/d') }}
        </div>

        <a
            href="{{ route('admin_attendance_list', ['date' => $nextDate]) }}"
            class="attendanceList__monthBtn"
        >
            翌日 →
        </a>
    </div>

    <div class="attendanceList__tableWrap">
        <table class="attendanceList__table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>

            <tbody>
                @forelse($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->user->name }}</td>
                        <td>{{ optional($attendance->clock_in)->format('H:i') ?? '' }}</td>
                        <td>{{ optional($attendance->clock_out)->format('H:i') ?? '' }}</td>
                        <td>{{ $attendance->break_time }}</td>
                        <td>{{ $attendance->total_time }}</td>
                        <td>
                            @if($attendance->id)
                                <a
                                    href="{{ route('admin_attendance_detail', ['id' => $attendance->id]) }}"
                                    class="attendanceList__detail"
                                >
                                    詳細
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="attendanceList__empty">
                            勤怠情報がありません
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection