<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\CorrectionBreak;
use Carbon\Carbon;

class CorrectionRequestDummySeeder extends Seeder
{
    public function run(): void
    {
        // 一般ユーザーのみ対象
        $users = User::where('role', User::ROLE_GENERAL)->get();

        foreach ($users as $user) {

            // 勤怠を複数取得（件数制限はここだけ）
            $attendances = Attendance::where('user_id', $user->id)
                ->orderBy('work_date', 'asc')
                ->take(6) // ← 表示数を増減したい場合はここ
                ->get();

            foreach ($attendances as $attendance) {

                // 既に申請がある場合は作らない（増殖防止）
                $exists = CorrectionRequest::where('attendance_id', $attendance->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // 申請時刻を少しズラす
                $reqIn  = $attendance->clock_in_at
                    ? Carbon::parse($attendance->clock_in_at)->addMinutes(5)->format('H:i:s')
                    : null;

                $reqOut = $attendance->clock_out_at
                    ? Carbon::parse($attendance->clock_out_at)->addMinutes(10)->format('H:i:s')
                    : null;

                // ✅ 全件 承認待ち（制限なし）
                $req = CorrectionRequest::create([
                    'attendance_id'            => $attendance->id,
                    'user_id'                  => $user->id,
                    'admin_id'                 => null,
                    'requested_work_date'      => $attendance->work_date,
                    'requested_clock_in_time'  => $reqIn,
                    'requested_clock_out_time' => $reqOut,
                    'reason'                   => '電車遅延のため',
                    'status'                   => 0,   // ← 常に承認待ち
                    'decided_at'               => null,
                ]);

                // 休憩申請
                if (!empty($attendance->break_start_at) && !empty($attendance->break_end_at)) {
                    CorrectionBreak::create([
                        'correction_request_id' => $req->id,
                        'break_no'              => 1,
                        'requested_break_start' => Carbon::parse($attendance->break_start_at)->format('H:i:s'),
                        'requested_break_end'   => Carbon::parse($attendance->break_end_at)->format('H:i:s'),
                    ]);
                } else {
                    CorrectionBreak::create([
                        'correction_request_id' => $req->id,
                        'break_no'              => 1,
                        'requested_break_start' => '12:00:00',
                        'requested_break_end'   => '13:00:00',
                    ]);
                }
            }
        }
    }
}
