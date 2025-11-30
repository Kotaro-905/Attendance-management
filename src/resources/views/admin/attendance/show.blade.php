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
                                   class="admin-detail__input-time"
                                   value="{{ $clockInValue }}"
                                   readonly>
                            <span class="admin-detail__tilde">〜</span>
                            <input type="time"
                                   class="admin-detail__input-time"
                                   value="{{ $clockOutValue }}"
                                   readonly>
                        </td>
                    </tr>

                    {{-- 休憩 --}}
                    <tr>
                        <th class="admin-detail__th">休憩</th>
                        <td class="admin-detail__td admin-detail__time-range">
                            <input type="time"
                                   class="admin-detail__input-time"
                                   value="{{ $breakStartValue }}"
                                   readonly>
                            <span class="admin-detail__tilde">〜</span>
                            <input type="time"
                                   class="admin-detail__input-time"
                                   value="{{ $breakEndValue }}"
                                   readonly>
                        </td>
                    </tr>

                    {{-- 休憩2（DB には無いので空のままのダミー） --}}
                    <tr>
                        <th class="admin-detail__th">休憩2</th>
                        <td class="admin-detail__td admin-detail__time-range">
                            <input type="time" class="admin-detail__input-time" value="" readonly>
                            <span class="admin-detail__tilde">〜</span>
                            <input type="time" class="admin-detail__input-time" value="" readonly>
                        </td>
                    </tr>

                    {{-- 備考 --}}
                    <tr>
                        <th class="admin-detail__th">備考</th>
                        <td class="admin-detail__td">
                            <textarea class="admin-detail__textarea" rows="3" readonly>{{ $attendance->remarks }}</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- 修正ボタン（まだ処理が無いので type="button" に） --}}
        <div class="admin-detail__actions">
            <button type="button" class="admin-detail__submit">
                修正
            </button>
        </div>
    </div>
</main>
@endsection
