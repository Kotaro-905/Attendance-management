<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;

class AttendanceDummySeeder extends Seeder
{
    public function run(): void
    {
        Attendance::where(function ($q) {
            $q->whereDate('work_date', '<', '2025-04-01')
                ->orWhereDate('work_date', '>', '2026-01-31');
        })->delete();
        // ★ ダミーを入れたい期間（必要ならここだけ変える）
        $startDate = Carbon::parse('2025-04-01');
        $endDate   = Carbon::parse('2026-01-31');

        // ★ 一般ユーザー（role=general）を全部対象にする
        $users = User::where('role', User::ROLE_GENERAL)->get();

        foreach ($users as $user) {

            $date = $startDate->copy();

            while ($date->lte($endDate)) {

                // 土日スキップ（今の仕様のまま）
                if (! $date->isWeekend()) {

                    $attendance = Attendance::updateOrCreate(
                        [
                            'user_id'   => $user->id,
                            'work_date' => $date->toDateString(),
                        ],
                        [
                            'clock_in_at'    => '09:00:00',
                            'break_start_at' => '12:00:00', // 旧カラムも残す
                            'break_end_at'   => '13:00:00',
                            'clock_out_at'   => '18:00:00',
                            'status'         => 3,
                            'remarks'        => null,
                        ]
                    );

                    // ★ breaks（子テーブル）も必ず作る（増殖防止で作り直し）
                    if (method_exists($attendance, 'breaks')) {
                        $attendance->breaks()->delete();

                        $attendance->breaks()->create([
                            'order'    => 1,
                            'start_at' => '12:00:00',
                            'end_at'   => '13:00:00',
                        ]);
                    } else {
                        AttendanceBreak::where('attendance_id', $attendance->id)->delete();

                        AttendanceBreak::create([
                            'attendance_id' => $attendance->id,
                            'order'         => 1,
                            'start_at'      => '12:00:00',
                            'end_at'        => '13:00:00',
                        ]);
                    }
                }

                $date->addDay();
            }
        }
    }
}
