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
    /**
     * 勤怠一覧（管理者）
     */
    public function index(Request $request)
    {
        $dateParam = $request->query('date');

        try {
            $targetDate = $dateParam
                ? Carbon::parse($dateParam)->startOfDay()
                : Carbon::today();
        } catch (\Exception $e) {
            $targetDate = Carbon::today();
        }

        $dateString = $targetDate->toDateString();
        $prevDate   = $targetDate->copy()->subDay();
        $nextDate   = $targetDate->copy()->addDay();

        // user と breaks を一緒に取得
        $attendances = Attendance::with(['user', 'breaks'])
            ->where('work_date', $dateString)
            ->orderBy('user_id')
            ->get()
            ->map(function ($attendance) {

                // 出勤・退勤 表示用
                $attendance->clock_in_display = $attendance->clock_in_at
                    ? Carbon::parse($attendance->clock_in_at)->format('H:i')
                    : '-';

                $attendance->clock_out_display = $attendance->clock_out_at
                    ? Carbon::parse($attendance->clock_out_at)->format('H:i')
                    : '-';

                // -----------------------------
                // 休憩合計
                // 1. attendance_breaks にあればそちらを優先
                // 2. 何も無ければ旧カラム break_start_at / break_end_at を見る
                // -----------------------------
                $breakMinutes = 0;
                $hasBreakFromBreaks = false;

                foreach ($attendance->breaks as $break) {
                    if ($break->start_at && $break->end_at) {
                        $hasBreakFromBreaks = true;
                        $breakMinutes += Carbon::parse($break->start_at)
                            ->diffInMinutes(Carbon::parse($break->end_at));
                    }
                }

                // attendance_breaks からは1つも取れなかった場合だけ、旧カラムを使う
                if (!$hasBreakFromBreaks && $attendance->break_start_at && $attendance->break_end_at) {
                    $breakMinutes += Carbon::parse($attendance->break_start_at)
                        ->diffInMinutes(Carbon::parse($attendance->break_end_at));
                }

                if ($breakMinutes > 0) {
                    $attendance->break_duration_display =
                        sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
                } else {
                    $attendance->break_duration_display = '-';
                    $breakMinutes = 0;
                }

                // -----------------------------
                // 合計勤務時間（出勤〜退勤 − 休憩）
                // -----------------------------
                if ($attendance->clock_in_at && $attendance->clock_out_at) {
                    $totalMinutes = Carbon::parse($attendance->clock_in_at)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out_at));

                    $totalMinutes -= $breakMinutes;

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

    /**
     * 勤怠詳細（管理者）
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['user', 'breaks']);

        $workDate = Carbon::parse($attendance->work_date);
        $clockIn  = $attendance->clock_in_at  ? Carbon::parse($attendance->clock_in_at)  : null;
        $clockOut = $attendance->clock_out_at ? Carbon::parse($attendance->clock_out_at) : null;

        $breaks = $attendance->breaks
            ->sortBy('order')
            ->values();

        // ▼ここが「10回まで」の原因だったので、上限を外す
        $breakRowCount = $breaks->count();
        $breakRowCount = max(1, $breakRowCount);
        // $breakRowCount = min(10, $breakRowCount); // ← 削除（回数無制限）

        return view('admin.attendance.show', [
            'attendance'    => $attendance,
            'workDate'      => $workDate,
            'clockIn'       => $clockIn,
            'clockOut'      => $clockOut,
            'breaks'        => $breaks,
            'breakRowCount' => $breakRowCount,
            'clockInValue'  => $clockIn ? $clockIn->format('H:i') : '',
            'clockOutValue' => $clockOut ? $clockOut->format('H:i') : '',
        ]);
    }

    /**
     * 勤怠更新（管理者）
     */
    public function update(
        AttendanceUpdateRequest $request,
        Attendance $attendance
    ): RedirectResponse {

        $data = $request->validated();

        $attendance->clock_in_at  = !empty($data['clock_in_at'])
            ? $data['clock_in_at'] . ':00'
            : null;

        $attendance->clock_out_at = !empty($data['clock_out_at'])
            ? $data['clock_out_at'] . ':00'
            : null;

        $attendance->remarks = $data['remarks'] ?? null;
        $attendance->save();

        // 休憩レコード作り直し
        $attendance->breaks()->delete();

        if (!empty($data['breaks']) && is_array($data['breaks'])) {
            foreach ($data['breaks'] as $index => $break) {
                $start = $break['start'] ?? null;
                $end   = $break['end']   ?? null;

                if ($start && $end) {
                    $attendance->breaks()->create([
                        'order'    => (int) $index,
                        'start_at' => $start . ':00',
                        'end_at'   => $end   . ':00',
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.attendance.index', ['date' => $attendance->work_date])
            ->with('status', '勤怠を更新しました。');
    }
}
