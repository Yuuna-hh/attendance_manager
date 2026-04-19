@extends('layouts.app')

@section('title', 'メール認証')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="verify">
    <p class="verify__text">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    <!-- Mailhogへの導線（ローカルならMailhogがおすすめ）-->
    <div class="verify__cta">
        <a class="verify__button" href="{{ config('app.mail_inbox_url', 'http://localhost:8025') }}" target="_blank" rel="noopener">
            認証はこちらから
        </a>
    </div>

    <!-- 認証メール再送：verification.send は POST -->
    <form method="POST" action="{{ route('verification.send') }}" class="verify__resend">
        @csrf
        <button type="submit" class="verify__link">認証メールを再送する</button>
    </form>

    @if (session('status') === 'verification-link-sent')
        <p class="verify__status">認証メールを再送しました。</p>
    @endif
</div>
@endsection
