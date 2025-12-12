@extends('layouts.app')

@section('content')
<main class="admin-main">
  <div class="admin-card admin-staff-card2">
    <div class="admin-heading">
      <div class="admin-heading__bar"></div>
      <h1 class="admin-heading__title">スタッフ一覧</h1>
    </div>

    <div class="admin-staff-table-wrap2">
      <table class="admin-staff-table2">
        <thead>
          <tr>
            <th class="col-name">名前</th>
            <th class="col-email">メールアドレス</th>
            <th class="col-link">月次勤怠</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($staff as $s)
            <tr>
              <td class="td-name">{{ $s->name }}</td>
              <td class="td-email">{{ $s->email }}</td>
              <td class="td-link">
                <a class="link-detail" href="{{ route('admin.staff.attendance', $s) }}">詳細</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

  </div>
</main>
@endsection
