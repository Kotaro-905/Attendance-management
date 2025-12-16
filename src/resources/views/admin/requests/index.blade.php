@extends('layouts.app')

@section('content')
<main class="admin-main">
    <div class="admin-card">

        <div class="admin-heading">
            <span class="admin-heading__bar"></span>
            <h1 class="admin-heading__title">申請一覧</h1>
        </div>

        {{-- タブ --}}
        <div class="admin-tabs">
            <a href="{{ route('admin.requests.index', ['status' => 'pending']) }}"
               class="admin-tab-link {{ $status === 'pending' ? 'is-active' : '' }}">
                承認待ち
            </a>
            <a href="{{ route('admin.requests.index', ['status' => 'approved']) }}"
               class="admin-tab-link {{ $status === 'approved' ? 'is-active' : '' }}">
                承認済み
            </a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
            @forelse($requests as $req)
                <tr>
                    <td>{{ $req->status === 0 ? '承認待ち' : '承認済み' }}</td>
                    <td>{{ $req->user?->name }}</td>
                    <td>{{ optional($req->attendance)->work_date }}</td>
                    <td>{{ $req->reason }}</td>
                    <td>{{ optional($req->created_at)->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('admin.requests.show', $req) }}">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;">データがありません</td>
                </tr>
            @endforelse
            </tbody>
        </table>

    </div>
</main>
@endsection
