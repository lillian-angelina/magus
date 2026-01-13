@extends('layouts/tab')

@section('content')
<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">保存済みコード譜一覧</h2>
</div>

<table border="1" style="width: 100%; border-collapse: collapse; background: white; table-layout: fixed; border: 1px solid #ddd;">
    <thead>
        <tr style="background: #f8f9fa;">
            <th style="padding: 12px; text-align: left; width: 50%;">タイトル（クリックでプレビュー）</th>
            <th style="padding: 12px; width: 20%;">更新日</th>
            <th style="padding: 12px; width: 30%;">操作</th>
        </tr>
    </thead>
    <tbody>
        @forelse($songs as $song)
        <tr style="border-bottom: 1px solid #eee;">
            {{-- タイトル：詳細画面(show)へ --}}
            <td style="padding: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <a href="{{ route('songs.show', $song->id) }}" 
                   style="color: #007bff; text-decoration: none; font-weight: bold; font-size: 1.1em;">
                    {{ $song->title }}
                </a>
            </td>
            
            <td style="padding: 12px; text-align: center; color: #666;">
                {{ $song->updated_at->format('Y/m/d') }}
            </td>

            <td style="padding: 12px;">
                <div style="display: flex; gap: 8px; justify-content: center;">
                    {{-- 編集ボタン --}}
                    <a href="{{ route('songs.edit', $song->id) }}" 
                       style="background: #007bff; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 0.9em;">
                       編集
                    </a>

                    {{-- 削除ボタン --}}
                    <form action="{{ route('songs.destroy', $song->id) }}" method="POST" 
                          onsubmit="return confirm('本当に「{{ $song->title }}」を削除しますか？');" style="margin: 0;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.9em;">
                            削除
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="3" style="padding: 40px; text-align: center; color: #888;">
                保存された曲はありません。
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection