<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 管理者のみ通るミドルウェア前提
    }

    public function rules(): array
    {
        return [
            'clock_in_at'  => ['nullable', 'date_format:H:i'],
            'clock_out_at' => ['nullable', 'date_format:H:i'],

            // 休憩（10回分）
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end'   => ['nullable', 'date_format:H:i'],

            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_in_at.date_format' => '出勤時間は「HH:MM」形式で入力してください。',
            'clock_out_at.date_format' => '退勤時間は「HH:MM」形式で入力してください。',
            'breaks.*.start.date_format' => '休憩開始時間は「HH:MM」形式で入力してください。',
            'breaks.*.end.date_format'   => '休憩終了時間は「HH:MM」形式で入力してください。',
        ];
    }

    /**
     * カスタムバリデーション（要件①～④）
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {

            $data = $this->validated();

            // 出勤・退勤
            $clockIn  = !empty($data['clock_in_at'])  ? Carbon::createFromFormat('H:i', $data['clock_in_at'])  : null;
            $clockOut = !empty($data['clock_out_at']) ? Carbon::createFromFormat('H:i', $data['clock_out_at']) : null;

            // --------------------------
            // ① 出勤より退勤が早い → エラー
            // --------------------------
            if ($clockIn && $clockOut && $clockIn >= $clockOut) {
                $validator->errors()->add(
                    'clock_out_at',
                    '出勤時間または退勤時間が不適切な値です。'
                );
            }

            // --------------------------
            // ②・③ 各休憩のチェック
            // --------------------------
            if (!empty($data['breaks'])) {

                foreach ($data['breaks'] as $index => $b) {

                    $start = !empty($b['start']) ? Carbon::createFromFormat('H:i', $b['start']) : null;
                    $end   = !empty($b['end'])   ? Carbon::createFromFormat('H:i', $b['end'])   : null;

                    // ② 休憩開始が勤務時間の外
                    if ($start && $clockIn && $start < $clockIn) {
                        $validator->errors()->add(
                            "breaks.$index.start",
                            "休憩{$index}の開始時間が不適切な値です。"
                        );
                    }
                    if ($start && $clockOut && $start > $clockOut) {
                        $validator->errors()->add(
                            "breaks.$index.start",
                            "休憩{$index}の開始時間が不適切な値です。"
                        );
                    }

                    // ③ 休憩終了が勤務時間の外 or 開始より前
                    if ($end && $clockIn && $end < $clockIn) {
                        $validator->errors()->add(
                            "breaks.$index.end",
                            "休憩{$index}の終了時間が不適切な値です。"
                        );
                    }
                    if ($end && $clockOut && $end > $clockOut) {
                        $validator->errors()->add(
                            "breaks.$index.end",
                            "休憩{$index}の終了時間が不適切な値です。"
                        );
                    }
                    if ($start && $end && $start >= $end) {
                        $validator->errors()->add(
                            "breaks.$index.end",
                            "休憩{$index}の時間が逆転しています。"
                        );
                    }
                }
            }

            // --------------------------
            // ④ 備考が未入力時の要件
            // --------------------------
            if (empty($data['remarks'])) {
                $validator->errors()->add('remarks', '備考を記入してください。');
            }
        });
    }
}
