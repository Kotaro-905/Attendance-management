<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
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

    // ✅ 追加：勤怠一覧（月次）
    public function list(Request $request)
    {
        $user = Auth::user();

        // month は "Y-m" 想定（例: 2025-12）
        $month = $request->query('month', Carbon::today()->format('Y-m'));

        try {
            $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            $monthStart = Carbon::today()->startOfMonth();
            $month = $monthStart->format('Y-m');
        }

        $monthEnd = $monthStart->copy()->endOfMonth();

        // ★ここが重要：キーを必ず "Y-m-d" 文字列に揃える
        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->keyBy(function ($a) {
                return Carbon::parse($a->work_date)->toDateString();
            });

        $days = [];

        for ($d = $monthStart->copy(); $d->lte($monthEnd); $d->addDay()) {
            $a = $attendances->get($d->toDateString());

            $clockIn  = ($a && $a->clock_in_at)  ? Carbon::parse($a->clock_in_at)->format('H:i')  : '-';
            $clockOut = ($a && $a->clock_out_at) ? Carbon::parse($a->clock_out_at)->format('H:i') : '-';

            // 休憩合計（breaks優先、無ければ旧カラム）
            $breakMinutes = 0;

            if ($a) {
                if ($a->breaks && $a->breaks->count() > 0) {
                    foreach ($a->breaks as $br) {
                        if ($br->start_at && $br->end_at) {
                            $breakMinutes += Carbon::parse($br->start_at)
                                ->diffInMinutes(Carbon::parse($br->end_at));
                        }
                    }
                } elseif (!empty($a->break_start_at) && !empty($a->break_end_at)) {
                    $breakMinutes = Carbon::parse($a->break_start_at)
                        ->diffInMinutes(Carbon::parse($a->break_end_at));
                }
            }

            $breakDisp = ($breakMinutes > 0)
                ? sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60)
                : '-';

            $totalDisp = '-';
            if ($a && $a->clock_in_at && $a->clock_out_at) {
                $totalMinutes = Carbon::parse($a->clock_in_at)
                    ->diffInMinutes(Carbon::parse($a->clock_out_at)) - $breakMinutes;

                if ($totalMinutes < 0) $totalMinutes = 0;

                $totalDisp = sprintf('%d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60);
            }

            $days[] = [
                'date_label'    => $d->copy()->locale('ja')->isoFormat('MM/DD(ddd)'),
                'clock_in'      => $clockIn,
                'clock_out'     => $clockOut,
                'break'         => $breakDisp,
                'total'         => $totalDisp,
                'attendance_id' => $a?->id,
            ];
        }

        $prevMonth = $monthStart->copy()->subMonth()->format('Y-m');
        $nextMonth = $monthStart->copy()->addMonth()->format('Y-m');

        return view('attendance.list', compact(
            'monthStart',
            'prevMonth',
            'nextMonth',
            'days'
        ));
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
                if ($attendance->status === 0) {
                    $attendance->clock_in_at = $time;
                    $attendance->status      = 1;
                }
                break;

            case 'break_in':
                if ($attendance->status === 1) {
                    $attendance->break_start_at = $time;
                    $attendance->status        = 2;
                }
                break;

            case 'break_out':
                if ($attendance->status === 2 && $attendance->break_start_at) {
                    $attendance->break_end_at = $time;

                    $nextOrder = ($attendance->breaks()->max('order') ?? 0) + 1;

                    $attendance->breaks()->create([
                        'order'    => $nextOrder,
                        'start_at' => $attendance->break_start_at,
                        'end_at'   => $attendance->break_end_at,
                    ]);

                    $attendance->status = 1;
                }
                break;

            case 'clock_out':
                if (in_array($attendance->status, [1, 2], true)) {

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
                    $attendance->status       = 3;
                }
                break;
        }

        $attendance->save();

        return redirect()->route('attendance.index');
    }

   // ✅ 勤怠詳細（一般ユーザー）
public function show(Attendance $attendance)
{
    $user = Auth::user();
    abort_unless($attendance->user_id === $user->id, 403);

    $attendance->load([
        'user',
        'breaks' => fn ($q) => $q->orderBy('order'),
    ]);

    $workDate = Carbon::parse($attendance->work_date);

    // 最新申請
    $latestRequest = \App\Models\CorrectionRequest::with([
            'breaks' => fn ($q) => $q->orderBy('break_no'),
        ])
        ->where('attendance_id', $attendance->id)
        ->where('user_id', $user->id)
        ->latest('id')
        ->first();

    $isPending  = $latestRequest && (int)$latestRequest->status === 0;
    $isApproved = $latestRequest && (int)$latestRequest->status === 1;

    /**
     * ✅ 表示の基本方針
     * - 承認待ち：申請内容を表示（編集不可）
     * - 承認済み：attendance（反映後）を表示（＝連動しているのが自然）
     * - 申請なし：attendance を表示
     */
    if ($isPending) {
        $sourceClockIn  = $latestRequest?->requested_clock_in_time;
        $sourceClockOut = $latestRequest?->requested_clock_out_time;

        $displayBreaks = $latestRequest->breaks; // correction_breaks
        $displayReason = $latestRequest->reason ?? '';
    } else {
        $sourceClockIn  = $attendance->clock_in_at;
        $sourceClockOut = $attendance->clock_out_at;

        $displayBreaks = $attendance->breaks; // attendance_breaks
        $displayReason = ''; // 備考は申請理由なので、勤怠詳細では空でOK（仕様次第）
    }

    $clockInValue  = $sourceClockIn  ? Carbon::parse($sourceClockIn)->format('H:i') : '';
    $clockOutValue = $sourceClockOut ? Carbon::parse($sourceClockOut)->format('H:i') : '';

    // 休憩行数（最低2行、+1の空行を出す）
    $oldCount = is_array(old('breaks')) ? count(old('breaks')) : 0;
    $breakRowCount = max(2, $displayBreaks->count() + 1, $oldCount);

    return view('attendance.show', compact(
        'attendance',
        'workDate',
        'latestRequest',
        'isPending',
        'isApproved',
        'clockInValue',
        'clockOutValue',
        'displayBreaks',
        'breakRowCount',
        'displayReason'
    ));
}

}