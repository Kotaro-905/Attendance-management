<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Fortify;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
{
    Fortify::loginView(fn () => view('auth.login'));
    Fortify::registerView(fn () => view('auth.register'));

    // ✅ 追加：メール認証の誘導画面
    Fortify::verifyEmailView(fn () => view('auth.verify-email'));

    Fortify::createUsersUsing(\App\Actions\Fortify\CreateNewUser::class);

    Fortify::authenticateUsing(function ($request) {
        $form = new \App\Http\Requests\Auth\LoginFormRequest();
        Validator::make(
            $request->all(),
            $form->rules(),
            $form->messages()
        )->validate();

        if (Auth::attempt($request->only('email', 'password'))) {
            return Auth::user();
        }

        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => ['ログイン情報が登録されていません'],
        ]);
    });
}

}
