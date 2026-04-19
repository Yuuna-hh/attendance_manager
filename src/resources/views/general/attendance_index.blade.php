@extends('layouts.app')

@section('title', '勤怠登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance">

    <!-- 勤務状態ラベル（仮） -->
    @php
        use App\Models\Attendance;

        $status = $attendance->status ?? Attendance::STATUS_OFF_DUTY;

        $labels = [
            Attendance::STATUS_OFF_DUTY => '勤務外',
            Attendance::STATUS_WORKING  => '出勤中',
            Attendance::STATUS_ON_BREAK => '休憩中',
            Attendance::STATUS_FINISHED => '退勤済',
        ];
    @endphp

    <div class="attendance__status">
        <span class="attendance__badge attendance__badge--{{ $status }}">
            {{ $labels[$status] ?? '勤務外' }}
        </span>
    </div>

    <!-- 日付 -->
    <div class="attendance__date">
        {{ \Carbon\Carbon::now()->isoFormat('YYYY年M月D日(dd)') }}
    </div>

    <!-- 時計 -->
    <div class="attendance__time" id="clock">
        {{ now()->format('H:i') }}
    </div>

    <!-- ボタン -->
    <div class="attendance__action">

    @if(!$attendance || $attendance->status === \App\Models\Attendance::STATUS_OFF_DUTY)

        <form method="POST" action="{{ route('attendance_index') }}">
            @csrf
            <input type="hidden" name="action" value="clock_in">
            <button type="submit" class="attendance__button">出勤</button>
        </form>

    @elseif($attendance->status === \App\Models\Attendance::STATUS_WORKING)

        <div style="display:flex; gap:20px; justify-content:center;">
            <form method="POST" action="{{ route('attendance_index') }}">
                @csrf
                <input type="hidden" name="action" value="clock_out">
                <button type="submit" class="attendance__button">退勤</button>
            </form>

            <form method="POST" action="{{ route('attendance_index') }}">
                @csrf
                <input type="hidden" name="action" value="break_start">
                <button type="submit" class="attendance__button attendance__button--sub">休憩入</button>
            </form>
        </div>

    @elseif($attendance->status === \App\Models\Attendance::STATUS_ON_BREAK)

        <form method="POST" action="{{ route('attendance_index') }}">
            @csrf
            <input type="hidden" name="action" value="break_end">
            <button type="submit" class="attendance__button attendance__button--sub">休憩戻</button>
        </form>

    @elseif($attendance && $attendance->status === \App\Models\Attendance::STATUS_FINISHED)
        <p class="attendance__message">
            お疲れ様でした。
        </p>

    @endif

    </div>

</div>
@endsection

@section('js')
<script>
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('clock').textContent = hours + ':' + minutes;
    }

    setInterval(updateClock, 1000);
    updateClock();
</script>
@endsection