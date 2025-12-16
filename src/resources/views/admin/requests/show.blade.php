@extends('layouts.app')

@section('content')
<main class="admin-main">
  <div class="admin-card admin-detail">

    <div class="admin-heading">
      <span class="admin-heading__bar"></span>
      <h1 class="admin-heading__title">勤怠詳細</h1>
    </div>

    <div class="admin-detail__table-wrap">
      <table class="admin-detail__table">
        <tr>
          <th class="admin-detail__th">名前</th>
          <td class="admin-detail__td">{{ $request->user?->name }}</td>
        </tr>

        <tr>
          <th class="admin-detail__th">日付</th>
          <td class="admin-detail__td">
            {{ optional($request->attendance)->work_date }}
          </td>
        </tr>

        <tr>
          <th class="admin-detail__th">出勤・退勤</th>
          <td class="admin-detail__td">
            {{ $request->requested_clock_in_time ? substr($request->requested_clock_in_time, 0, 5) : '-' }}
            〜
            {{ $request->requested_clock_out_time ? substr($request->requested_clock_out_time, 0, 5) : '-' }}
          </td>
        </tr>

        <tr>
          <th class="admin-detail__th">休憩</th>
          <td class="admin-detail__td">
            @if($request->breaks && $request->breaks->count())
              @foreach($request->breaks as $b)
                <div>
                  {{ $b->requested_break_start ? substr($b->requested_break_start, 0, 5) : '-' }}
                  〜
                  {{ $b->requested_break_end ? substr($b->requested_break_end, 0, 5) : '-' }}
                </div>
              @endforeach
            @else
              -
            @endif
          </td>
        </tr>

        <tr>
          <th class="admin-detail__th">備考</th>
          <td class="admin-detail__td">{{ $request->reason }}</td>
        </tr>
      </table>
    </div>

    <div class="admin-detail__actions">
      @if($request->status === 0)
        <form method="POST" action="{{ route('admin.requests.approve', $request) }}">
          @csrf
          <button type="submit" class="admin-detail__submit">承認</button>
        </form>
      @else
        <button type="button" class="admin-detail__submit" style="background:#666;" disabled>承認済み</button>
      @endif
    </div>

  </div>
</main>
@endsection
