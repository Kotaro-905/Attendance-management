<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 勤怠登録画面表示
    public function index()
    {
        $user      = Auth::user();
        $today     = Carbon::today();
        $todayDate = $today->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $todayDate)
            ->first();

        // ステータス（0:未出勤,1:出勤中,2:休憩中,3:退勤済）
        $status = $attendance ? $attendance->status : 0;

        // 表示する時刻
        if ($attendance && $attendance->clock_in_at) {
            $displayTime = Carbon::parse($attendance->clock_in_at)->format('H:i');
        } else {
            $displayTime = Carbon::now()->format('H:i');
        }

        return view('attendance.index', [
            'attendance'  => $attendance,
            'status'      => $status,
            'today'       => $today,
            'displayTime' => $displayTime,
        ]);
    }

    // ボタン押下時の処理
    public function store(Request $request)
    {
        $user      = Auth::user();
        $todayDate = Carbon::today()->toDateString();

        // 今日の勤怠を取得 or 新規作成
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $todayDate],
            ['status'  => 0]
        );

        $action = $request->input('action');   // 'clock_in', 'break_in', 'break_out', 'clock_out'
        $now    = Carbon::now();
        $time   = $now->format('H:i:s');

        switch ($action) {
            case 'clock_in':
                // 未出勤 → 出勤
                if ($attendance->status === 0) {
                    $attendance->clock_in_at = $time;
                    $attendance->status      = 1; // 勤務中
                }
                break;

            case 'break_in':
                // 勤務中 → 休憩中
                if ($attendance->status === 1) {
                    $attendance->break_start_at = $time; // 進行中の休憩開始
                    $attendance->status        = 2;     // 休憩中
                }
                break;

            case 'break_out':
                // 休憩中 → 勤務中
                if ($attendance->status === 2 && $attendance->break_start_at) {
                    $attendance->break_end_at = $time;

                    // attendance_breaks に1件追加
                    $nextOrder = ($attendance->breaks()->max('order') ?? 0) + 1;

                    $attendance->breaks()->create([
                        'order'    => $nextOrder,
                        'start_at' => $attendance->break_start_at,
                        'end_at'   => $attendance->break_end_at,
                    ]);

                    $attendance->status = 1; // 勤務中へ
                }
                break;

            case 'clock_out':
                // 勤務中 or 休憩中 → 退勤済
                if (in_array($attendance->status, [1, 2], true)) {

                    // 休憩中のまま退勤した場合、休憩終了も記録してから退勤
                    if ($attendance->status === 2 && $attendance->break_start_at) {
                        $attendance->break_end_at = $time;

                        $nextOrder = ($attendance->breaks()->max('order') ?? 0) + 1;

                        $attendance->breaks()->create([
                            'order'    => $nextOrder,
                            'start_at' => $attendance->break_start_at,
                            'end_at'   => $attendance->break_end_at,
                        ]);
                    }

                    $attendance->clock_out_at = $time;
                    $attendance->status       = 3; // 退勤済
                }
                break;
        }

        $attendance->save();

        return redirect()->route('attendance.index');
    }
}
