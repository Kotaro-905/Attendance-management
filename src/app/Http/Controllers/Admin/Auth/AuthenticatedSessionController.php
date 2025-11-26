<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AdminLoginRequest; // ← 追加
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.admin.login');
    }

    public function store(AdminLoginRequest $request) // ← Request → AdminLoginRequest に変更
    {
        // validated() でバリデーション済みデータ取得
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'メールアドレスまたはパスワードが違います',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // 管理者判定
        if (!($user instanceof User) || !$user->isAdmin()) {
            Auth::logout();
            return back()->withErrors([
                'email' => '管理者ユーザーではありません',
            ])->onlyInput('email');
        }

        return redirect()->route('admin.attendance.index');
    }

    public function destroy()
    {
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
