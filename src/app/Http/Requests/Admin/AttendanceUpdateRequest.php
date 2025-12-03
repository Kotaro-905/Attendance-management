<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // 管理者しか通らないミドルウェアをかけている前提なので true
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in_at'     => ['nullable', 'date_format:H:i'],
            'clock_out_at'    => ['nullable', 'date_format:H:i'],
            'break_start_at'  => ['nullable', 'date_format:H:i'],
            'break_end_at'    => ['nullable', 'date_format:H:i'],
            // 休憩2はDBに無いダミーなのでとりあえず形式だけ見て無視する
            'break2_start'    => ['nullable', 'date_format:H:i'],
            'break2_end'      => ['nullable', 'date_format:H:i'],
            'remarks'         => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_in_at.date_format'    => '出勤時間は「HH:MM」形式で入力してください。',
            'clock_out_at.date_format'   => '退勤時間は「HH:MM」形式で入力してください。',
            'break_start_at.date_format' => '休憩開始時間は「HH:MM」形式で入力してください。',
            'break_end_at.date_format'   => '休憩終了時間は「HH:MM」形式で入力してください。',
            'break2_start.date_format'   => '休憩2の開始時間は「HH:MM」形式で入力してください。',
            'break2_end.date_format'     => '休憩2の終了時間は「HH:MM」形式で入力してください。',
        ];
    }
}
