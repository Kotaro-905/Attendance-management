{{-- resources/views/admin/attendance/index.blade.php --}}
@php
use Carbon\Carbon;
@endphp

@extends('layouts.app')

@section('title', '勤怠一覧（管理者）')

@section('content')
<main class="admin-main">
    <div class="admin-card">

        {{-- タイトル部分 --}}
        <div class="admin-heading">
            <span class="admin-heading__bar"></span>
            <h1 class="admin-heading__title">
                {{ $targetDate->format('Y年n月j日の勤怠') }}
            </h1>
        </div>

        {{-- 日付ナビ --}}
        <div class="admin-date-nav">
            <a href="{{ route('admin.attendance.index', ['date' => $prevDate->toDateString()]) }}"
                class="admin-date-nav__button">
                ← 前日
            </a>

            <div class="admin-date-nav__current">
                <span class="admin-date-nav__icon">📅</span>
                <span class="admin-date-nav__text">
                    {{ $targetDate->format('Y/m/d') }}
                </span>
            </div>

            <a href="{{ route('admin.attendance.index', ['date' => $nextDate->toDateString()]) }}"
                class="admin-date-nav__button admin-date-nav__button--right">
                翌日 →
            </a>
        </div>

        {{-- 一覧テーブル --}}
        @if ($attendances->isEmpty())
        <p class="admin-empty">該当日の勤怠データはありません。</p>
        @else
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="admin-table__col-name">名前</th>
                    <th class="admin-table__col-time">出勤</th>
                    <th class="admin-table__col-time">退勤</th>
                    <th class="admin-table__col-time">休憩</th>
                    <th class="admin-table__col-total">合計</th>
                    <th class="admin-table__col-detail">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                @php
                // 出勤・退勤（datetime でも time でも OK なように parse を使う）
                $clockIn = ($attendance->clock_in_at ?? '') !== ''
                ? Carbon::parse($attendance->clock_in_at)
                : null;
                $clockOut = ($attendance->clock_out_at ?? '') !== ''
                ? Carbon::parse($attendance->clock_out_at)
                : null;

                // 休憩
                $breakStart = ($attendance->break_start_at ?? '') !== '' &&
                ($attendance->break_end_at ?? '') !== ''
                ? Carbon::parse($attendance->break_start_at)
                : null;

                $breakEnd = ($attendance->break_start_at ?? '') !== '' &&
                ($attendance->break_end_at ?? '') !== ''
                ? Carbon::parse($attendance->break_end_at)
                : null;

                // 休憩時間（分）
                $breakMinutes = ($breakStart && $breakEnd)
                ? $breakEnd->diffInMinutes($breakStart)
                : 0;

                // 合計勤務時間（分）
                if ($clockIn && $clockOut) {
                $totalMinutes = $clockOut->diffInMinutes($clockIn) - $breakMinutes;
                if ($totalMinutes < 0) {
                    $totalMinutes=0;
                    }
                    } else {
                    $totalMinutes=null;
                    }

                    // 表示用フォーマット
                    $clockInDisplay=$clockIn ? $clockIn->format('H:i') : '-';
                    $clockOutDisplay = $clockOut ? $clockOut->format('H:i') : '-';

                    if ($breakMinutes > 0) {
                    $breakHour = floor($breakMinutes / 60);
                    $breakMin = $breakMinutes % 60;
                    $breakDisplay = sprintf('%d:%02d', $breakHour, $breakMin);
                    } else {
                    $breakDisplay = '-';
                    }

                    if (!is_null($totalMinutes)) {
                    $totalHour = floor($totalMinutes / 60);
                    $totalMin = $totalMinutes % 60;
                    $totalDisplay = sprintf('%d:%02d', $totalHour, $totalMin);
                    } else {
                    $totalDisplay = '-';
                    }
                    @endphp

                    <tr>
                        <td class="admin-table__cell-name">
                            {{ $attendance->user->name }}
                        </td>
                        <td class="admin-table__cell-time">
                            {{ $clockInDisplay }}
                        </td>
                        <td class="admin-table__cell-time">
                            {{ $clockOutDisplay }}
                        </td>
                        <td class="admin-table__cell-time">
                            {{ $breakDisplay }}
                        </td>
                        <td class="admin-table__cell-total">
                            {{ $totalDisplay }}
                        </td>
                        <td class="admin-table__cell-detail">
                            <a href="{{ route('admin.attendance.show', ['attendance' => $attendance->id]) }}"
                                class="admin-table__detail-link">
                                詳細
                            </a>
                        </td>
                    </tr>
                    @endforeach
            </tbody>
        </table>
        @endif
    </div>
</main>
@endsection