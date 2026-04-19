@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
@php
    $breaks = $breaks ?? $attendance->breaks ?? collect();
    $displayBreaks = $breaks->values();
    $breakRowCount = $displayBreaks->count() + 1;
@endphp

<div class="attendanceDetail">

    <h2 class="pageTitle">
        <span class="pageTitle__bar"></span>
        勤怠詳細
    </h2>

    <form method="POST" action="{{ route('admin_attendance_update', ['id' => $attendance->id]) }}" class="attendanceDetail__form">
        @csrf

        <div class="attendanceDetail__card">
            <div class="attendanceDetail__row">
                <div class="attendanceDetail__label">名 前</div>
                <div class="attendanceDetail__value attendanceDetail__value--text">
                    {{ $user->name }}
                </div>
            </div>

            <div class="attendanceDetail__row">
                <div class="attendanceDetail__label">日 付</div>
                <div class="attendanceDetail__value attendanceDetail__value--date">
                    <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
                    <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}</span>
                </div>
            </div>

            <div class="attendanceDetail__row">
                <div class="attendanceDetail__label">出 勤・退 勤</div>
                <div class="attendanceDetail__value">
                    <div class="attendanceDetail__timeGroup">
                        <input
                            type="text"
                            name="clock_in"
                            class="attendanceDetail__timeInput"
                            value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}"
                        >
                        <span class="attendanceDetail__separator">〜</span>
                        <input
                            type="text"
                            name="clock_out"
                            class="attendanceDetail__timeInput"
                            value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}"
                        >
                    </div>

                    @error('clock_in')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                    @error('clock_out')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                    @error('attendance_time')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @for($i = 0; $i < $breakRowCount; $i++)
                @php
                    $break = $displayBreaks[$i] ?? null;
                    $label = $i === 0 ? '休 憩' : '休 憩 ' . ($i + 1);
                @endphp

                <div class="attendanceDetail__row">
                    <div class="attendanceDetail__label">{{ $label }}</div>
                    <div class="attendanceDetail__value">
                        <div class="attendanceDetail__timeGroup">
                            <input
                                type="text"
                                name="breaks[{{ $i }}][break_start]"
                                class="attendanceDetail__timeInput"
                                value="{{ old("breaks.$i.break_start", optional(optional($break)->break_start)->format('H:i')) }}"
                            >
                            <span class="attendanceDetail__separator">〜</span>
                            <input
                                type="text"
                                name="breaks[{{ $i }}][break_end]"
                                class="attendanceDetail__timeInput"
                                value="{{ old("breaks.$i.break_end", optional(optional($break)->break_end)->format('H:i')) }}"
                            >
                        </div>

                        @if($break)
                            <input type="hidden" name="breaks[{{ $i }}][id]" value="{{ $break->id }}">
                        @endif

                        @error("breaks.$i.break_start")
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error("breaks.$i.break_end")
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error("breaks.$i.break_time")
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error("breaks.$i.break_end_time")
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endfor

            <div class="attendanceDetail__row attendanceDetail__row--textarea">
                <div class="attendanceDetail__label">備 考</div>
                <div class="attendanceDetail__value">
                    <textarea
                        name="note"
                        class="attendanceDetail__textarea"
                    >{{ old('note', $attendance->note) }}</textarea>

                    @error('note')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="attendanceDetail__buttonWrap">
            <button type="submit" class="btn attendanceDetail__submit">
                修正
            </button>
        </div>
    </form>
</div>
@endsection