@extends('layouts.app')

@section('title', '申請詳細')

@section('content')
@php
    use Carbon\Carbon;

    $isPending  = (int)($correctionRequest->status ?? 0) === 0;
    $isApproved = (int)($correctionRequest->status ?? 0) === 1;

    $attendance = $correctionRequest->attendance;
    $userName   = $attendance?->user?->name ?? '-';

    // 対象日（requested_work_date が無いなら勤怠日）
    $workDate = $correctionRequest->requested_work_date
        ? Carbon::parse($correctionRequest->requested_work_date)
        : ($attendance?->work_date ? Carbon::parse($attendance->work_date) : null);

    // 申請値（出退勤）
    $clockInValue  = $correctionRequest->requested_clock_in_time
        ? Carbon::parse($correctionRequest->requested_clock_in_time)->format('H:i')
        : '';

    $clockOutValue = $correctionRequest->requested_clock_out_time
        ? Carbon::parse($correctionRequest->requested_clock_out_time)->format('H:i')
        : '';

    // 申請値（休憩）
    $displayBreaks = $correctionRequest->breaks ?? collect();
    $breakRowCount = max(2, $displayBreaks->count());

    // 備考（申請理由）
    $displayReason = $correctionRequest->reason ?? '';
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

        @if($isPending)
            <p class="admin-detail__notice">
                ※ 承認待ちのため修正はできません。
            </p>
        @endif

        <div class="admin-detail__table-wrap">
            <table class="admin-detail__table admin-detail__table--request">
                <tbody>
                    <tr>
                        <th class="admin-detail__th">名前</th>
                        {{-- ★ 勤怠詳細と同じ寄せ方にする --}}
                        <td class="admin-detail__td admin-detail__td--name">
                            {{ $userName }}
                        </td>
                    </tr>

                    <tr>
                        <th class="admin-detail__th">日付</th>
                        {{-- ★ 勤怠詳細と同じ寄せ方にする --}}
                        <td class="admin-detail__td admin-detail__td--date">
                            @if($workDate)
                                <span class="admin-detail__date-item admin-detail__date-item--left">{{ $workDate->format('Y年') }}</span>
                                <span class="admin-detail__date-item admin-detail__date-item--right">{{ $workDate->format('n月j日') }}</span>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                    </tr>

                    {{-- ✅ 出勤・退勤（td丸ごと置き換え版） --}}
                    <tr>
                        <th class="admin-detail__th">出勤・退勤</th>
                        <td class="admin-detail__td">
                            <div class="admin-detail__time-range">
                                <input class="admin-detail__input-time" type="time" value="{{ $clockInValue }}" disabled>
                                <span class="admin-detail__tilde">〜</span>
                                <input class="admin-detail__input-time" type="time" value="{{ $clockOutValue }}" disabled>
                            </div>
                        </td>
                    </tr>

                    {{-- ✅ 休憩（td丸ごと置き換え版） --}}
                    @for ($i = 1; $i <= $breakRowCount; $i++)
                        @php
                            $br = $displayBreaks[$i - 1] ?? null;

                            $start = $br?->requested_break_start
                                ? Carbon::parse($br->requested_break_start)->format('H:i')
                                : '';

                            $end = $br?->requested_break_end
                                ? Carbon::parse($br->requested_break_end)->format('H:i')
                                : '';
                        @endphp

                        <tr>
                            <th class="admin-detail__th">休憩{{ $i }}</th>
                            <td class="admin-detail__td">
                                <div class="admin-detail__time-range">
                                    <input class="admin-detail__input-time" type="time" value="{{ $start }}" disabled>
                                    <span class="admin-detail__tilde">〜</span>
                                    <input class="admin-detail__input-time" type="time" value="{{ $end }}" disabled>
                                </div>
                            </td>
                        </tr>
                    @endfor

                    <tr>
                        <th class="admin-detail__th">備考</th>
                        <td class="admin-detail__td">
                            <textarea class="admin-detail__textarea" rows="3" disabled>{{ $displayReason }}</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="admin-detail__actions">
            <a href="{{ route('requests.index') }}" class="admin-table__detail-link">← 戻る</a>
        </div>

    </div>
</main>
@endsection
