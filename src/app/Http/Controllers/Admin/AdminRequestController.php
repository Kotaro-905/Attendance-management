<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'breaks' => fn ($q) => $q->orderBy('break_no'),
        ]);

        return view('admin.requests.show', compact('request'));
    }

    /**
     * 承認（承認待ち → 承認済み）
     */
    public function approve(CorrectionRequest $request)
    {
        $request->update([
            'status'     => 1,
            'admin_id'   => Auth::id(),
            'decided_at' => now(),
        ]);

        // もし「承認したら attendances を実値で更新」まで要件に入れるならここで反映処理を追加します。
        // まずは「承認待ち → 承認済みに移動」だけならこれでOK。

        return redirect()
            ->route('admin.requests.index', ['status' => 'pending'])
            ->with('message', '承認しました');
    }
}
