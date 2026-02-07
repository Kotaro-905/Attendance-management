<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminRequestController extends Controller
{
    /**
     * 申請一覧（承認待ち / 承認済み）
     * ?status=pending|approved
     */
    public function index(Request $httpRequest)
    {
        $status = $httpRequest->query('status', 'pending'); // bladeで使う変数名に合わせる

        $statusValue = $status === 'approved' ? 1 : 0;

        $requests = CorrectionRequest::with(['user', 'attendance'])
            ->where('status', $statusValue)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.requests.index', compact('requests', 'status'));
    }

    /**
     * 申請詳細
     */
    public function show(CorrectionRequest $request)
    {
        $request->load([
            'user',
            'attendance',
            'breaks' => fn($q) => $q->orderBy('break_no'),
        ]);

        return view('admin.requests.show', [
            'request' => $request,
        ]);
    }

    /**
     * 承認（承認待ち → 承認済み）
     */
    public function approve(CorrectionRequest $request)
    {
        $request->load('breaks', 'attendance');

        DB::transaction(function () use ($request) {

            // 1. 申請を承認済みに
            $request->update([
                'status'     => 1, // 承認済み
                'admin_id'   => auth()->id(),
                'decided_at' => now(),
            ]);

            $attendance = $request->attendance;

            // 2. 勤怠本体を申請内容で更新
            $attendance->update([
                'clock_in_at'  => $request->requested_clock_in_time,
                'clock_out_at' => $request->requested_clock_out_time,
            ]);

            // 3. 休憩を申請内容で入れ替え
            $attendance->breaks()->delete();

            foreach ($request->breaks as $br) {
                $attendance->breaks()->create([
                    'order'    => $br->break_no,
                    'start_at' => $br->requested_break_start,
                    'end_at'   => $br->requested_break_end,
                ]);
            }
        });

        return redirect()->route('admin.requests.show', $request);
    }
}
