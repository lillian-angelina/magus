@extends('layouts/app')

@section('title')
    <title>お問い合わせ</title>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}">
@endsection

@section('content')
    <div class="contact-container">
        <h1 class="contact-title">お問い合わせ</h1>
        <div class="contact-list">
            <div class="contact-items">
                <p class="contact-item">メールアドレス</p>
                <p class="contact-item"></p>
            </div>
        </div>
    </div>
@endsection