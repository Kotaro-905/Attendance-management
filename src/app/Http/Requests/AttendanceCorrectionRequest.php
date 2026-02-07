<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authミドルウェア前提
    }

    public function rules(): array
    {
        return [
            'attendance_id' => ['required', 'integer', 'exists:attendances,id'],
            'clock_in_at'   => ['nullable', 'date_format:H:i'],
            'clock_out_at'  => ['nullable', 'date_format:H:i'],

            'breaks'         => ['nullable', 'array'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end'   => ['nullable', 'date_format:H:i'],

            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_in_at.date_format'   => '出勤時間は「HH:MM」形式で入力してください。',
            'clock_out_at.date_format'  => '退勤時間は「HH:MM」形式で入力してください。',
            'breaks.*.start.date_format' => '休憩開始時間は「HH:MM」形式で入力してください。',
            'breaks.*.end.date_format'  => '休憩終了時間は「HH:MM」形式で入力してください。',
            'reason.required'           => '備考を記入してください。',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {

            // validated() でもOKだけど、nullable項目が落ちることがあるので all() 推奨
            $data = $this->all();

            $clockIn  = !empty($data['clock_in_at'])  ? Carbon::createFromFormat('H:i', $data['clock_in_at'])  : null;
            $clockOut = !empty($data['clock_out_at']) ? Carbon::createFromFormat('H:i', $data['clock_out_at']) : null;

            // ① 出勤 >= 退勤
            if ($clockIn && $clockOut && $clockIn >= $clockOut) {
                $validator->errors()->add('clock_out_at', '出勤時間もしくは退勤時間が不適切な値です。');
            }

            // ②③ 休憩チェック
            foreach (($data['breaks'] ?? []) as $index => $b) {
                $start = !empty($b['start']) ? Carbon::createFromFormat('H:i', $b['start']) : null;
                $end   = !empty($b['end'])   ? Carbon::createFromFormat('H:i', $b['end'])   : null;

                // 休憩開始が勤務時間外（出勤前 / 退勤後）
                if ($start && $clockIn && $start < $clockIn) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です。');
                }
                if ($start && $clockOut && $start > $clockOut) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です。');
                }

                // 休憩終了が勤務時間外（出勤前 / 退勤後）
                if ($end && $clockIn && $end < $clockIn) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間が不適切な値です。');
                }
                if ($end && $clockOut && $end > $clockOut) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間が不適切な値です。');
                }

                // ③ 休憩終了 <= 休憩開始
                if ($start && $end && $start >= $end) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間が不適切な値です。');
                }
            }
        });
    }
}
