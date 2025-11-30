<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'COACHTECH' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- 認証画面専用のCSS --}}
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    {{-- 黒いヘッダー＋ロゴだけ --}}
    <header class="auth-header">
        <img src="{{ asset('images/logo.svg') }}" alt="CT COACHTECH" class="auth-header__logo">
    </header>

    {{-- 真ん中の白いカード --}}
    <main class="auth-container">
        <div class="auth-card">
            @yield('content')
        </div>
    </main>
</body>
</html>
