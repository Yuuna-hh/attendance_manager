@extends('layouts.app')

@section('title', 'スタッフ一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/staff_list.css') }}">
@endsection

@section('content')
<div class="staffList">

    <h2 class="pageTitle">
        <span class="pageTitle__bar"></span>
        スタッフ一覧
    </h2>

    <div class="staffList__tableWrap">
        <table class="staffList__table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staffs as $staff)
                    <tr>
                        <td>{{ $staff->name }}</td>
                        <td>{{ $staff->email }}</td>
                        <td>
                            <a
                                href="{{ route('admin_staff_attendance_list', ['id' => $staff->id]) }}"
                                class="staffList__detail"
                            >
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="staffList__empty">
                            スタッフ情報がありません
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection