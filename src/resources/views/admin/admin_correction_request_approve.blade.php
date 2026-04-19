@extends('layouts.app')

@section('title', '申請詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
@php
    $breakCorrections = $correction->breakCorrections->values();
    $breakRowCount = $breakCorrections->count() + 1;
    $isApproved = $correction->status === 'approved';
@endphp

<div class="attendanceDetail">

    <h2 class="pageTitle">
        <span class="pageTitle__bar"></span>
        勤怠詳細
    </h2>

    <form
        method="POST"
        action="{{ route('admin_correction_request_approve_update', ['attendance_correct_request_id' => $correction->id]) }}"
        class="attendanceDetail__form"
    >
        @csrf

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

            @for($i = 0; $i < $breakRowCount; $i++)
                @php
                    $breakCorrection = $breakCorrections[$i] ?? null;
                    $label = $i === 0 ? '休 憩' : '休 憩 ' . ($i + 1);
                    $start = optional(optional($breakCorrection)->requested_break_start)->format('H:i');
                    $end = optional(optional($breakCorrection)->requested_break_end)->format('H:i');
                @endphp

                <div class="attendanceDetail__row">
                    <div class="attendanceDetail__label">{{ $label }}</div>
                    <div class="attendanceDetail__value">
                        <div class="attendanceDetail__timeGroup">
                            <span class="attendanceDetail__timeDisplay">{{ $start }}</span>

                            @if($start && $end)
                                <span class="attendanceDetail__separator">〜</span>
                            @endif

                            <span class="attendanceDetail__timeDisplay">{{ $end }}</span>
                        </div>
                    </div>
                </div>
            @endfor

            <div class="attendanceDetail__row attendanceDetail__row--textarea">
                <div class="attendanceDetail__label">備 考</div>
                <div class="attendanceDetail__value attendanceDetail__value--text">
                    {{ $correction->requested_note }}
                </div>
            </div>
        </div>

        <div class="attendanceDetail__buttonWrap">
            @if($isApproved)
                <button type="button" class="btn attendanceDetail__submit attendanceDetail__submit--approved" disabled>
                    承認済み
                </button>
            @else
                <button type="submit" class="btn attendanceDetail__submit">
                    承認
                </button>
            @endif
        </div>
    </form>
</div>
@endsection