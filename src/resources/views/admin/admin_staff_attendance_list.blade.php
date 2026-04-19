@extends('layouts.app')

@section('title', 'スタッフ別勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/staff_attendance_list.css') }}">
@endsection

@section('content')
<div class="staffAttendanceList">

    <h2 class="pageTitle">
        <span class="pageTitle__bar"></span>
        {{ $staff->name }}さんの勤怠
    </h2>

    <div class="staffAttendanceList__monthNav">
        <a
            href="{{ route('admin_staff_attendance_list', ['id' => $staff->id, 'month' => $prevMonth]) }}"
            class="staffAttendanceList__monthBtn"
        >
            ← 前月
        </a>

        <div class="staffAttendanceList__month">
            <label class="staffAttendanceList__calendar">
                <img
                    src="{{ asset('images/calendar.png') }}"
                    class="staffAttendanceList__calendarIcon"
                    alt="calendar"
                >
                <input
                    type="month"
                    value="{{ $currentMonth }}"
                    class="staffAttendanceList__monthInput"
                    onchange="location.href='{{ route('admin_staff_attendance_list', ['id' => $staff->id]) }}?month=' + this.value"
                >
            </label>
            {{ \Carbon\Carbon::parse($currentMonth)->format('Y/m') }}
        </div>

        @if($nextMonth)
            <a
                href="{{ route('admin_staff_attendance_list', ['id' => $staff->id, 'month' => $nextMonth]) }}"
                class="staffAttendanceList__monthBtn"
            >
                翌月 →
            </a>
        @else
            <span class="staffAttendanceList__monthBtn staffAttendanceList__monthBtn--disabled">
                翌月 →
            </span>
        @endif
    </div>

    <div class="staffAttendanceList__tableWrap">
        <table class="staffAttendanceList__table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($days as $day)
                    <tr>
                        <td>{{ $day['date']->format('m/d') }}({{ ['日', '月', '火', '水', '木', '金', '土'][$day['date']->dayOfWeek] }})</td>
                        <td>{{ optional($day['attendance']?->clock_in)->format('H:i') ?? '' }}</td>
                        <td>{{ optional($day['attendance']?->clock_out)->format('H:i') ?? '' }}</td>
                        <td>{{ $day['break_time'] }}</td>
                        <td>{{ $day['total_time'] }}</td>
                        <td>
                            @if($day['attendance'])
                                <a
                                    href="{{ route('admin_attendance_detail', ['id' => $day['attendance']->id]) }}"
                                    class="staffAttendanceList__detail"
                                >
                                    詳細
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="staffAttendanceList__buttonWrap">
        <a
            href="{{ route('admin_staff_attendance_csv', ['id' => $staff->id, 'month' => $currentMonth]) }}"
            class="btn staffAttendanceList__csvButton"
        >
            CSV出力
        </a>
    </div>

</div>
@endsection