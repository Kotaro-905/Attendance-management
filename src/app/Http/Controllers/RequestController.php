<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\CorrectionBreak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceCorrectionRequest;

class RequestController extends Controller
{
    /**
     * 申請一覧（一般ユーザー）
     * ?status=pending|approved
     */
    public function index(Request $httpRequest)
    {
        $statusParam = $httpRequest->query('status', 'pending'); // pending/approved
        $statusValue = $statusParam === 'approved' ? 1 : 0;

        $requests = CorrectionRequest::with(['attendance', 'breaks'])
            ->where('user_id', Auth::id())              // ★自分のだけ
            ->where('status', $statusValue)
            ->orderByDesc('created_at')
            ->get();

        return view('requests.index', [
            'requests' => $requests,
            'status'   => $statusParam,                 // bladeでタブのactiveに使う
        ]);
    }

    /**
     * 申請詳細（一般ユーザー）
     */
    public function show(CorrectionRequest $correctionRequest)
    {
        abort_unless($correctionRequest->user_id === Auth::id(), 403);

        $correctionRequest->load([
            'attendance.user',
            'breaks' => fn($q) => $q->orderBy('break_no'),
        ]);

        return view('requests.show', [
            'correctionRequest' => $correctionRequest,
        ]);
    }
    /**
     * 修正申請 作成（一般ユーザー）
     * attendance.show の「修正」ボタンから呼ぶ想定
     */
    public function store(AttendanceCorrectionRequest $httpRequest)
    {
        $data = $httpRequest->validated([
            'attendance_id' => ['required', 'integer', 'exists:attendances,id'],
            'clock_in_at'   => ['nullable', 'date_format:H:i'],
            'clock_out_at'  => ['nullable', 'date_format:H:i'],
            'breaks'        => ['nullable', 'array'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end'  => ['nullable', 'date_format:H:i'],
            'reason'        => ['required', 'string', 'max:255'],
        ]);

        $attendance = Attendance::with('breaks')->findOrFail($data['attendance_id']);
        abort_unless($attendance->user_id === Auth::id(), 403);

        // ★「承認待ち」が既にある勤怠は二重申請させない（仕様に合わせて）
        $alreadyPending = CorrectionRequest::where('attendance_id', $attendance->id)
            ->where('user_id', Auth::id())
            ->where('status', 0)
            ->exists();

        if ($alreadyPending) {
            return redirect()
                ->route('attendance.show', $attendance)
                ->withErrors(['reason' => '承認待ちのため修正はできません。']);
        }

        $req = CorrectionRequest::create([
            'attendance_id'            => $attendance->id,
            'user_id'                  => Auth::id(),
            'admin_id'                 => null,
            'requested_work_date'      => $attendance->work_date,
            'requested_clock_in_time'  => !empty($data['clock_in_at'])  ? $data['clock_in_at']  . ':00' : null,
            'requested_clock_out_time' => !empty($data['clock_out_at']) ? $data['clock_out_at'] . ':00' : null,
            'reason'                   => $data['reason'],
            'status'                   => 0,   // 承認待ち
            'decided_at'               => null,
        ]);

        // breaks（空行はスキップ）
        $breakNo = 1;
        foreach (($data['breaks'] ?? []) as $row) {
            $start = $row['start'] ?? null;
            $end   = $row['end'] ?? null;

            if ($start && $end) {
                CorrectionBreak::create([
                    'correction_request_id' => $req->id,
                    'break_no'              => $breakNo++,
                    'requested_break_start' => $start . ':00',
                    'requested_break_end'   => $end   . ':00',
                ]);
            }
        }

        return redirect()->route('requests.show', $req);
    }
}
