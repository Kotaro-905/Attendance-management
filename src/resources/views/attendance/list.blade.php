@extends('layouts.app')

@section('title', '勤怠一覧')

@section('content')
<main class="staff-month-main">
    <div class="staff-month-card">

        <div class="admin-heading staff-month-heading">
            <span class="admin-heading__bar"></span>
            <h1 class="admin-heading__title">勤怠一覧</h1>
        </div>

        {{-- 月ナビ --}}
        <div class="staff-month-nav">
            <a href="{{ request()->fullUrlWithQuery(['month' => $prevMonth]) }}"
   class="staff-month-nav__button staff-month-nav__button--prev">
    ← 前月
</a>

            <div class="staff-month-nav__current">
                <span class="staff-month-nav__icon">📅</span>
                <span>{{ $monthStart->format('Y/m') }}</span>
            </div>

            <a href="{{ request()->fullUrlWithQuery(['month' => $nextMonth]) }}"
   class="staff-month-nav__button staff-month-nav__button--next">
    翌月 →
</a>
        </div>

        <div class="staff-month-table-wrap">
            <table class="staff-month-table">
                <thead>
                    <tr>
                        <th class="col-date">日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($days as $row)
                        <tr>
                            <td class="col-date">{{ $row['date_label'] }}</td>
                            <td>{{ $row['clock_in'] }}</td>
                            <td>{{ $row['clock_out'] }}</td>
                            <td>{{ $row['break'] }}</td>
                            <td>{{ $row['total'] }}</td>
                            <td>
                                @if($row['attendance_id'])
                                    <a class="staff-month-detail-link"
                                       href="{{ route('attendance.show', ['attendance' => $row['attendance_id']]) }}">
                                        詳細
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</main>
@endsection
