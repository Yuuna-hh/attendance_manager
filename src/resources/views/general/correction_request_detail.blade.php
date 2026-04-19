@extends('layouts.app')

@section('title', '申請詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
@php
    $breakCorrections = $correction->breakCorrections->values();
@endphp

<div class="attendanceDetail">

    <h2 class="pageTitle">
        <span class="pageTitle__bar"></span>
        勤怠詳細
    </h2>

    <div class="attendanceDetail__form">
        <div class="attendanceDetail__card">
            <div class="attendanceDetail__row">
                <div class="attendanceDetail__label">名 前</div>
                <div class="attendanceDetail__value attendanceDetail__value--text">
                    {{ $correction->user->name }}
                </div>
            </div>

            <div class="attendanceDetail__row">
                <div class="attendanceDetail__label">日 付</div>
                <div class="attendanceDetail__value attendanceDetail__value--date">
                    <span>{{ \Carbon\Carbon::parse($correction->attendance->work_date)->format('Y年') }}</span>
                    <span>{{ \Carbon\Carbon::parse($correction->attendance->work_date)->format('n月j日') }}</span>
                </div>
            </div>

            <div class="attendanceDetail__row">
                <div class="attendanceDetail__label">出 勤・退 勤</div>
                <div class="attendanceDetail__value">
                    <div class="attendanceDetail__timeGroup">
                        <span class="attendanceDetail__timeDisplay">
                            {{ optional($correction->requested_clock_in)->format('H:i') }}
                        </span>
                        <span class="attendanceDetail__separator">〜</span>
                        <span class="attendanceDetail__timeDisplay">
                            {{ optional($correction->requested_clock_out)->format('H:i') }}
                        </span>
                    </div>
                </div>
            </div>

            @foreach($breakCorrections as $index => $breakCorrection)
                <div class="attendanceDetail__row">
                    <div class="attendanceDetail__label">
                        {{ $index === 0 ? '休 憩' : '休 憩 ' . ($index + 1) }}
                    </div>
                    <div class="attendanceDetail__value">
                        <div class="attendanceDetail__timeGroup">
                            <span class="attendanceDetail__timeDisplay">
                                {{ optional($breakCorrection->requested_break_start)->format('H:i') }}
                            </span>
                            <span class="attendanceDetail__separator">〜</span>
                            <span class="attendanceDetail__timeDisplay">
                                {{ optional($breakCorrection->requested_break_end)->format('H:i') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="attendanceDetail__row attendanceDetail__row--textarea">
                <div class="attendanceDetail__label">備 考</div>
                <div class="attendanceDetail__value attendanceDetail__value--text">
                    {{ $correction->requested_note }}
                </div>
            </div>
        </div>

        <p class="attendanceDetail__readonly-message">
            *承認待ちのため修正はできません。
        </p>
    </div>
</div>
@endsection