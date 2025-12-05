@extends('layouts/app')

@section('title')
    <title>メーガス4兄弟紹介</title>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/brothers-items.css') }}">
@endsection

@section('content')
<div class="brothers-container">
    <h1 class="brothers-title">メーガス4兄弟紹介</h1>
    <div class="brothers-list">
        <div class="brother-item">
            <h2 class="brother-name">四男</h2>
            <p class="brother-description"></p>
        </div>
    </div>
</div>
@endsection