<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests\Admin\AttendanceUpdateRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\CorrectionRequest;

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
        // 勤怠と休憩は従来通り
        $attendance->load(['user', 'breaks']);

        $workDate = Carbon::parse($attendance->work_date);
        $clockIn  = $attendance->clock_in_at  ? Carbon::parse($attendance->clock_in_at)  : null;
        $clockOut = $attendance->clock_out_at ? Carbon::parse($attendance->clock_out_at) : null;

        $breaks = $attendance->breaks
            ->sortBy('order')
            ->values();

        $breakRowCount = max(2, $breaks->count() + 1);

        /**
         * ✅ 追加：この勤怠に紐づく申請を取得
         * - 承認待ち(status=0)があればそれを優先
         * - なければ最新を1件
         */
        $correctionRequest = CorrectionRequest::with(['breaks', 'attendance.user'])
            ->where('attendance_id', $attendance->id)
            ->orderBy('status')          // 0(承認待ち) → 1(承認済み)
            ->orderByDesc('created_at')  // 同じstatusなら最新
            ->first();

        // ✅ 申請があるかどうか（blade分岐用）
        $hasRequest = !is_null($correctionRequest);

        // ✅ 申請がある場合の状態（blade分岐用）
        $isPending  = $correctionRequest ? ((int)$correctionRequest->status === 0) : false;
        $isApproved = $correctionRequest ? ((int)$correctionRequest->status === 1) : false;

        return view('admin.attendance.show', [
            // 既存
            'attendance'    => $attendance,
            'workDate'      => $workDate,
            'clockIn'       => $clockIn,
            'clockOut'      => $clockOut,
            'breaks'        => $breaks,
            'breakRowCount' => $breakRowCount,
            'clockInValue'  => $clockIn ? $clockIn->format('H:i') : '',
            'clockOutValue' => $clockOut ? $clockOut->format('H:i') : '',

            // ✅ 追加（ここが肝）
            'correctionRequest' => $correctionRequest,
            'hasRequest'        => $hasRequest,
            'isPending'         => $isPending,
            'isApproved'        => $isApproved,
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

        /**
         * ✅ 休憩：入力されたものを保存（空行は保存しない）
         */
        $attendance->breaks()->delete();

        $order = 1;

        if (!empty($data['breaks']) && is_array($data['breaks'])) {
            foreach ($data['breaks'] as $break) {
                $start = $break['start'] ?? null;
                $end   = $break['end']   ?? null;

                // 両方空ならスキップ（＝空枠はDBに作らない）
                if (empty($start) && empty($end)) {
                    continue;
                }

                // 片方だけ入ってるケースは、バリデーションで弾く想定
                // （もし許容するならここで追加処理が必要）
                if (!empty($start) && !empty($end)) {
                    $attendance->breaks()->create([
                        'order'    => $order,
                        'start_at' => $start . ':00',
                        'end_at'   => $end   . ':00',
                    ]);
                    $order++;
                }
            }
        }

        /**
         * ✅ 更新後は「詳細に留まる」
         */
        return redirect()
            ->route('admin.attendance.show', $attendance)
            ->with('status', '勤怠を更新しました。');
    }
}
