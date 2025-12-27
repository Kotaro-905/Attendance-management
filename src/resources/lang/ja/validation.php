<?php

return [
    'required' => ':attribute を入力してください。',
    'string' => ':attribute は文字列で入力してください。',
    'array' => ':attribute は配列で入力してください。',
    'max' => [
        'string' => ':attribute は:max文字以内で入力してください。',
    ],
    'date_format' => ':attribute は「:format」形式で入力してください。',
    'exists' => '選択された:attributeは正しくありません。',

    'attributes' => [
        'attendance_id' => '勤怠ID',
        'clock_in_at' => '出勤時間',
        'clock_out_at' => '退勤時間',
        'reason' => '備考',
        'breaks.*.start' => '休憩開始時間',
        'breaks.*.end' => '休憩終了時間',
    ],
];
