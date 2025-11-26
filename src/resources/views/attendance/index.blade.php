@extends('layouts.app')

@section('content')
<div class="attendance-main">
    <div class="attendance-card">

        <div class="attendance-status-wrap">
            @if ($status === 0)
                <span class="badge badge--gray">勤務外</span>
            @elseif ($status === 1)
                <span class="badge badge--gray">出勤中</span>
            @elseif ($status === 2)
                <span class="badge badge--gray">休憩中</span>
            @elseif ($status === 3)
                <span class="badge badge--gray">退勤済</span>
            @endif
        </div>

        <p class="attendance-date">
            {{ $today->format('Y年n月j日') }}({{ ['日','月','火','水','木','金','土'][$today->dayOfWeek] }})
        </p>

        <p class="attendance-time">
            {{ $displayTime }}
        </p>

        <div class="attendance-buttons">

            @if ($status === 0)
                <form method="POST" action="{{ route('attendance.store') }}">
                    @csrf
                    <input type="hidden" name="action" value="clock_in">
                    <button type="submit" class="btn btn--primary btn--wide">
                        出勤
                    </button>
                </form>

            @elseif ($status === 1)
                <div class="attendance-button-row">
                    <form method="POST" action="{{ route('attendance.store') }}">
                        @csrf
                        <input type="hidden" name="action" value="clock_out">
                        <button type="submit" class="btn btn--primary">
                            退勤
                        </button>
                    </form>

                    <form method="POST" action="{{ route('attendance.store') }}">
                        @csrf
                        <input type="hidden" name="action" value="break_in">
                        <button type="submit" class="btn btn--secondary">
                            休憩入
                        </button>
                    </form>
                </div>

            @elseif ($status === 2)
                <form method="POST" action="{{ route('attendance.store') }}">
                    @csrf
                    <input type="hidden" name="action" value="break_out">
                    <button type="submit" class="btn btn--primary btn--wide">
                        休憩戻
                    </button>
                </form>

            @elseif ($status === 3)
                <p class="attendance-message">
                    お疲れ様でした。
                </p>
            @endif

        </div>
    </div>
</div>
@endsection