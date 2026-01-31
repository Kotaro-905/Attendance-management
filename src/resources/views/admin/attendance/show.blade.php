{{-- resources/views/admin/attendance/show.blade.php --}}
@extends('layouts.app')

@section('title', '勤怠詳細（管理者）')

@section('content')
@php
    use Carbon\Carbon;

    // ✅ 申請あり表示で使う（controller から correctionRequest が渡ってくる想定）
    $req = $correctionRequest ?? null;

    // 申請側の勤怠（念のため）
    $reqAttendance = $req?->attendance;

    // 承認状態
    $isPending  = (bool)($isPending  ?? false);
    $isApproved = (bool)($isApproved ?? false);

    // 申請表示の対象日（申請日があればそれ、なければ勤怠日）
    $reqWorkDate = $req?->requested_work_date
        ? Carbon::parse($req->requested_work_date)
        : ($attendance?->work_date ? Carbon::parse($attendance->work_date) : null);

    // 申請表示の出退勤（time文字列は blade 側で H:i に整形）
    $reqClockIn  = $req?->requested_clock_in_time
        ? Carbon::parse($req->requested_clock_in_time)->format('H:i')
        : '';

    $reqClockOut = $req?->requested_clock_out_time
        ? Carbon::parse($req->requested_clock_out_time)->format('H:i')
        : '';

    // 申請表示の休憩（CorrectionBreak）
    $reqBreaks = $req?->breaks ?? collect();
    $reqBreakRowCount = max(2, $reqBreaks->count());

    // 申請理由
    $reqReason = $req?->reason ?? '';

    // ==============================
    // ✅ 通常フォーム用（あなたの元のまま）
    // ==============================
    $clockInValue = old('clock_in_at');
    if ($clockInValue === null) {
        $clockInValue = $clockIn ? $clockIn->format('H:i') : '';
    }

    $clockOutValue = old('clock_out_at');
    if ($clockOutValue === null) {
        $clockOutValue = $clockOut ? $clockOut->format('H:i') : '';
    }

    $breakCount = is_countable($breaks) ? count($breaks) : 0;
    $breakRowCount = max(2, $breakCount + 1);
@endphp

<main class="admin-main">
    <div class="admin-card admin-detail">

        <div class="admin-heading admin-heading--between">
            <div class="admin-heading__left">
                <span class="admin-heading__bar"></span>
                <h1 class="admin-heading__title">勤怠詳細</h1>
            </div>
        </div>

        {{-- =========================================================
             ✅ 申請がある勤怠：申請詳細の見た目（入力不可 + 承認ボタン）
           ========================================================= --}}
        @if(!empty($hasRequest) && $req)

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
                                @if($reqWorkDate)
                                    <span class="admin-detail__date-item admin-detail__date-item--left">{{ $reqWorkDate->format('Y年') }}</span>
                                    <span class="admin-detail__date-item admin-detail__date-item--right">{{ $reqWorkDate->format('n月j日') }}</span>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th class="admin-detail__th">出勤・退勤</th>
                            <td class="admin-detail__td">
                                <div class="admin-detail__time-range">
                                    <input class="admin-detail__input-time" type="time" value="{{ $reqClockIn }}" disabled>
                                    <span class="admin-detail__tilde">〜</span>
                                    <input class="admin-detail__input-time" type="time" value="{{ $reqClockOut }}" disabled>
                                </div>
                            </td>
                        </tr>

                        @for ($i = 1; $i <= $reqBreakRowCount; $i++)
                            @php
                                $br = $reqBreaks[$i - 1] ?? null;

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
                                <textarea class="admin-detail__textarea" rows="3" disabled>{{ $reqReason }}</textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="admin-detail__actions">
                @if($isApproved)
                    <span class="status-badge status-badge--approved">承認済み</span>
                @else
                    <form action="{{ route('admin.requests.approve', $req) }}" method="POST">
                        @csrf
                        <button type="submit" class="admin-detail__submit">承認</button>
                    </form>
                @endif
            </div>

        {{-- =========================================================
             ✅ 申請がない勤怠：従来どおり編集フォーム
           ========================================================= --}}
        @else

            <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
                @csrf
                @method('PUT')

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
                                        @error('clock_in_at')  <p class="form-error-item">{{ $message }}</p> @enderror
                                        @error('clock_out_at') <p class="form-error-item">{{ $message }}</p> @enderror
                                    </div>
                                </td>
                            </tr>

                            @for ($i = 1; $i <= $breakRowCount; $i++)
                                @php
                                    $break = $breaks[$i - 1] ?? null;

                                    $defaultStart = '';
                                    $defaultEnd   = '';

                                    if ($break && $break->start_at) {
                                        $defaultStart = Carbon::parse($break->start_at)->format('H:i');
                                    }
                                    if ($break && $break->end_at) {
                                        $defaultEnd   = Carbon::parse($break->end_at)->format('H:i');
                                    }

                                    $startValue = old('breaks.'.$i.'.start', $defaultStart);
                                    $endValue   = old('breaks.'.$i.'.end',   $defaultEnd);
                                @endphp

                                <tr>
                                    <th class="admin-detail__th">休憩{{ $i }}</th>
                                    <td class="admin-detail__td admin-detail__td--stack">
                                        <div class="admin-detail__time-range">
                                            <input type="time" name="breaks[{{ $i }}][start]" class="admin-detail__input-time" value="{{ $startValue }}">
                                            <span class="admin-detail__tilde">〜</span>
                                            <input type="time" name="breaks[{{ $i }}][end]" class="admin-detail__input-time" value="{{ $endValue }}">
                                        </div>

                                        <div class="admin-detail__errors">
                                            @error("breaks.$i.start") <p class="form-error-item">{{ $message }}</p> @enderror
                                            @error("breaks.$i.end")   <p class="form-error-item">{{ $message }}</p> @enderror
                                        </div>
                                    </td>
                                </tr>
                            @endfor

                            <tr>
                                <th class="admin-detail__th">備考</th>
                                <td class="admin-detail__td">
                                    <textarea name="remarks" class="admin-detail__textarea admin-detail__textarea--narrow" rows="3">{{ old('remarks', $attendance->remarks) }}</textarea>
                                    @error('remarks') <p class="form-error-item">{{ $message }}</p> @enderror
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