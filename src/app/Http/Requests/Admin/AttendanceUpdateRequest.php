<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in_at'  => ['nullable', 'date_format:H:i'],
            'clock_out_at' => ['nullable', 'date_format:H:i'],

            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end'   => ['nullable', 'date_format:H:i'],

            // FN029④：備考は必須
            'remarks' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            // date_format の日本語（validation.phpが整ってれば不要だけど、確実に日本語にするなら書く）
            'clock_in_at.date_format'   => '出勤時間は「HH:MM」形式で入力してください。',
            'clock_out_at.date_format'  => '退勤時間は「HH:MM」形式で入力してください。',
            'breaks.*.start.date_format'=> '休憩開始時間は「HH:MM」形式で入力してください。',
            'breaks.*.end.date_format'  => '休憩終了時間は「HH:MM」形式で入力してください。',

            // required の日本語（FN029④）
            'remarks.required' => '備考を記入してください。',
            'remarks.max' => '備考は255文字以内で入力してください。',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            // after の中では validated() じゃなく input() を使う方が安全（未入力も拾える）
            $clockInStr  = $this->input('clock_in_at');
            $clockOutStr = $this->input('clock_out_at');

            $clockIn  = $clockInStr  ? Carbon::createFromFormat('H:i', $clockInStr)  : null;
            $clockOut = $clockOutStr ? Carbon::createFromFormat('H:i', $clockOutStr) : null;

            // ① 出勤/退勤 逆転 or 同時刻
            if ($clockIn && $clockOut && $clockIn >= $clockOut) {
                $validator->errors()->add('clock_out_at', '出勤時間もしくは退勤時間が不適切な値です。');
            }

            $breaks = $this->input('breaks', []);
            foreach ($breaks as $i => $b) {
                $startStr = $b['start'] ?? null;
                $endStr   = $b['end'] ?? null;

                $start = $startStr ? Carbon::createFromFormat('H:i', $startStr) : null;
                $end   = $endStr   ? Carbon::createFromFormat('H:i', $endStr)   : null;

                // ② 休憩開始が勤務時間外
                if ($start && $clockIn && $start < $clockIn) {
                    $validator->errors()->add("breaks.$i.start", '休憩時間が不適切な値です。');
                }
                if ($start && $clockOut && $start > $clockOut) {
                    $validator->errors()->add("breaks.$i.start", '休憩時間が不適切な値です。');
                }

                // ③ 休憩終了が勤務時間外 / 開始より前 / 勤務外
                if ($end && $clockIn && $end < $clockIn) {
                    $validator->errors()->add("breaks.$i.end", '休憩時間もしくは退勤時間が不適切な値です。');
                }
                if ($end && $clockOut && $end > $clockOut) {
                    $validator->errors()->add("breaks.$i.end", '休憩時間もしくは退勤時間が不適切な値です。');
                }
                if ($start && $end && $start >= $end) {
                    $validator->errors()->add("breaks.$i.end", '休憩時間もしくは退勤時間が不適切な値です。');
                }
            }

            // ④ 備考は rules で required にしたので、ここで追加しなくてOK
        });
    }
}
