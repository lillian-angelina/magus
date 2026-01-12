@extends('layouts/tab')

{{-- タイトル設定： songモデルがあればタイトルを、なければ「新規作成」を表示 --}}
@section('title')
    <title>Tab譜作成 - {{ $song->title ?? '新規作成' }}</title>
@endsection

{{-- CSS読み込み： 公開ディレクトリの css/tab.css を適用 --}}
@section('css')
    <link rel="stylesheet" href="{{ asset('css/tab.css') }}">
@endsection

{{-- メインコンテンツ --}}
@section('content')
    {{-- html2canvas: プレビュー画面を画像に変換するためのライブラリ --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    {{-- jsPDF: 画像化したデータをPDFとして生成・保存するためのライブラリ --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <div class="contents">
        {{-- 上部操作バー： 保存、ダウンロード、スクロール設定など --}}
        <div class="controls-top">
            {{-- 曲名入力： 既存データがあれば value にセット --}}
            <input type="text" id="song-title" placeholder="曲のタイトルを入力" value="{{ $song->title ?? '' }}"
                style="padding: 10px; width: 300px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px;">

            {{-- DB保存ボタン： $song->id の有無で「上書き」か「新規」かを動的に表示 --}}
            <button onclick="saveToDB()" class="btn-save"
                style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                {{ isset($song->id) ? '上書き保存' : '新規保存' }}
            </button>

            {{-- PDF保存ボタン --}}
            <button onclick="downloadPDF()" class="btn-pdf">
                PDFファイルをダウンロード
            </button>

            {{-- スクロール速度コントローラー --}}
            <div
                style="margin-left: 20px; display: flex; align-items: center; gap: 10px; background: #f0f0f0; padding: 5px 15px; border-radius: 20px;">
                <span style="font-size: 12px; font-weight: bold;">スクロール速度</span>
                <input type="range" id="scroll-speed" min="0.1" max="5.0" step="0.1" value="1.0" style="cursor: pointer;">
                <span id="speed-display" style="font-size: 12px; width: 30px;">x1.0</span>
            </div>
            <span style="font-size: 12px; color: #888;">※プレビューをクリックで開始/停止</span>

            <a href="{{ route('songs.index') }}" style="color: #666; text-decoration: none; margin-left: auto;">一覧に戻る</a>
        </div>

        {{-- メインエディタエリア --}}
        <div class="editor-container">
            {{-- 入力セクション： テキストエリアにコードを打ち込む --}}
            <div class="input-section">
                <h2>コード譜エディタ</h2>
                <p class="instruction" style="font-size: 0.9em; color: #666;">スペース区切りで入力。改行で次の段になります。</p>
                <textarea id="chord-input" placeholder="C G Am F">{{ $song->content ?? '' }}</textarea>
            </div>

            {{-- プレビューセクション： JSによって生成されたSVG図形がここに描画される --}}
            <div class="preview-section-container">
                <h2>プレビュー</h2>
                <div id="preview-area"></div>
            </div>
        </div>
    </div>

    <script>
        // --- サーバーサイド(PHP)からJSへデータの引き継ぎ ---
        const songId = @json($song->id ?? null); // 既存データがあればIDを保持、なければnull

        // --- コード図の構成データ (指の位置を数値化) ---
        // strings: [1弦, 2弦, 3弦, 4弦, 5弦, 6弦] の順。0は開放、xはミュート。
        const chordLib = {

            "C": { barre: null, strings: [0, 1, 0, 2, 3, "x"] },

            "CM7": { barre: null, strings: [0, 0, 0, 2, 3, "x"] },

            "C7": { barre: null, strings: [0, 1, 3, 2, 3, "x"] },

            "C6": { barre: null, strings: [0, 1, 2, 2, 3, "x"] },

            "Caug": { barre: null, strings: ["x", 1, 1, 2, 3, "x"] },

            "Cm": { barre: 3, strings: [3, 4, 5, 5, 3, "x"] },

            "CmM7": { barre: 3, strings: [3, 4, 4, 5, 3, "x"] },

            "Cm7": { barre: 3, strings: [3, 4, 3, 5, 3, "x"] },

            "Cm6": { barre: null, strings: [3, 1, 2, 1, 3, "x"] },

            "Cm7b5": { barre: null, strings: ["x", 4, 3, 4, 3, "x"] },

            "Cadd9": { barre: null, strings: [0, 3, 0, 2, 3, "x"] },

            "Csus4": { barre: null, strings: [1, 1, 0, 3, 3, "x"] },

            "C7sus4": { barre: 3, strings: [3, 6, 3, 5, 3, "x"] },

            "Cdim7": { barre: null, strings: ["x", 4, 2, 4, 3, "x"] },

            "C#": { barre: 4, strings: [4, 6, 6, 6, 4, "x"] },

            "C#M7": { barre: 4, strings: [4, 6, 5, 6, 4, "x"] },

            "C#7": { barre: 4, strings: [4, 6, 4, 6, 4, "x"] },

            "C#6": { barre: 4, strings: [6, 6, 6, 6, 4, "x"] },

            "C#aug": { barre: null, strings: ["x", 2, 2, 3, 4, "x"] },

            "C#m": { barre: 4, strings: [4, 5, 6, 6, 4, "x"] },

            "C#mM7": { barre: 4, strings: [4, 5, 5, 6, 4, "x"] },

            "C#m7": { barre: 4, strings: [4, 5, 4, 6, 4, "x"] },

            "C#m6": { barre: null, strings: [4, 2, 3, 2, 4, "x"] },

            "C#m7b5": { barre: null, strings: ["x", 5, 4, 5, 4, "x"] },

            "C#add9": { barre: 4, strings: [4, 4, 6, 6, 4, "x"] },

            "C#sus4": { barre: 4, strings: [4, 7, 6, 6, 4, "x"] },

            "C#7sus4": { barre: 4, strings: [4, 7, 4, 6, 4, "x"] },

            "C#dim7": { barre: null, strings: ["x", 5, 3, 5, 4, "x"] },

            "D": { barre: null, strings: [2, 3, 2, 0, "x", "x"] },

            "DM7": { barre: null, strings: [2, 2, 2, 0, "x", "x"] },

            "D7": { barre: null, strings: [2, 1, 2, 0, "x", "x"] },

            "D6": { barre: null, strings: [2, 0, 2, 0, "x", "x"] },

            "Daug": { barre: null, strings: [2, 2, 3, 0, "x", "x"] },

            "Dm": { barre: null, strings: [1, 3, 2, 0, "x", "x"] },

            "DmM7": { barre: null, strings: [1, 2, 2, 0, "x", "x"] },

            "Dm7": { barre: null, strings: [1, 1, 2, 0, "x", "x"] },

            "Dm6": { barre: null, strings: [1, 0, 2, 0, "x", "x"] },

            "Dm7b5": { barre: null, strings: [1, 1, 1, 0, "x", "x"] },

            "Dadd9": { barre: null, strings: [0, 3, 2, 0, "x", "x"] },

            "Dsus4": { barre: null, strings: [3, 3, 2, 0, "x", "x"] },

            "D7sus4": { barre: null, strings: [3, 1, 2, 0, "x", "x"] },

            "Ddim7": { barre: null, strings: [1, 0, 1, 0, "x", "x"] },

            "D#": { barre: 6, strings: [6, 8, 8, 8, 6, "x"] },

            "D#M7": { barre: 6, strings: [6, 8, 7, 8, 6, "x"] },

            "D#7": { barre: 6, strings: [6, 8, 6, 8, 6, "x"] },

            "D#6": { barre: 6, strings: [8, 8, 8, 8, 6, "x"] },

            "D#aug": { barre: null, strings: ["x", 4, 4, 5, 6, "x"] },

            "D#m": { barre: 6, strings: [6, 7, 8, 8, 6, "x"] },

            "D#mM7": { barre: 6, strings: [6, 7, 7, 8, 6, "x"] },

            "D#m7": { barre: 6, strings: [6, 7, 6, 8, 6, "x"] },

            "D#m6": { barre: null, strings: [6, 4, 5, 4, 6, "x"] },

            "D#m7b5": { barre: null, strings: ["x", 7, 6, 7, 6, "x"] },

            "D#add9": { barre: 6, strings: [6, 6, 8, 8, 6, "x"] },

            "D#sus4": { barre: 6, strings: [6, 9, 8, 8, 6, "x"] },

            "D#7sus4": { barre: 6, strings: [6, 9, 6, 8, 6, "x"] },

            "D#dim7": { barre: null, strings: ["x", 7, 5, 7, 6, "x"] },

            "E": { barre: null, strings: [0, 0, 1, 2, 2, 0] },

            "EM7": { barre: null, strings: [0, 0, 1, 1, 2, 0] },

            "E7": { barre: null, strings: [0, 0, 1, 0, 2, 0] },

            "E6": { barre: null, strings: [0, 2, 1, 2, 2, 0] },

            "Eaug": { barre: null, strings: [0, 1, 1, 2, "x", "x"] },

            "Em": { barre: null, strings: [0, 0, 0, 2, 2, 0] },

            "EmM7": { barre: null, strings: ["x", 0, 0, 1, 2, 0] },

            "Em7": { barre: null, strings: [0, 0, 0, 0, 2, 0] },

            "Em6": { barre: null, strings: [0, 2, 0, 2, 2, 0] },

            "Em7b5": { barre: null, strings: [0, 3, 0, 2, 1, 0] },

            "Eadd9": { barre: null, strings: [0, 0, 1, 4, 2, 0] },

            "Esus4": { barre: null, strings: [0, 0, 2, 2, 2, 0] },

            "E7sus4": { barre: null, strings: [0, 0, 2, 0, 2, 0] },

            "Edim7": { barre: null, strings: [0, 2, 0, 2, 1, 0] },

            "F": { barre: 1, strings: [1, 1, 2, 3, 3, 1] },

            "FM7": { barre: null, strings: ["x", 1, 2, 2, "x", 1] },

            "F7": { barre: 1, strings: [1, 1, 2, 1, 3, 1] },

            "F6": { barre: 1, strings: [1, 3, 2, 3, 1, 1] },

            "Faug": { barre: null, strings: [1, 2, 2, 3, "x", "x"] },

            "Fm": { barre: 1, strings: [1, 1, 1, 3, 3, 1] },

            "FmM7": { barre: 1, strings: [1, 1, 1, 2, 3, 1] },

            "Fm7": { barre: 1, strings: [1, 1, 1, 1, 3, 1] },

            "Fm6": { barre: 1, strings: [1, 3, 1, 3, 3, 1] },

            "Fm7b5": { barre: null, strings: ["x", 0, 1, 1, "x", 1] },

            "Fadd9": { barre: null, strings: [3, 1, 2, 3, "x", "x"] },

            "Fsus4": { barre: 1, strings: [1, 1, 3, 3, 3, 1] },

            "F7sus4": { barre: 1, strings: [1, 1, 3, 1, 3, 1] },

            "Fdim7": { barre: null, strings: [1, 0, 1, 0, "x", 1] },

            "F#": { barre: 2, strings: [2, 2, 3, 4, 4, 2] },

            "F#M7": { barre: null, strings: ["x", 2, 3, 3, "x", 2] },

            "F#7": { barre: 2, strings: [2, 2, 3, 2, 4, 2] },

            "F#6": { barre: 2, strings: [2, 4, 3, 4, 2, 2] },

            "F#aug": { barre: null, strings: [2, 3, 3, 4, "x", "x"] },

            "F#m": { barre: 2, strings: [2, 2, 2, 4, 4, 2] },

            "F#mM7": { barre: 2, strings: [2, 2, 2, 3, 4, 2] },

            "F#m7": { barre: 2, strings: [2, 2, 2, 2, 4, 2] },

            "F#m6": { barre: 2, strings: [2, 4, 2, 4, 4, 2] },

            "F#m7b5": { barre: null, strings: [0, 1, 2, 2, "x", 2] },

            "F#add9": { barre: null, strings: [4, 2, 3, 4, "x", "x"] },

            "F#sus4": { barre: 2, strings: [2, 2, 4, 4, 4, 2] },

            "F#7sus4": { barre: 2, strings: [2, 2, 4, 2, 4, 2] },

            "F#dim7": { barre: null, strings: ["x", 1, 2, 1, "x", 2] },

            "G": { barre: null, strings: [3, 0, 0, 0, 2, 3] },

            "GM7": { barre: null, strings: [3, 0, 0, 0, 2, 3] },

            "G7": { barre: null, strings: [1, 0, 0, 0, 2, 3] },

            "G6": { barre: null, strings: [0, 0, 0, 0, 2, 3] },

            "Gaug": { barre: null, strings: [3, 4, 4, 5, "x", "x"] },

            "Gm": { barre: 3, strings: [3, 3, 3, 5, 5, 3] },

            "GmM7": { barre: 3, strings: [3, 3, 3, 4, 5, 3] },

            "Gm7": { barre: 3, strings: [3, 3, 3, 3, 5, 3] },

            "Gm6": { barre: 3, strings: [3, 5, 3, 5, 5, 3] },

            "Gm7b5": { barre: null, strings: ["x", 2, 3, 3, "x", 3] },

            "Gadd9": { barre: null, strings: [3, 0, 2, 0, 0, 3] },

            "Gsus4": { barre: null, strings: [3, 1, 0, 0, 3, 3] },

            "G7sus4": { barre: 3, strings: [3, 3, 5, 3, 5, 3] },

            "Gdim7": { barre: null, strings: ["x", 2, 3, 2, "x", 3] },

            "G#": { barre: 4, strings: [4, 4, 5, 6, 6, 4] },

            "G#M7": { barre: null, strings: ["x", 4, 5, 5, "x", 4] },

            "G#7": { barre: 4, strings: [4, 4, 5, 4, 6, 4] },

            "G#6": { barre: null, strings: ["x", 4, 5, 3, "x", 4] },

            "G#aug": { barre: null, strings: [4, 5, 5, 6, "x", "x"] },

            "G#m": { barre: 4, strings: [4, 4, 4, 6, 6, 4] },

            "G#mM7": { barre: 4, strings: [4, 4, 4, 5, 6, 4] },

            "G#m7": { barre: 4, strings: [4, 4, 4, 4, 6, 4] },

            "G#m6": { barre: 4, strings: [4, 6, 4, 6, 6, 4] },

            "G#m7b5": { barre: null, strings: [0, 3, 4, 4, "x", 4] },

            "G#add9": { barre: null, strings: [6, 4, 5, 6, "x", "x"] },

            "G#sus4": { barre: 4, strings: [4, 4, 6, 6, 6, 4] },

            "G#7sus4": { barre: 4, strings: [4, 4, 6, 4, 6, 4] },

            "G#dim7": { barre: null, strings: ["x", 3, 4, 3, "x", 4] },

            "A": { barre: null, strings: [0, 2, 2, 2, 0, "x"] },

            "AM7": { barre: null, strings: [0, 2, 1, 2, 0, "x"] },

            "A7": { barre: null, strings: [0, 2, 0, 2, 0, "x"] },

            "A6": { barre: null, strings: [2, 2, 2, 2, 0, "x"] },

            "Aaug": { barre: null, strings: [1, 2, 2, 3, 0, "x"] },

            "Am": { barre: null, strings: [0, 1, 2, 2, 0, "x"] },

            "AmM7": { barre: null, strings: [0, 1, 1, 2, 0, "x"] },

            "Am7": { barre: null, strings: [0, 1, 0, 2, 0, "x"] },

            "Am6": { barre: null, strings: [2, 1, 2, 2, 0, "x"] },

            "Am7b5": { barre: null, strings: ["x", 1, 0, 1, 0, "x"] },

            "Aadd9": { barre: null, strings: [0, 0, 2, 2, 0, "x"] },

            "Asus4": { barre: null, strings: [0, 3, 2, 2, 0, "x"] },

            "A7sus4": { barre: null, strings: [0, 3, 0, 2, 0, "x"] },

            "Adim7": { barre: null, strings: [2, 1, 2, 1, 0, "x"] },

            "A#": { barre: 1, strings: [1, 3, 3, 3, 1, "x"] },

            "A#M7": { barre: 1, strings: [1, 3, 2, 3, 1, "x"] },

            "A#7": { barre: 1, strings: [1, 3, 1, 3, 1, "x"] },

            "A#6": { barre: 1, strings: [3, 3, 3, 3, 1, "x"] },

            "A#aug": { barre: null, strings: [6, 7, 7, 8, "x", "x"] },

            "A#m": { barre: 1, strings: [1, 2, 3, 3, 1, "x"] },

            "A#mM7": { barre: 1, strings: [1, 2, 2, 3, 1, "x"] },

            "A#m7": { barre: 1, strings: [1, 2, 1, 3, 1, "x"] },

            "A#m6": { barre: 1, strings: [3, 2, 3, 1, 1, "x"] },

            "A#m7b5": { barre: null, strings: ["x", 2, 1, 2, 1, "x"] },

            "A#add9": { barre: 1, strings: [1, 1, 3, 3, 1, "x"] },

            "A#sus4": { barre: 1, strings: [1, 4, 3, 3, 1, "x"] },

            "A#7sus4": { barre: 1, strings: [1, 4, 1, 3, 1, "x"] },

            "A#dim7": { barre: null, strings: [0, 2, 0, 2, 1, "x"] },

            "B": { barre: 2, strings: [2, 4, 4, 4, 2, "x"] },

            "BM7": { barre: 2, strings: [2, 4, 3, 4, 2, "x"] },

            "B7": { barre: null, strings: [2, 0, 2, 1, 2, "x"] },

            "B6": { barre: 2, strings: [4, 4, 4, 4, 2, "x"] },

            "Baug": { barre: null, strings: ["x", 0, 0, 1, 2, "x"] },

            "Bm": { barre: 2, strings: [2, 3, 4, 4, 2, "x"] },

            "BmM7": { barre: 2, strings: [2, 3, 3, 4, 2, "x"] },

            "Bm7": { barre: 2, strings: [2, 3, 2, 4, 2, "x"] },

            "Bm6": { barre: null, strings: [3, 0, 2, 0, 3, "x"] },

            "Bm7b5": { barre: null, strings: ["x", 3, 2, 3, 2, "x"] },

            "Badd9": { barre: 1, strings: [1, 1, 3, 3, 1, "x"] },

            "Bsus4": { barre: 2, strings: [2, 5, 4, 4, 2, "x"] },

            "B7sus4": { barre: 2, strings: [2, 5, 2, 4, 2, "x"] },

            "Bdim7": { barre: null, strings: ["x", 3, 1, 3, 2, "x"] },

        };

        // --- ギターコードのSVG図形を生成するメイン関数 ---
        function createChordSVG(chordName) {
            const data = chordLib[chordName];
            if (!data) return `<div style="font-size:10px; color:#ccc;">No Data</div>`;

            // 開始フレットの判定（ハイコードなどの場合に数値を調整）
            const startFret = data.barre ? data.barre : Math.min(...data.strings.filter(n => typeof n === 'number' && n > 0)) || 1;
            let elements = "";

            // 6本の横線（弦）を描画
            for (let i = 0; i < 6; i++) {
                const y = 20 + i * 10;
                elements += `<line x1="25" y1="${y}" x2="95" y2="${y}" stroke="#ddd" stroke-width="1"/>`;
            }
            // 5本の縦線（フレット）を描画
            for (let i = 0; i < 5; i++) {
                const x = 25 + i * 17.5;
                elements += `<line x1="${x}" y1="20" x2="${x}" y2="70" stroke="#ddd" stroke-width="1"/>`;
            }
            // セーハ（人差し指でまとめて押さえる太線）がある場合の描画
            if (data.barre) {
                const bx = 25 + (17.5 / 2);
                elements += `<line x1="${bx}" y1="20" x2="${bx}" y2="70" stroke="black" stroke-width="8" stroke-linecap="round"/>`;
            }
            // 指の位置（黒丸）やミュート（×）などを描画
            data.strings.forEach((f, i) => {
                const y = 20 + i * 10;
                if (f === "x") { elements += `<text x="5" y="${y + 4}" font-size="12" font-weight="bold">×</text>`; }
                else if (f === 0) { elements += `<circle cx="12" cy="${y}" r="4" fill="none" stroke="black" stroke-width="1"/>`; }
                else if (f !== data.barre) {
                    const x = 25 + ((f - startFret + 1) * 17.5) - (17.5 / 2);
                    elements += `<circle cx="${x}" cy="${y}" r="4" fill="black"/>`;
                }
            });
            // 図の左側にフレット番号（3fなど）を表示
            if (startFret > 0) { elements += `<text x="18" y="12" font-size="10" font-weight="bold">${startFret}</text>`; }
            return `<svg class="chord-svg" viewBox="0 0 100 85">${elements}</svg>`;
        }

        // --- プレビュー表示の更新ロジック ---
        function updatePreview() {
            const titleInput = document.getElementById('song-title').value;
            const chordInput = document.getElementById('chord-input').value;
            const previewArea = document.getElementById('preview-area');
            previewArea.innerHTML = '';

            // 曲のタイトルを表示
            const titleEl = document.createElement('h1');
            titleEl.style.textAlign = 'center';
            titleEl.style.marginBottom = '40px';
            titleEl.innerText = titleInput || 'Untitled';
            previewArea.appendChild(titleEl);

            // テキストエリアの入力を1行ずつ、さらに1単語(コード)ずつ分割して解析
            const lines = chordInput.split('\n');
            lines.forEach(line => {
                if (!line.trim()) return;
                const lineDiv = document.createElement('div');
                lineDiv.className = 'line-container';
                const chordRow = document.createElement('div');
                chordRow.className = 'chord-row';

                line.trim().split(/\s+/).forEach(name => {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'chord-item';
                    // コードネームと生成したSVGをセット
                    itemDiv.innerHTML = `<div class="chord-name">${name}</div>${createChordSVG(name)}`;
                    chordRow.appendChild(itemDiv);
                });
                lineDiv.appendChild(chordRow);
                previewArea.appendChild(lineDiv);
            });
        }

        // --- データベース保存処理 (Laravelへの非同期リクエスト) ---
        async function saveToDB() {
            const title = document.getElementById('song-title').value;
            const content = document.getElementById('chord-input').value;
            if (!title) { alert("タイトルを入力してください"); return; }

            const isEdit = songId !== null;
            const url = isEdit ? `/songs/${songId}` : '/songs';
            const method = isEdit ? 'PUT' : 'POST'; // 既存なら更新、新規なら作成

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' // Laravelセキュリティ対策用
                    },
                    body: JSON.stringify({ title, content })
                });
                if (response.ok) {
                    alert("保存が完了しました");
                    if (!isEdit) window.location.href = '/songs';
                } else {
                    alert("保存に失敗しました");
                }
            } catch (error) { alert("通信エラーが発生しました"); }
        }

        // --- プレビュー内容をPDFとしてダウンロード ---
        async function downloadPDF() {
            const { jsPDF } = window.jspdf;
            const element = document.getElementById('preview-area');
            const title = document.getElementById('song-title').value || 'chord-sheet';
            const btn = document.querySelector('.btn-pdf');
            btn.innerText = "生成中...";
            btn.disabled = true;
            try {
                // HTML要素をキャンバスに描画
                const canvas = await html2canvas(element, { scale: 2, backgroundColor: "#ffffff" });
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
                // 画像をPDFに追加して保存
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                pdf.save(`${title}.pdf`);
            } catch (e) { alert("PDF作成エラー"); }
            btn.innerText = "PDFファイルをダウンロード";
            btn.disabled = false;
        }

        // --- 自動スクロール制御ロジック ---
        let isScrolling = false;
        let animationFrameId = null;
        let currentScrollY = 0; // 0.1単位の細かい位置を追跡

        const previewArea = document.getElementById('preview-area');
        const speedInput = document.getElementById('scroll-speed');
        const speedDisplay = document.getElementById('speed-display');

        // 速度スライダー変更時に表示テキストを更新 (x1.0など)
        speedInput.addEventListener('input', () => {
            speedDisplay.innerText = `x${parseFloat(speedInput.value).toFixed(1)}`;
        });

        // スクロールを1ステップ進める再帰関数
        function step() {
            if (!isScrolling) return;

            const speed = parseFloat(speedInput.value);

            // スピード(0.1〜5.0)を内部カウンタに加算
            currentScrollY += speed;

            // 1ピクセル分溜まったら実際に画面を動かす
            if (currentScrollY >= 1) {
                const movePixels = Math.floor(currentScrollY);
                window.scrollBy(0, movePixels);
                currentScrollY -= movePixels; // 動いた分を差し引く
            }

            // ページ下端に到達したら停止
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
                stopAutoScroll();
                return;
            }

            // 次のフレーム(通常1/60秒後)にまた実行
            animationFrameId = requestAnimationFrame(step);
        }

        // 開始：フラグを立ててアニメーションループを開始
        function startAutoScroll() {
            isScrolling = true;
            currentScrollY = 0;
            previewArea.style.boxShadow = "0 0 15px rgba(230, 126, 34, 0.5)"; // 動作中の光彩
            animationFrameId = requestAnimationFrame(step);
        }

        // 停止：フラグを下ろしてアニメーションをキャンセル
        function stopAutoScroll() {
            isScrolling = false;
            previewArea.style.boxShadow = "0 0 10px rgba(0,0,0,0.05)";
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
            }
        }

        // プレビューエリアをクリックした際の挙動
        previewArea.addEventListener('click', (e) => {
            // ボタンやスライダーのクリック時は反応させない
            if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT') return;
            if (isScrolling) {
                stopAutoScroll();
            } else {
                startAutoScroll();
            }
        });

        // --- イベントリスナーの設定 ---
        // タイトルやコード入力が変更されるたびに自動でプレビューを更新
        document.getElementById('chord-input').addEventListener('input', updatePreview);
        document.getElementById('song-title').addEventListener('input', updatePreview);

        // 画面読み込み完了時に最初のプレビューを実行
        document.addEventListener('DOMContentLoaded', updatePreview);
    </script>
@endsection