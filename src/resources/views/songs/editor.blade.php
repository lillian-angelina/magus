@extends('layouts/tab')

@section('title')
    <title>Tab譜作成 - {{ $song->title ?? '新規作成' }}</title>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/tab.css') }}">
@endsection

@section('content')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<div class="contents">
    <div class="controls-top">
        {{-- タイトルを既存データからセット --}}
        <input type="text" id="song-title" placeholder="曲のタイトルを入力" 
               value="{{ $song->title ?? '' }}" 
               style="padding: 10px; width: 300px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px;">
        
        <button onclick="saveToDB()" class="btn-save" 
                style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">
            {{ isset($song->id) ? '上書き保存' : '新規保存' }}
        </button>

        <button onclick="downloadPDF()" class="btn-pdf">
            PDFファイルをダウンロード
        </button>

        <div style="margin-left: 20px; display: flex; align-items: center; gap: 10px; background: #f0f0f0; padding: 5px 15px; border-radius: 20px;">
            <span style="font-size: 12px; font-weight: bold;">スクロール速度</span>
            <input type="range" id="scroll-speed" min="0.1" max="5.0" step="0.1" value="1.0" style="cursor: pointer;">
            <span id="speed-display" style="font-size: 12px; width: 30px;">x1.0</span>
        </div>
        <span style="font-size: 12px; color: #888;">※プレビューをクリックで開始/停止</span>

        <a href="{{ route('songs.index') }}" style="color: #666; text-decoration: none; margin-left: auto;">一覧に戻る</a>
    </div>

    <div class="editor-container">
        <div class="input-section">
            <h2>コード譜エディタ</h2>
            <p class="instruction" style="font-size: 0.9em; color: #666;">スペース区切りで入力。改行で次の段になります。</p>
            {{-- コンテンツを既存データからセット --}}
            <textarea id="chord-input" placeholder="C G Am F">{{ $song->content ?? '' }}</textarea>
        </div>

        <div class="preview-section-container">
            <h2>プレビュー</h2>
            <div id="preview-area"></div>
        </div>
    </div>
</div>

<script>
    // --- 重要なIDの受け渡し ---
    const songId = @json($song->id ?? null); // ここでPHPのIDをJSに渡す

    // --- コードライブラリ (省略せず保持してください) ---
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

    // --- SVG生成関数 ---
    function createChordSVG(chordName) {
        const data = chordLib[chordName];
        if (!data) return `<div style="font-size:10px; color:#ccc;">No Data</div>`;
        const startFret = data.barre ? data.barre : Math.min(...data.strings.filter(n => typeof n === 'number' && n > 0)) || 1;
        let elements = "";
        for (let i = 0; i < 6; i++) {
            const y = 20 + i * 10;
            elements += `<line x1="25" y1="${y}" x2="95" y2="${y}" stroke="#ddd" stroke-width="1"/>`;
        }
        for (let i = 0; i < 5; i++) {
            const x = 25 + i * 17.5;
            elements += `<line x1="${x}" y1="20" x2="${x}" y2="70" stroke="#ddd" stroke-width="1"/>`;
        }
        if (data.barre) {
            const bx = 25 + (17.5 / 2); 
            elements += `<line x1="${bx}" y1="20" x2="${bx}" y2="70" stroke="black" stroke-width="8" stroke-linecap="round"/>`;
        }
        data.strings.forEach((f, i) => {
            const y = 20 + i * 10;
            if (f === "x") { elements += `<text x="5" y="${y + 4}" font-size="12" font-weight="bold">×</text>`; }
            else if (f === 0) { elements += `<circle cx="12" cy="${y}" r="4" fill="none" stroke="black" stroke-width="1"/>`; }
            else if (f !== data.barre) {
                const x = 25 + ((f - startFret + 1) * 17.5) - (17.5 / 2);
                elements += `<circle cx="${x}" cy="${y}" r="4" fill="black"/>`;
            }
        });
        if (startFret > 0) { elements += `<text x="18" y="12" font-size="10" font-weight="bold">${startFret}</text>`; }
        return `<svg class="chord-svg" viewBox="0 0 100 85">${elements}</svg>`;
    }

    // --- プレビュー更新関数 ---
    function updatePreview() {
        const titleInput = document.getElementById('song-title').value;
        const chordInput = document.getElementById('chord-input').value;
        const previewArea = document.getElementById('preview-area');
        previewArea.innerHTML = '';
        const titleEl = document.createElement('h1');
        titleEl.style.textAlign = 'center';
        titleEl.style.marginBottom = '40px';
        titleEl.innerText = titleInput || 'Untitled';
        previewArea.appendChild(titleEl);

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
                itemDiv.innerHTML = `<div class="chord-name">${name}</div>${createChordSVG(name)}`;
                chordRow.appendChild(itemDiv);
            });
            lineDiv.appendChild(chordRow);
            previewArea.appendChild(lineDiv);
        });
    }

    // --- 保存ロジック ---
    async function saveToDB() {
        const title = document.getElementById('song-title').value;
        const content = document.getElementById('chord-input').value;
        if (!title) { alert("タイトルを入力してください"); return; }

        const isEdit = songId !== null;
        const url = isEdit ? `/songs/${songId}` : '/songs';
        const method = isEdit ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
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

    // --- PDFダウンロード ---
    async function downloadPDF() {
        const { jsPDF } = window.jspdf;
        const element = document.getElementById('preview-area');
        const title = document.getElementById('song-title').value || 'chord-sheet';
        const btn = document.querySelector('.btn-pdf');
        btn.innerText = "生成中...";
        btn.disabled = true;
        try {
            const canvas = await html2canvas(element, { scale: 2, backgroundColor: "#ffffff" });
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
            pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
            pdf.save(`${title}.pdf`);
        } catch (e) { alert("PDF作成エラー"); }
        btn.innerText = "PDFファイルをダウンロード";
        btn.disabled = false;
    }

// --- 自動スクロール機能（小数点対応版） ---
let isScrolling = false;
let animationFrameId = null;
let currentScrollY = 0; // 現在の内部的なスクロール位置（小数点保持用）

const previewArea = document.getElementById('preview-area');
const speedInput = document.getElementById('scroll-speed');
const speedDisplay = document.getElementById('speed-display');

speedInput.addEventListener('input', () => {
    speedDisplay.innerText = `x${parseFloat(speedInput.value).toFixed(1)}`;
});

function step() {
    if (!isScrolling) return;

    const speed = parseFloat(speedInput.value);
    
    // 1. 内部的な位置を更新（ここなら 0.1 単位でも蓄積される）
    currentScrollY += speed;

    // 2. 蓄積された値が 1ピクセルを超えたら、その分だけ実際にスクロールさせる
    if (currentScrollY >= 1) {
        const movePixels = Math.floor(currentScrollY); // 整数分を取得
        window.scrollBy(0, movePixels);
        currentScrollY -= movePixels; // スクロールした分を差し引く（端数は残す）
    }

    // ページの最下部に達したら停止
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
        stopAutoScroll();
        return;
    }

    animationFrameId = requestAnimationFrame(step);
}

function startAutoScroll() {
    isScrolling = true;
    // 開始時のスクロール位置の端数をリセット
    currentScrollY = 0; 
    previewArea.style.boxShadow = "0 0 15px rgba(230, 126, 34, 0.5)";
    animationFrameId = requestAnimationFrame(step);
}

function stopAutoScroll() {
    isScrolling = false;
    previewArea.style.boxShadow = "0 0 10px rgba(0,0,0,0.05)";
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
    }
}

// クリックイベントはそのまま
previewArea.addEventListener('click', (e) => {
    if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT') return;
    if (isScrolling) {
        stopAutoScroll();
    } else {
        startAutoScroll();
    }
});

    // --- イベントと初期実行 ---
    document.getElementById('chord-input').addEventListener('input', updatePreview);
    document.getElementById('song-title').addEventListener('input', updatePreview);

    // ページ読み込み完了時にプレビューを生成
    document.addEventListener('DOMContentLoaded', updatePreview);
</script>
@endsection