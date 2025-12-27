@extends('layouts.app')

@section('title', '修正申請承認')

@section('content')
@php
    use Carbon\Carbon;

    // ここは controller から compact('request') で渡ってくる想定
    $correction = $request;

    $workDate = $correction->attendance?->work_date
        ? Carbon::parse($correction->attendance->work_date)
        : null;

    $in  = $correction->requested_clock_in_time
        ? Carbon::parse($correction->requested_clock_in_time)->format('H:i')
        : '';

    $out = $correction->requested_clock_out_time
        ? Carbon::parse($correction->requested_clock_out_time)->format('H:i')
        : '';

    $breaks = $correction->breaks ?? collect();
    $breakRowCount = max(2, $breaks->count());
    $isApproved = (int)$correction->status === 1;
@endphp

<main class="admin-main">
    <div class="admin-card admin-detail">

        <div class="admin-heading admin-heading--between">
            <div class="admin-heading__left">
                <span class="admin-heading__bar"></span>
                <h1 class="admin-heading__title">勤怠詳細</h1>
            </div>

            @if($isApproved)
                <span class="status-badge status-badge--approved">承認済み</span>
            @endif
        </div>

        <div class="admin-detail__table-wrap admin-detail__table-wrap--narrow">
            <table class="admin-detail__table">
                <tbody>
                    <tr>
                        <th class="admin-detail__th">名前</th>
                        <td class="admin-detail__td">
                            {{ $correction->user?->name ?? $correction->attendance?->user?->name }}
                        </td>
                    </tr>

                    <tr>
                        <th class="admin-detail__th">日付</th>
                        <td class="admin-detail__td admin-detail__date-row">
                            @if($workDate)
                                <span>{{ $workDate->format('Y年') }}</span>
                                <span>{{ $workDate->format('n月j日') }}</span>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th class="admin-detail__th">出勤・退勤</th>
                        <td class="admin-detail__td admin-detail__time-range">
                            <input class="admin-detail__input-time" type="time" value="{{ $in }}" disabled>
                            <span class="admin-detail__tilde">〜</span>
                            <input class="admin-detail__input-time" type="time" value="{{ $out }}" disabled>
                        </td>
                    </tr>

                    @for($i = 1; $i <= $breakRowCount; $i++)
                        @php
                            $br = $breaks[$i-1] ?? null;
                            $bStart = $br?->requested_break_start ? Carbon::parse($br->requested_break_start)->format('H:i') : '';
                            $bEnd   = $br?->requested_break_end   ? Carbon::parse($br->requested_break_end)->format('H:i')   : '';
                        @endphp
                        <tr>
                            <th class="admin-detail__th">休憩{{ $i }}</th>
                            <td class="admin-detail__td admin-detail__time-range">
                                <input class="admin-detail__input-time" type="time" value="{{ $bStart }}" disabled>
                                <span class="admin-detail__tilde">〜</span>
                                <input class="admin-detail__input-time" type="time" value="{{ $bEnd }}" disabled>
                            </td>
                        </tr>
                    @endfor

                    <tr>
                        <th class="admin-detail__th">備考</th>
                        <td class="admin-detail__td">
                            <textarea class="admin-detail__textarea" rows="3" disabled>{{ $correction->reason ?? '' }}</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="admin-detail__actions">
            @unless($isApproved)
                <form action="{{ route('admin.requests.approve', $request) }}" method="POST">
                    @csrf
                    <button type="submit" class="admin-detail__submit">承認</button>
                </form>
            @endunless
        </div>

    </div>
</main>
@endsection
