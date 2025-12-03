<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;   // ★ 追加

class AttendanceDummySeeder extends Seeder
{
    public function run(): void
    {
        // ★ ダミーを入れたい期間を決める
        $startDate = Carbon::parse('2025-11-01');
        $endDate   = Carbon::parse('2026-03-31');

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

            // ★ 期間内の日付を 1 日ずつループ
            $date = $startDate->copy();

            while ($date->lte($endDate)) {

                // ★ 土日をスキップ
                if (! $date->isWeekend()) {

                    Attendance::updateOrCreate(
                        [
                            'user_id'   => $staff->id,
                            'work_date' => $date->toDateString(),
                        ],
                        [
                            // 出勤 09:00
                            'clock_in_at'     => '09:00:00',

                            // 休憩 12:00〜13:00
                            'break_start_at'  => '12:00:00',
                            'break_end_at'    => '13:00:00',

                            // 退勤 18:00
                            'clock_out_at'    => '18:00:00',

                            // 退勤済ステータス（例）
                            'status'          => 3,
                            'remarks'         => null,
                        ]
                    );
                }

                // 次の日へ
                $date->addDay();
            }
        }
    }
}
