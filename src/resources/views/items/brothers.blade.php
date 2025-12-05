@extends('layouts/app')

@section('title')
    <title></title>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/brothers.css') }}">
@endsection

@section('content')
<div class="brothers-container">
    <h1 class="brothers-title">メーガス4兄弟紹介</h1>
    <div class="brothers-list">
        <div class="brother-item">
            <h2 class="brother-name"><a href="/eldest-son" class="eldest-son">長男</a></h2>
            <p class="brother-description"></p>
        </div>
        <div class="brother-item">
            <h2 class="brother-name"><a href="second-son" class="second-son">次男</a></h2>
            <p class="brother-description"></p>
        </div>
        <div class="brother-item">
            <h2 class="brother-name"><a href="third-son" class="third-son">三男</a></h2>
            <p class="brother-description"></p>
        </div>
        <div class="brother-item">
            <h2 class="brother-name"><a href="fourth-son" class="fourth-son">四男</a></h2>
            <p class="brother-description"></p>
        </div>
    </div>
</div>
@endsection