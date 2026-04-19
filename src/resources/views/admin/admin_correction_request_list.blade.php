@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/correction_request_list.css') }}">
@endsection

@section('content')
<div class="correctionRequestList">

    <h2 class="pageTitle">
        <span class="pageTitle__bar"></span>
        申請一覧
    </h2>

    <div class="correctionRequestList__tabs">
        <a
            href="{{ route('correction_request_list', ['status' => 'pending']) }}"
            class="correctionRequestList__tab {{ $status === 'pending' ? 'correctionRequestList__tab--active' : '' }}"
        >
            承認待ち
        </a>

        <a
            href="{{ route('correction_request_list', ['status' => 'approved']) }}"
            class="correctionRequestList__tab {{ $status === 'approved' ? 'correctionRequestList__tab--active' : '' }}"
        >
            承認済み
        </a>
    </div>

    <div class="correctionRequestList__tableWrap">
        <table class="correctionRequestList__table">
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
                @forelse($requests as $requestItem)
                    <tr>
                        <td>
                            {{ $requestItem->status === 'approved' ? '承認済み' : '承認待ち' }}
                        </td>
                        <td>
                            {{ $requestItem->user->name }}
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($requestItem->attendance->work_date)->format('Y/m/d') }}
                        </td>
                        <td>
                            {{ $requestItem->requested_note }}
                        </td>
                        <td>
                            {{ optional($requestItem->created_at)->format('Y/m/d') }}
                        </td>
                        <td>
                            <a
                                href="{{ route('admin_correction_request_approve', ['attendance_correct_request_id' => $requestItem->id]) }}"
                                class="correctionRequestList__detail"
                            >
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="correctionRequestList__empty">
                            申請はありません
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection