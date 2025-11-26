{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'COACHTECH 勤怠管理' }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

<header class="site-header">
    <div class="site-header__left">
        <a href="{{ route('attendance.index') }}" class="site-header__logo-link">
            <img src="{{ asset('images/logo.svg') }}" alt="CT COACHTECH" class="site-header__logo">
        </a>
    </div>
    <nav class="site-header__nav">
        <a href="{{ route('attendance.index') }}" class="site-header__nav-link">勤怠</a>
        <a href="#" class="site-header__nav-link">勤怠一覧</a>
        <a href="#" class="site-header__nav-link">申請</a>
        <form method="POST" action="{{ route('logout') }}" class="site-header__logout-form">
            @csrf
            <button type="submit" class="site-header__nav-link site-header__nav-link--button">
                ログアウト
            </button>
        </form>
    </nav>
</header>

<main class="site-main">
    @yield('content')
</main>

</body>
</html>
