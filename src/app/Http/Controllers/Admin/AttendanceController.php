<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests\Admin\AttendanceUpdateRequest;
use Illuminate\Http\RedirectResponse;

class AttendanceController extends Controller
{
    // 勤怠一覧画面（管理者）
    public function index(Request $request)
    {
        // ?date=2023-06-01 みたいなクエリがあればそれを優先
        $dateParam = $request->query('date');

        try {
            $targetDate = $dateParam
                ? Carbon::parse($dateParam)->startOfDay()
                : Carbon::today();
        } catch (\Exception $e) {
            // 変な日付が来たら今日に戻す
            $targetDate = Carbon::today();
        }

        $dateString = $targetDate->toDateString();
        $prevDate   = $targetDate->copy()->subDay();
        $nextDate   = $targetDate->copy()->addDay();

        // 指定日の勤怠＋ユーザー名
        $attendances = Attendance::with('user')
            ->where('work_date', $dateString)
            ->orderBy('user_id')
            ->get()
            ->map(function ($attendance) {
                // 出勤・退勤の表示
                $attendance->clock_in_display = $attendance->clock_in_at
                    ? Carbon::parse($attendance->clock_in_at)->format('H:i')
                    : '-';

                $attendance->clock_out_display = $attendance->clock_out_at
                    ? Carbon::parse($attendance->clock_out_at)->format('H:i')
                    : '-';

                // 休憩時間
                $breakMinutes = null;
                if ($attendance->break_start_at && $attendance->break_end_at) {
                    $breakMinutes = Carbon::parse($attendance->break_start_at)
                        ->diffInMinutes(Carbon::parse($attendance->break_end_at));

                    $attendance->break_duration_display =
                        sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
                } else {
                    $attendance->break_duration_display = '-';
                }

                // 合計勤務時間（出勤〜退勤 − 休憩）
                if ($attendance->clock_in_at && $attendance->clock_out_at) {
                    $totalMinutes = Carbon::parse($attendance->clock_in_at)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out_at));

                    if ($breakMinutes !== null) {
                        $totalMinutes -= $breakMinutes;
                    }

                    $attendance->total_duration_display =
                        sprintf('%d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60);
                } else {
                    $attendance->total_duration_display = '-';
                }

                return $attendance;
            });

        return view('admin.attendance.index', [
            'targetDate'  => $targetDate,
            'prevDate'    => $prevDate,
            'nextDate'    => $nextDate,
            'attendances' => $attendances,
        ]);
    }

    // 勤怠詳細（管理者）
    public function show(Attendance $attendance)
    {
        // ユーザー情報も一緒に
        $attendance->load('user');

        $workDate   = Carbon::parse($attendance->work_date);
        $clockIn    = $attendance->clock_in_at ? Carbon::parse($attendance->clock_in_at) : null;
        $clockOut   = $attendance->clock_out_at ? Carbon::parse($attendance->clock_out_at) : null;
        $breakStart = $attendance->break_start_at ? Carbon::parse($attendance->break_start_at) : null;
        $breakEnd   = $attendance->break_end_at ? Carbon::parse($attendance->break_end_at) : null;

        return view('admin.attendance.show', compact(
            'attendance',
            'workDate',
            'clockIn',
            'clockOut',
            'breakStart',
            'breakEnd'
        ));
    }

    public function update(AttendanceUpdateRequest $request, Attendance $attendance): RedirectResponse
    {
        $data = $request->validated();

        // form は "HH:MM" なので、カラムが time(またはdatetime) なら秒を付けて保存
        $attendance->clock_in_at    = !empty($data['clock_in_at'])    ? $data['clock_in_at']    . ':00' : null;
        $attendance->clock_out_at   = !empty($data['clock_out_at'])   ? $data['clock_out_at']   . ':00' : null;
        $attendance->break_start_at = !empty($data['break_start_at']) ? $data['break_start_at'] . ':00' : null;
        $attendance->break_end_at   = !empty($data['break_end_at'])   ? $data['break_end_at']   . ':00' : null;

        // 休憩2は今はDBに保存しない想定なので何もしない
        $attendance->remarks = $data['remarks'] ?? null;

        $attendance->save();

        // 一覧画面に戻る（日付はその勤怠の work_date をクエリで渡す）
        return redirect()
            ->route('admin.attendance.index', ['date' => $attendance->work_date])
            ->with('status', '勤怠を更新しました。');
    }
}

