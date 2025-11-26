@extends('layouts.app')

@section('content')

<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">ログイン</h1>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">メールアドレス</label>
                <input id="email" type="email" name="email"
                       value="{{ old('email') }}" class="form-input">
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">パスワード</label>
                <input id="password" type="password" name="password"
                       class="form-input">
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="auth-button">
                ログインする
            </button>

            <p class="auth-link">
                <a href="{{ route('register') }}">会員登録はこちら</a>
            </p>
        </form>
    </div>
</div>
@endsection
