<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'note' => ['required'],
            'breaks' => ['array'],
        ];

        $breaks = $this->input('breaks', []);

        foreach ($breaks as $i => $break) {
            $hasStart = !empty($break['break_start'] ?? null);
            $hasEnd = !empty($break['break_end'] ?? null);

            if ($hasStart || $hasEnd) {
                $rules["breaks.$i.break_start"] = ['required', 'date_format:H:i'];
                $rules["breaks.$i.break_end"] = ['required', 'date_format:H:i'];
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_in.date_format' => '出勤時間は00:00形式で入力してください',

            'clock_out.required' => '退勤時間を入力してください',
            'clock_out.date_format' => '退勤時間は00:00形式で入力してください',

            'breaks.*.break_start.required' => '休憩開始時間を入力してください',
            'breaks.*.break_start.date_format' => '休憩開始時間は00:00形式で入力してください',

            'breaks.*.break_end.required' => '休憩終了時間を入力してください',
            'breaks.*.break_end.date_format' => '休憩終了時間は00:00形式で入力してください',

            'note.required' => '備考を入力してください',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            if (
                $validator->errors()->has('clock_in') ||
                $validator->errors()->has('clock_out')
            ) {
                return;
            }

            $clockIn = Carbon::createFromFormat('H:i', $this->clock_in);
            $clockOut = Carbon::createFromFormat('H:i', $this->clock_out);

            if ($clockIn->gte($clockOut)) {
                $validator->errors()->add(
                    'attendance_time',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            foreach ($this->input('breaks', []) as $i => $break) {
                $start = $break['break_start'] ?? null;
                $end = $break['break_end'] ?? null;

                if (empty($start) && empty($end)) {
                    continue;
                }

                if (
                    $validator->errors()->has("breaks.$i.break_start") ||
                    $validator->errors()->has("breaks.$i.break_end")
                ) {
                    continue;
                }

                $breakStart = Carbon::createFromFormat('H:i', $start);
                $breakEnd = Carbon::createFromFormat('H:i', $end);

                if (
                    $breakStart->lt($clockIn) ||
                    $breakStart->gt($clockOut) ||
                    $breakStart->gt($breakEnd)
                ) {
                    $validator->errors()->add(
                        "breaks.$i.break_time",
                        '休憩時間が不適切な値です'
                    );
                }

                if ($breakEnd->gt($clockOut)) {
                    $validator->errors()->add(
                        "breaks.$i.break_end_time",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        });
    }
}