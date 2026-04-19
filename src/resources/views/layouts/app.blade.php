<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'COACHTECH')</title>

    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('css')
</head>

@php
    $isAuthPage = request()->routeIs(
        'login',
        'register',
        'admin_login',
        'verification.notice',
    );
@endphp

<body class="{{ $isAuthPage ? 'body--auth' : 'body--default' }}">

<header class="header">
    <div class="header__inner">
        <a class="header__logo"
            @auth
                href="{{ auth()->user()->isAdmin() 
                    ? route('admin_attendance_list') 
                    : route('attendance_index') }}"
            @else
                href="{{ route('login') }}"
            @endauth>
            <img src="{{ asset('images/COACHTECH_logo.png') }}" alt="COACHTECH">
        </a>

        @unless ($isAuthPage)
            <nav class="header__nav">
                @auth
                <!-- 管理者 -->
                    @if(auth()->user()->isAdmin())

                        <a href="{{ route('admin_attendance_list') }}" class="header__navButton">
                            勤怠一覧
                        </a>

                        <a href="{{ route('admin_staff_list') }}" class="header__navButton">
                            スタッフ一覧
                        </a>

                        <a href="{{ route('correction_request_list') }}" class="header__navButton">
                            申請一覧
                        </a>

                <!-- 一般ユーザー -->
                    @else

                        @php
                            $isFinished = isset($attendance)
                                && $attendance
                                && $attendance->status === \App\Models\Attendance::STATUS_FINISHED;
                        @endphp

                        @if(request()->routeIs('attendance_index') && $isFinished)

                            <a href="{{ route('attendance_list') }}" class="header__navButton">
                                今月の出勤一覧
                            </a>

                            <a href="{{ route('correction_request_list') }}" class="header__navButton">
                                申請一覧
                            </a>

                        @else
                            <a href="{{ route('attendance_index') }}" class="header__navButton">
                                勤怠
                            </a>

                            <a href="{{ route('attendance_list') }}" class="header__navButton">
                                勤怠一覧
                            </a>

                            <a href="{{ route('correction_request_list') }}" class="header__navButton">
                                申請
                            </a>

                        @endif

                    @endif

                    {{-- 共通ログアウト --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="header__navButton">
                            ログアウト
                        </button>
                    </form>

                @endauth

                </nav>
        @endunless
    </div>
</header>

<main class="main">
    @yield('content')
</main>

@yield('js')

</body>
</html>