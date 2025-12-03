{{-- resources/views/admin/attendance/show.blade.php --}}
@extends('layouts.app')

@section('title', '勤怠詳細（管理者）')

@section('content')
@php
    use Carbon\Carbon;

    $clockInValue    = $clockIn    ? $clockIn->format('H:i')    : '';
    $clockOutValue   = $clockOut   ? $clockOut->format('H:i')   : '';
    $breakStartValue = $breakStart ? $breakStart->format('H:i') : '';
    $breakEndValue   = $breakEnd   ? $breakEnd->format('H:i')   : '';
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
                                <input type="time"
                                       name="clock_in_at"
                                       class="admin-detail__input-time"
                                       value="{{ old('clock_in_at', $clockInValue) }}">

                                <span class="admin-detail__tilde">〜</span>

                                <input type="time"
                                       name="clock_out_at"
                                       class="admin-detail__input-time"
                                       value="{{ old('clock_out_at', $clockOutValue) }}">
                            </td>
                        </tr>

                        {{-- 休憩 --}}
                        <tr>
                            <th class="admin-detail__th">休憩</th>
                            <td class="admin-detail__td admin-detail__time-range">
                                <input type="time"
                                       name="break_start_at"
                                       class="admin-detail__input-time"
                                       value="{{ old('break_start_at', $breakStartValue) }}">

                                <span class="admin-detail__tilde">〜</span>

                                <input type="time"
                                       name="break_end_at"
                                       class="admin-detail__input-time"
                                       value="{{ old('break_end_at', $breakEndValue) }}">
                            </td>
                        </tr>

                        {{-- 休憩2（ダミー項目） --}}
                        <tr>
                            <th class="admin-detail__th">休憩2</th>
                            <td class="admin-detail__td admin-detail__time-range">
                                <input type="time"
                                       name="break2_start"
                                       class="admin-detail__input-time"
                                       value="">
                                <span class="admin-detail__tilde">〜</span>
                                <input type="time"
                                       name="break2_end"
                                       class="admin-detail__input-time"
                                       value="">
                            </td>
                        </tr>

                        {{-- 備考 --}}
                        <tr>
                            <th class="admin-detail__th">備考</th>
                            <td class="admin-detail__td">
                                <textarea name="remarks"
                                          class="admin-detail__textarea"
                                          rows="3">{{ old('remarks', $attendance->remarks) }}</textarea>
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
