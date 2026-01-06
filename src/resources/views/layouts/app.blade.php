<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @yield('title')
    <title>メーガス4兄弟</title>
    <link rel="stylesheet" href="{{ asset('css/layouts-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header-h">
            <a class="home" href="{{ url('/') }}">メーガス4兄弟</a>
        </div>
        <div class="list">
            <a class="list-item" href="{{ url('/brothers') }}">兄弟紹介</a>
            <a class="list-item" href="{{ url('/items') }}">TAB譜</a>
            <a class="list-item" href="{{ url('/contact') }}">お問い合わせ</a>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>

    </footer>

    @yield('js')
</body>

</html>