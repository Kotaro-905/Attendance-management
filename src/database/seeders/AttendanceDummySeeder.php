<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;

class AttendanceDummySeeder extends Seeder
{
    public function run(): void
    {
        // ダミーデータとして使う勤務日
        // Figma に合わせるなら 2023-06-01、今のままで良ければ 2025-11-28 のままでOKです。
        $workDate = '2025-11-28';

        // ダミースタッフ用ユーザーだけ取得
        $staffs = User::whereIn('email', [
            'staff1@example.com',
            'staff2@example.com',
            'staff3@example.com',
            'staff4@example.com',
            'staff5@example.com',
            'staff6@example.com',
        ])->get();

        foreach ($staffs as $staff) {
            Attendance::updateOrCreate(
                [
                    'user_id'   => $staff->id,
                    'work_date' => $workDate,
                ],
                [
                    // 出勤 09:00
                    'clock_in_at'     => '09:00:00',
                    // 休憩 12:00〜13:00
                    'break_start_at'  => '12:00:00',
                    'break_end_at'    => '13:00:00',
                    // 退勤 18:00
                    'clock_out_at'    => '18:00:00',
                    // 退勤済ステータス（0:未出勤,1:勤務中,2:休憩中,3:退勤済 想定）
                    'status'          => 3,
                    'remarks'         => null,
                ]
            );
        }
    }
}
