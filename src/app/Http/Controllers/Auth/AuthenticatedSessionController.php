<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;          // ★ 追加
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'メールアドレスまたはパスワードが異なります。',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (!($user instanceof User) || !$user->isGeneral()) {
            Auth::logout();
            return back()->withErrors([
                'email' => '一般ユーザーとしてのログインに失敗しました。',
            ])->onlyInput('email');
        }

        return redirect()->route('attendance.index');
    }


    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
