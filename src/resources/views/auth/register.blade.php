@extends('layouts.auth')

@section('content')
<h1 class="auth-title">会員登録</h1>

<form method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">名前</label>
                <input id="name" type="text" name="name"
                       value="{{ old('name') }}" class="form-input">
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

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

            <div class="form-group">
                <label for="password_confirmation" class="form-label">パスワード確認</label>
                <input id="password_confirmation" type="password"
                       name="password_confirmation" class="form-input">
                @error('password_confirmation')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="auth-button">
                登録する
            </button>

            <p class="auth-link">
                <a href="{{ route('login') }}">ログインはこちら</a>
            </p>
        </form>
@endsection
