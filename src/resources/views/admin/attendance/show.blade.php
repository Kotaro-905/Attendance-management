{{-- resources/views/admin/attendance/show.blade.php --}}
@extends('layouts.app')

@section('title', '勤怠詳細（管理者）')

@section('content')
@php
    use Carbon\Carbon;

    // 出勤・退勤（バリデーションエラー時は old() を優先）
    $clockInValue = old('clock_in_at');
    if ($clockInValue === null) {
        $clockInValue = $clockIn ? $clockIn->format('H:i') : '';
    }

    $clockOutValue = old('clock_out_at');
    if ($clockOutValue === null) {
        $clockOutValue = $clockOut ? $clockOut->format('H:i') : '';
    }
@endphp

<main class="admin-main">
    <div class="admin-card admin-detail">

        {{-- 見出し --}}
        <div class="admin-heading">
            <span class="admin-heading__bar"></span>
            <h1 class="admin-heading__title">勤怠詳細</h1>
        </div>

        <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- バリデーションエラー表示 --}}
            @if ($errors->any())
                <div class="form-errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li class="form-error-item">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="admin-detail__table-wrap">
                <table class="admin-detail__table">
                    <tbody>
                        {{-- 名前 --}}
                        <tr>
                            <th class="admin-detail__th">名前</th>
                            <td class="admin-detail__td">
                                {{ $attendance->user->name }}
                            </td>
                        </tr>

                        {{-- 日付 --}}
                        <tr>
                            <th class="admin-detail__th">日付</th>
                            <td class="admin-detail__td admin-detail__date-row">
                                <span>{{ $workDate->format('Y年') }}</span>
                                <span>{{ $workDate->format('n月j日') }}</span>
                            </td>
                        </tr>

                        {{-- 出勤・退勤 --}}
                        <tr>
                            <th class="admin-detail__th">出勤・退勤</th>
                            <td class="admin-detail__td admin-detail__time-range">
                                <input
                                    type="time"
                                    name="clock_in_at"
                                    class="admin-detail__input-time"
                                    value="{{ $clockInValue }}"
                                >

                                <span class="admin-detail__tilde">〜</span>

                                <input
                                    type="time"
                                    name="clock_out_at"
                                    class="admin-detail__input-time"
                                    value="{{ $clockOutValue }}"
                                >
                            </td>
                        </tr>

                        {{-- 休憩（一般ユーザーが押した回数ぶん＋最低1行、上限なし） --}}
                        @for ($i = 1; $i <= $breakRowCount; $i++)
                            @php
                                /** @var \App\Models\AttendanceBreak|null $break */
                                $break = $breaks[$i - 1] ?? null;

                                // DB からの初期値
                                $defaultStart = '';
                                $defaultEnd   = '';

                                if ($break && $break->start_at) {
                                    $defaultStart = Carbon::parse($break->start_at)->format('H:i');
                                }
                                if ($break && $break->end_at) {
                                    $defaultEnd = Carbon::parse($break->end_at)->format('H:i');
                                }

                                // old() があればそちら優先
                                $startValue = old('breaks.'.$i.'.start', $defaultStart);
                                $endValue   = old('breaks.'.$i.'.end',   $defaultEnd);
                            @endphp

                            <tr>
                                <th class="admin-detail__th">休憩{{ $i }}</th>
                                <td class="admin-detail__td admin-detail__time-range">
                                    <input
                                        type="time"
                                        name="breaks[{{ $i }}][start]"
                                        class="admin-detail__input-time"
                                        value="{{ $startValue }}"
                                    >

                                    <span class="admin-detail__tilde">〜</span>

                                    <input
                                        type="time"
                                        name="breaks[{{ $i }}][end]"
                                        class="admin-detail__input-time"
                                        value="{{ $endValue }}"
                                    >
                                </td>
                            </tr>
                        @endfor

                        {{-- 備考 --}}
                        <tr>
                            <th class="admin-detail__th">備考</th>
                            <td class="admin-detail__td">
                                <textarea
                                    name="remarks"
                                    class="admin-detail__textarea"
                                    rows="3"
                                >{{ old('remarks', $attendance->remarks) }}</textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- ボタン --}}
            <div class="admin-detail__actions">
                <button type="submit" class="admin-detail__submit">
                    修正
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
