<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class StaffController extends Controller
{
    /**
     * スタッフ一覧（管理者）
     */
    public function index()
    {
        $staff = User::query()
            ->when(
                Schema::hasColumn('users', 'is_admin'),
                fn ($q) => $q->where('is_admin', false)
            )
            ->where('email', '!=', 'admin@example.com')
            ->orderBy('id')
            ->get();

        return view('admin.staff.index', compact('staff'));
    }

    /**
     * スタッフ別 月次勤怠一覧（管理者）
     */
    public function attendance(User $user, Request $request)
{
    $month = $request->query('month', now()->format('Y-m')); // 例: 2023-06

    $monthStart = Carbon::parse($month . '-01')->startOfMonth();
    $monthEnd   = $monthStart->copy()->endOfMonth();

    // work_date が date/datetime どちらでも確実に拾う
    $attendances = Attendance::with('breaks')
        ->where('user_id', $user->id)
        ->whereDate('work_date', '>=', $monthStart->toDateString())
        ->whereDate('work_date', '<=', $monthEnd->toDateString())
        ->get()
        // key を必ず "Y-m-d" に揃える（←ここが超重要）
        ->keyBy(fn ($a) => Carbon::parse($a->work_date)->toDateString());

    $days = [];

    for ($d = $monthStart->copy(); $d->lte($monthEnd); $d->addDay()) {
        $key = $d->toDateString();
        $a = $attendances->get($key);

        // 出勤・退勤
        $clockIn  = ($a && $a->clock_in_at)  ? Carbon::parse($a->clock_in_at)->format('H:i')  : '-';
        $clockOut = ($a && $a->clock_out_at) ? Carbon::parse($a->clock_out_at)->format('H:i') : '-';

        // 休憩合計（breaks優先、無ければ旧カラム）
        $breakMinutes = 0;

        if ($a) {
            if ($a->relationLoaded('breaks') && $a->breaks->count() > 0) {
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

        // 合計（出勤〜退勤 − 休憩）
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
            'date'          => $key,
        ];
    }

    $prevMonth = $monthStart->copy()->subMonth()->format('Y-m');
    $nextMonth = $monthStart->copy()->addMonth()->format('Y-m');

    return view('admin.staff.attendance', compact(
        'user',
        'month',
        'monthStart',
        'prevMonth',
        'nextMonth',
        'days'
    ));
}
}