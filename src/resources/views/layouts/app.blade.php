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
    <header>
        <nav>
            <ul>
                <li><a href="{{ url('/') }}">ホーム</a></li>
                <li><a href="{{ url('/about') }}">メーガス4兄弟</a></li>
                <li><a href="{{ url('/brothers') }}">兄弟紹介</a></li>
                <li><a href="{{ url('/contact') }}">お問い合わせ</a></li>
            </ul>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>

    </footer>

    @yield('js')
</body>

</html>