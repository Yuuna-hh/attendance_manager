@extends('layouts.app')

@section('title', '管理者ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth">
    <div class="auth__container">
        <h1 class="auth__title">管理者ログイン</h1>

        <form method="POST" action="{{ route('admin_login') }}" class="auth__form" novalidate>
            @csrf

            {{-- メールアドレス --}}
            <div class="auth__field">
                <label class="auth__label" for="email">メールアドレス</label>
                <input
                    class="auth__input"
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                >
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            {{-- パスワード --}}
            <div class="auth__field">
                <label class="auth__label" for="password">パスワード</label>
                <input
                    class="auth__input"
                    type="password"
                    id="password"
                    name="password"
                >
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="auth__submit">
                管理者ログインする
            </button>
        </form>
    </div>
</div>
@endsection