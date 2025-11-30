<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'COACHTECH' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="site-header">
    <div class="site-header__inner">
        @php
            use Illuminate\Support\Facades\Auth;
            $user = Auth::user();
        @endphp

        {{-- ロゴのリンク先をロールで出し分け --}}
        @if ($user && method_exists($user, 'isAdmin') && $user->isAdmin())
            {{-- 管理者：勤怠一覧へ --}}
            <a href="{{ route('admin.attendance.index') }}" class="site-header__logo">
                <img src="{{ asset('images/logo.svg') }}" alt="CT COACHTECH">
            </a>
        @else
            {{-- 一般ユーザー：勤怠登録画面へ --}}
            <a href="{{ route('attendance.index') }}" class="site-header__logo">
                <img src="{{ asset('images/logo.svg') }}" alt="CT COACHTECH">
            </a>
        @endif

        <nav class="site-header__nav">
            @auth
                @if ($user && method_exists($user, 'isGeneral') && $user->isGeneral())
                    {{-- 一般ユーザー用メニュー --}}
                    {{-- ここは画面があるので本物のルート --}}
                    <a href="{{ route('attendance.index') }}" class="site-header__nav-link">
                        勤怠
                    </a>

                    {{-- まだ画面が無いのでダミーリンク --}}
                    <a href="#" class="site-header__nav-link" onclick="return false;">
                        勤怠一覧
                    </a>
                    <a href="#" class="site-header__nav-link" onclick="return false;">
                        申請
                    </a>

                    <form action="{{ route('logout') }}" method="POST" class="site-header__logout-form">
                        @csrf
                        <button type="submit"
                                class="site-header__nav-link site-header__nav-link--button">
                            ログアウト
                        </button>
                    </form>

                @elseif ($user && method_exists($user, 'isAdmin') && $user->isAdmin())
                    {{-- 管理者用メニュー --}}
                    {{-- 勤怠一覧だけ実装済み --}}
                    <a href="{{ route('admin.attendance.index') }}" class="site-header__nav-link">
                        勤怠一覧
                    </a>

                    {{-- ここから下はまだなのでダミー --}}
                    <a href="#" class="site-header__nav-link" onclick="return false;">
                        スタッフ一覧
                    </a>
                    <a href="#" class="site-header__nav-link" onclick="return false;">
                        申請一覧
                    </a>

                    <form action="{{ route('admin.logout') }}" method="POST" class="site-header__logout-form">
                        @csrf
                        <button type="submit"
                                class="site-header__nav-link site-header__nav-link--button">
                            ログアウト
                        </button>
                    </form>
                @endif
            @endauth
        </nav>
    </div>
</header>

<main class="site-main">
    @yield('content')
</main>
</body>
</html>
