{{-- resources/views/attendance/show.blade.php --}}
@extends('layouts.app')

@section('title', '勤怠詳細')

@section('content')
@php
    use Carbon\Carbon;

    /**
     * controller 側で渡ってくる想定:
     * $attendance, $workDate, $clockInValue, $clockOutValue, $displayBreaks, $breakRowCount, $displayReason
     * $isPending, $isApproved（申請状態）
     */

    // ✅ 申請がある（承認待ち/承認済み）なら編集不可にする
    $isPending  = (bool)($isPending  ?? false);
    $isApproved = (bool)($isApproved ?? false);
    $readonly   = $isPending || $isApproved;

    // readonly 表示用（空なら --:--）
    $dispClockIn  = !empty($clockInValue)  ? $clockInValue  : '--:--';
    $dispClockOut = !empty($clockOutValue) ? $clockOutValue : '--:--';
@endphp

<main class="admin-main">
    <div class="admin-card admin-detail">

        {{-- 見出し --}}
        <div class="admin-heading admin-heading--between">
            <div class="admin-heading__left">
                <span class="admin-heading__bar"></span>
                <h1 class="admin-heading__title">勤怠詳細</h1>
            </div>
        </div>

        {{-- 承認待ちメッセージ（いつもの位置） --}}
        @if($isPending)
            <p class="admin-detail__notice">※ 承認待ちのため修正はできません。</p>
        @endif

        {{-- =========================================
             ✅ 申請あり（承認待ち/承認済み）：表示専用
           ========================================= --}}
        @if($readonly)
            <div class="admin-detail__table-wrap admin-detail__table-wrap--narrow">
                <table class="admin-detail__table admin-detail__table--request">
                    <tbody>
                        <tr>
                            <th class="admin-detail__th">名前</th>
                            <td class="admin-detail__td admin-detail__td--name">
                                {{ $attendance->user->name }}
                            </td>
                        </tr>

                        <tr>
                            <th class="admin-detail__th">日付</th>
                            <td class="admin-detail__td admin-detail__td--date">
                                <span class="admin-detail__date-item admin-detail__date-item--left">{{ $workDate->format('Y年') }}</span>
                                <span class="admin-detail__date-item admin-detail__date-item--right">{{ $workDate->format('n月j日') }}</span>
                            </td>
                        </tr>

                        <tr>
                            <th class="admin-detail__th">出勤・退勤</th>
                            <td class="admin-detail__td">
                                <div class="admin-detail__time-range">
                                    <input class="admin-detail__input-time" type="time" value="{{ $dispClockIn  === '--:--' ? '' : $dispClockIn }}" disabled>
                                    <span class="admin-detail__tilde">〜</span>
                                    <input class="admin-detail__input-time" type="time" value="{{ $dispClockOut === '--:--' ? '' : $dispClockOut }}" disabled>
                                </div>
                            </td>
                        </tr>

                        @for ($i = 1; $i <= $breakRowCount; $i++)
                            @php
                                $br = $displayBreaks[$i - 1] ?? null;

                                $startRaw = $br?->start_at ?? $br?->requested_break_start;
                                $endRaw   = $br?->end_at   ?? $br?->requested_break_end;

                                $start = $startRaw ? Carbon::parse($startRaw)->format('H:i') : '';
                                $end   = $endRaw   ? Carbon::parse($endRaw)->format('H:i')   : '';
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

            {{-- ✅ 申請ありのときは修正ボタンは絶対に出さない --}}
            <div class="admin-detail__actions admin-detail__actions--right">
    @if($isApproved)
        <span class="status-badge status-badge--approved">承認済み</span>
    @endif
</div>

        {{-- =========================================
             ✅ 申請なし：通常の申請フォーム（編集可）
           ========================================= --}}
        @else
            <form action="{{ route('requests.store') }}" method="POST">
                @csrf
                <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

                <div class="admin-detail__table-wrap admin-detail__table-wrap--narrow">
                    <table class="admin-detail__table admin-detail__table--wide-label">
                        <tbody>
                            <tr>
                                <th class="admin-detail__th">名前</th>
                                <td class="admin-detail__td admin-detail__td--name">
                                    {{ $attendance->user->name }}
                                </td>
                            </tr>

                            <tr>
                                <th class="admin-detail__th">日付</th>
                                <td class="admin-detail__td admin-detail__td--date">
                                    <span class="admin-detail__date-item admin-detail__date-item--left">{{ $workDate->format('Y年') }}</span>
                                    <span class="admin-detail__date-item admin-detail__date-item--right">{{ $workDate->format('n月j日') }}</span>
                                </td>
                            </tr>

                            <tr>
                                <th class="admin-detail__th">出勤・退勤</th>
                                <td class="admin-detail__td admin-detail__td--stack">
                                    <div class="admin-detail__time-range">
                                        <input type="time" name="clock_in_at" class="admin-detail__input-time" value="{{ $clockInValue }}">
                                        <span class="admin-detail__tilde">〜</span>
                                        <input type="time" name="clock_out_at" class="admin-detail__input-time" value="{{ $clockOutValue }}">
                                    </div>

                                    <div class="admin-detail__errors">
                                        @error('clock_in_at') <p class="form-error-item">{{ $message }}</p> @enderror
                                        @error('clock_out_at') <p class="form-error-item">{{ $message }}</p> @enderror
                                    </div>
                                </td>
                            </tr>

                            @for ($i = 1; $i <= $breakRowCount; $i++)
                                @php
                                    $br = $displayBreaks[$i - 1] ?? null;
                                    $start = $br?->start_at ?? $br?->requested_break_start;
                                    $end   = $br?->end_at   ?? $br?->requested_break_end;
                                @endphp
                                <tr>
                                    <th class="admin-detail__th">休憩{{ $i }}</th>
                                    <td class="admin-detail__td admin-detail__td--stack">
                                        <div class="admin-detail__time-range">
                                            <input type="time"
                                                   name="breaks[{{ $i }}][start]"
                                                   class="admin-detail__input-time"
                                                   value="{{ old("breaks.$i.start", $start ? Carbon::parse($start)->format('H:i') : '') }}">
                                            <span class="admin-detail__tilde">〜</span>
                                            <input type="time"
                                                   name="breaks[{{ $i }}][end]"
                                                   class="admin-detail__input-time"
                                                   value="{{ old("breaks.$i.end", $end ? Carbon::parse($end)->format('H:i') : '') }}">
                                        </div>

                                        <div class="admin-detail__errors">
                                            @error("breaks.$i.start") <p class="form-error-item">{{ $message }}</p> @enderror
                                            @error("breaks.$i.end") <p class="form-error-item">{{ $message }}</p> @enderror
                                        </div>
                                    </td>
                                </tr>
                            @endfor

                            <tr>
                                <th class="admin-detail__th">備考</th>
                                <td class="admin-detail__td">
                                    <textarea class="admin-detail__textarea admin-detail__textarea--narrow"
                                              name="reason"
                                              rows="3">{{ old('reason', $displayReason) }}</textarea>
                                    @error('reason') <p class="form-error-item">{{ $message }}</p> @enderror
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="admin-detail__actions">
                    <button type="submit" class="admin-detail__submit">修正</button>
                </div>
            </form>
        @endif

    </div>
</main>
@endsection