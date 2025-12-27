@extends('layouts.app')

@section('title', $user->name . 'さんの勤怠')

@section('content')
<main class="admin-main">
    <div class="admin-card">

        <div class="admin-heading">
            <span class="admin-heading__bar"></span>
            <h1 class="admin-heading__title">{{ $user->name }}さんの勤怠</h1>
        </div>

        {{-- 月ナビ（勤怠一覧の日付ナビを流用） --}}
        <div class="admin-date-nav">
            <a href="{{ route('admin.staff.attendance', ['user' => $user->id, 'month' => $prevMonth]) }}"
               class="admin-date-nav__button">
                ← 前月
            </a>

            <div class="admin-date-nav__current">
                <span class="admin-date-nav__icon">📅</span>
                <span class="admin-date-nav__text">{{ $monthStart->format('Y/m') }}</span>
            </div>

            <a href="{{ route('admin.staff.attendance', ['user' => $user->id, 'month' => $nextMonth]) }}"
               class="admin-date-nav__button admin-date-nav__button--right">
                翌月 →
            </a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th class="admin-table__col-name">日付</th>
                    <th class="admin-table__col-time">出勤</th>
                    <th class="admin-table__col-time">退勤</th>
                    <th class="admin-table__col-time">休憩</th>
                    <th class="admin-table__col-total">合計</th>
                    <th class="admin-table__col-detail">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($days as $row)
                <tr>
                    <td class="admin-table__cell-name">{{ $row['date_label'] }}</td>
                    <td class="admin-table__cell-time">{{ $row['clock_in'] }}</td>
                    <td class="admin-table__cell-time">{{ $row['clock_out'] }}</td>
                    <td class="admin-table__cell-time">{{ $row['break'] }}</td>
                    <td class="admin-table__cell-total">{{ $row['total'] }}</td>
                    <td class="admin-table__cell-detail">
                        @if($row['attendance_id'])
                            <a href="{{ route('admin.attendance.show', ['attendance' => $row['attendance_id']]) }}"
                               class="admin-table__detail-link">
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

        <div class="staff-month-export">
  <a
    href="{{ route('admin.staff.attendance.csv', ['user' => $user->id, 'month' => $monthStart->format('Y-m')]) }}"
    class="staff-month-export__button"
  >
    CSV出力
  </a>
</div>

    </div>
</main>
@endsection
