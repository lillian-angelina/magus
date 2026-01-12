<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @yield('title')
    <title>U-FRET風 ダイアグラム・スクロール</title>
    <link rel="stylesheet" href="{{ asset('css/tab.css') }}">
    @yield('css')
</head>

<body>
    <header class="create">
        <div class="header-h">
            <a class="home" href="{{ url('/') }}">メーガス4兄弟</a>
        </div>
        <div class="header-h">
            <a class="create-button" href="{{ route('songs.create') }}">Tab譜作成</a>
            <a class="create-button" href="{{ route('songs.index') }}">一覧</a>
        </div>
    </header>
    @yield('content')
    @yield('js')
</body>

</html>