@extends('layouts.app')

@section('title', 'メール認証')

@section('content')
@php
    $isPrompt = request()->boolean('prompt'); // /email/verify?prompt=1 のとき true
@endphp

@php $isPrompt = request()->boolean('prompt'); @endphp

<main class="verify-page {{ $isPrompt ? 'is-prompt' : '' }}">
    <div class="verify-box">
        <p class="verify-message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        {{-- ✅ 「認証はこちらから」：再送(POST) → 画面を ?prompt=1 にして、MailHogも開く --}}
        @if(!$isPrompt && app()->environment('local'))
    <form method="POST" action="{{ route('verification.send') }}"
          onsubmit="window.open('http://localhost:8025', '_blank');">
        @csrf
        <button type="submit" class="verify-button">
            認証はこちらから
        </button>
    </form>
@endif

        {{-- ✅ 赤文字画面（prompt=1のとき）でも再送リンクは出す --}}
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="verify-resend">認証メールを再送する</button>
        </form>
    </div>
</main>
@endsection
