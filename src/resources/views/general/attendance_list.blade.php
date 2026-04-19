@extends('layouts.app')

@section('title','勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')

<div class="attendanceList">

    <h2 class="pageTitle">
        <span class="pageTitle__bar"></span>
        勤怠一覧
    </h2>

    <!-- 月ナビ -->
    <div class="attendanceList__monthNav">

        <!-- 前月 -->
        @if($prevMonth)
        <a href="{{ route('attendance_list',['month'=>$prevMonth]) }}" class="attendanceList__monthBtn">
            ← 前月
        </a>
        @else
        <span></span>
        @endif

        <div class="attendanceList__month">
            <label class="attendanceList__calendar">
                <img
                    src="{{ asset('images/calendar.png') }}"
                    class="attendanceList__calendarIcon"
                    alt="calendar"
                >
                <input
                    type="month"
                    value="{{ $currentMonth }}"
                    class="attendanceList__monthInput"
                    onchange="location.href='?month=' + this.value"
                >
            </label>
            {{ \Carbon\Carbon::parse($currentMonth)->format('Y/m') }}
        </div>

        <!-- 翌月 -->
        @if($nextMonth)
        <a href="{{ route('attendance_list',['month'=>$nextMonth]) }}" class="attendanceList__monthBtn">
            翌月 →
        </a>
        @else
        <span></span>
        @endif

    </div>


    <!-- テーブル -->
    <div class="attendanceList__tableWrap">

        <table class="attendanceList__table">

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
                @php
                    $attendance = $day['attendance'];
                @endphp
                <tr>
                    <td>
                        {{ $day['date']->isoFormat('MM/DD(dd)') }}
                    </td>

                    <td>
                        {{ $attendance?->clock_in?->format('H:i') ?? '' }}
                    </td>

                    <td>
                        {{ $attendance?->clock_out?->format('H:i') ?? '' }}
                    </td>

                    <td>
                        {{ $day['break_time'] }}
                    </td>

                    <td>
                        {{ $day['total_time'] }}
                    </td>

                    <td>
                        @if($attendance)
                            <a href="{{ route('attendance_detail', $attendance->id) }}" class="attendanceList__detail">
                                詳細
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach

            </tbody>

        </table>

    </div>

</div>

@endsection