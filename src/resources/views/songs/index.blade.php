@extends('layouts/tab')

@section('content')
<table border="1" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #eee;">
            <th>タイトル</th>
            <th>更新日</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        @foreach($songs as $song)
        <tr>
            <td style="padding: 10px;">{{ $song->title }}</td>
            <td>{{ $song->updated_at->format('Y/m/d') }}</td>
            <td style="display: flex; gap: 10px; padding: 10px;">
                <a href="{{ route('songs.editor', $song->id) }}" 
                   style="background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;">編集・PDF</a>

                <form action="{{ route('songs.destroy', $song->id) }}" method="POST" 
                      onsubmit="return confirm('本当にこの曲を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">削除</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection