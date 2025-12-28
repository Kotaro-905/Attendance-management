{{-- resources/views/admin/attendance/show.blade.php --}}
@extends('layouts.app')

@section('title', '勤怠詳細（管理者）')

@section('content')
@php
    use Carbon\Carbon;

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

        <div class="admin-heading">
            <span class="admin-heading__bar"></span>
            <h1 class="admin-heading__title">勤怠詳細</h1>
        </div>

        <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- ★ここ：wrapを細く＆中央寄せ --}}
            <div class="admin-detail__table-wrap admin-detail__table-wrap--narrow">
                {{-- ★ここ：ラベル列を広げる --}}
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
                                    $defaultEnd = Carbon::parse($break->end_at)->format('H:i');
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
                                {{-- ★ここ：備考を細め中央寄せ --}}
                                <textarea name="remarks"
                                    class="admin-detail__textarea admin-detail__textarea--narrow"
                                    rows="3">{{ old('remarks', $attendance->remarks) }}</textarea>

                                @error('remarks')
                                    <p class="form-error-item">{{ $message }}</p>
                                @enderror
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="admin-detail__actions">
                <button type="submit" class="admin-detail__submit">修正</button>
            </div>
        </form>
    </div>
</main>
@endsection
