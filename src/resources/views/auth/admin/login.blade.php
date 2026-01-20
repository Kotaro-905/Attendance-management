@extends('layouts.auth')

@section('content')
<h1 class="auth-title">管理者ログイン</h1>

<form method="POST" action="{{ route('admin.login') }}" novalidate>
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
        管理者ログインする
    </button>
</form>
@endsection
