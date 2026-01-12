<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;

class SongController extends Controller
{
    // 一覧表示
    public function index()
    {
        $songs = Song::orderBy('updated_at', 'desc')->get();
        return view('songs.index', compact('songs'));
    }

    public function show(Song $song)
    {
        // 指定された ID の曲データを show.blade.php に渡す
        return view('songs.show', compact('song'));
    }

    // 保存（新規作成）
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);
        $song = Song::create($validated);
        return response()->json(['message' => '新しく保存しました！', 'id' => $song->id]);
    }

    // 編集画面表示
    public function editor($id)
    {
        $song = Song::findOrFail($id); // IDからデータを取得
        return view('songs.editor', compact('song')); // ビューに $song を渡す
    }

    // 更新（上書き保存）
    public function update(Request $request, $id)
    {
        $song = Song::findOrFail($id);
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);
        $song->update($validated);
        return response()->json(['message' => '上書き保存しました！']);
    }

    // 削除機能
    public function destroy($id)
    {
        $song = Song::findOrFail($id);
        $song->delete();
        return redirect()->route('songs.index')->with('success', '曲を削除しました');
    }
}