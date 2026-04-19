@extends('layouts.app')

@section('title', '会員登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth">
    <div class="auth__container">
        <h1 class="auth__title">会員登録</h1>

        <form method="POST" action="{{ route('register') }}" class="auth__form" novalidate>
            @csrf

            <div class="auth__field">
                <label class="auth__label" for="name">名前</label>
                <input class="auth__input" type="text" id="name" name="name" value="{{ old('name') }}">
                @error('name')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth__field">
                <label class="auth__label" for="email">メールアドレス</label>
                <input class="auth__input" type="email" id="email" name="email" value="{{ old('email') }}">
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth__field">
                <label class="auth__label" for="password">パスワード</label>
                <input class="auth__input" type="password" id="password" name="password">
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth__field">
                <label class="auth__label" for="password_confirmation">パスワード確認</label>
                <input class="auth__input" type="password" id="password_confirmation" name="password_confirmation">
                @error('password_confirmation')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="auth__submit">
                登録する
            </button>
        </form>

        <div class="auth__bottom">
            <a href="{{ route('login') }}" class="auth__link">
                ログインはこちら
            </a>
        </div>
    </div>
</div>
@endsection