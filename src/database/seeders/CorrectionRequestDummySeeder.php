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
        // StaffDummySeeder で作った一般ユーザー（role=general）を想定
        $users = User::where('role', User::ROLE_GENERAL)->get();

        foreach ($users as $user) {
            // そのユーザーの勤怠から適当に2件拾う（無ければスキップ）
            $attendances = Attendance::where('user_id', $user->id)
                ->orderBy('work_date', 'asc')
                ->take(2)
                ->get();

            foreach ($attendances as $attendance) {

                // 申請時刻を「少しズラした値」にする（例：出勤+5分、退勤+10分）
                $reqIn  = $attendance->clock_in_at ? Carbon::parse($attendance->clock_in_at)->addMinutes(5)->format('H:i:s') : null;
                $reqOut = $attendance->clock_out_at ? Carbon::parse($attendance->clock_out_at)->addMinutes(10)->format('H:i:s') : null;

                $req = CorrectionRequest::create([
                    'attendance_id'           => $attendance->id,
                    'user_id'                 => $user->id,
                    'admin_id'                => null,
                    'requested_work_date'     => $attendance->work_date,
                    'requested_clock_in_time' => $reqIn,
                    'requested_clock_out_time'=> $reqOut,
                    'reason'                  => '電車遅延のため',
                    'status'                  => 0, // 承認待ち
                    'decided_at'              => null,
                ]);

                // 休憩は子テーブルに入れる（correction_requests の break カラムは削除済み想定）
                // attendanceの旧break_start_at / break_end_at があればそれを少しズラして登録
                if (!empty($attendance->break_start_at) && !empty($attendance->break_end_at)) {
                    CorrectionBreak::create([
                        'correction_request_id' => $req->id,
                        'break_no'              => 1,
                        'requested_break_start' => Carbon::parse($attendance->break_start_at)->format('H:i:s'),
                        'requested_break_end'   => Carbon::parse($attendance->break_end_at)->format('H:i:s'),
                    ]);
                } else {
                    // 無い場合は固定で1本入れてもOK（見本表示用）
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
