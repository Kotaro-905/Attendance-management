{{-- resources/views/attendance/show.blade.php --}}
@extends('layouts.app')

@section('title', '勤怠詳細')

@section('content')
@php
    use Carbon\Carbon;
    $disabled = $isPending ? 'disabled' : '';
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
            <p style="color:#ff4d4f; text-align:right; margin: 12px 0;">
                ※ 承認待ちのため修正はできません。
            </p>
        @endif

        <form action="{{ route('requests.store') }}" method="POST">
            @csrf
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

            {{-- ★ここ：wrapを細く＆中央寄せ --}}
            <div class="admin-detail__table-wrap admin-detail__table-wrap--narrow">
                {{-- ★ここ：ラベル列を広げる --}}
                <table class="admin-detail__table admin-detail__table--wide-label">
                    <tbody>
                        <tr>
                            <th class="admin-detail__th">名前</th>
                            <td class="admin-detail__td">{{ $attendance->user->name }}</td>
                        </tr>

                        <tr>
                            <th class="admin-detail__th">日付</th>
                            <td class="admin-detail__td admin-detail__date-row">
                                <span>{{ $workDate->format('Y年') }}</span>
                                <span>{{ $workDate->format('n月j日') }}</span>
                            </td>
                        </tr>

                        <tr>
                            <th class="admin-detail__th">出勤・退勤</th>
                            <td class="admin-detail__td admin-detail__time-range">
                                <input class="admin-detail__input-time" type="time" name="clock_in_at"
                                    value="{{ old('clock_in_at', $clockInValue) }}" {{ $disabled }}>
                                <span class="admin-detail__tilde">〜</span>
                                <input class="admin-detail__input-time" type="time" name="clock_out_at"
                                    value="{{ old('clock_out_at', $clockOutValue) }}" {{ $disabled }}>

                                {{-- エラー --}}
                                @error('clock_in_at')
                                    <p class="form-error-item">{{ $message }}</p>
                                @enderror
                                @error('clock_out_at')
                                    <p class="form-error-item">{{ $message }}</p>
                                @enderror
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
                                <td class="admin-detail__td admin-detail__time-range">
                                    <input class="admin-detail__input-time" type="time"
                                        name="breaks[{{ $i }}][start]"
                                        value="{{ old("breaks.$i.start", $start ? Carbon::parse($start)->format('H:i') : '') }}"
                                        {{ $disabled }}>
                                    <span class="admin-detail__tilde">〜</span>
                                    <input class="admin-detail__input-time" type="time"
                                        name="breaks[{{ $i }}][end]"
                                        value="{{ old("breaks.$i.end", $end ? Carbon::parse($end)->format('H:i') : '') }}"
                                        {{ $disabled }}>

                                    @error("breaks.$i.start")
                                        <p class="form-error-item">{{ $message }}</p>
                                    @enderror
                                    @error("breaks.$i.end")
                                        <p class="form-error-item">{{ $message }}</p>
                                    @enderror
                                </td>
                            </tr>
                        @endfor

                        <tr>
                            <th class="admin-detail__th">備考</th>
                            <td class="admin-detail__td">
                                {{-- ★ここ：備考を細め中央寄せ --}}
                                <textarea class="admin-detail__textarea admin-detail__textarea--narrow"
                                    name="reason" rows="3" {{ $disabled }}>{{ old('reason', $displayReason) }}</textarea>

                                @error('reason')
                                    <p class="form-error-item">{{ $message }}</p>
                                @enderror
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @unless($isPending)
                <div class="admin-detail__actions">
                    <button type="submit" class="admin-detail__submit">修正</button>
                </div>
            @endunless
        </form>

    </div>
</main>
@endsection
