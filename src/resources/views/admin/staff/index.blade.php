@extends('layouts.app')

@section('content')
<main class="admin-main">
  <div class="admin-card staff-card">
    <div class="admin-heading">
      <div class="admin-heading__bar"></div>
      <h1 class="admin-heading__title">スタッフ一覧</h1>
    </div>

    <table class="staff-table">
      <thead>
        <tr>
          <th class="staff-table__col-name">名前</th>
          <th class="staff-table__col-email">メールアドレス</th>
          <th class="staff-table__col-month">月次勤怠</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($staff as $s)
          <tr>
            <td class="staff-table__cell-name">{{ $s->name }}</td>
            <td class="staff-table__cell-email">{{ $s->email }}</td>
            <td class="staff-table__cell-month">
              <a class="staff-table__detail-link" href="{{ route('admin.staff.attendance', $s) }}">詳細</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

  </div>
</main>
@endsection
