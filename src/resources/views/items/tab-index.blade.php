@extends('layouts/tab')

@section('title')
    <title>Tab譜</title>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/tab.css') }}">
@endsection

@section('content')

    <div class="editor-container">
        <div class="input-section">
            <h2>コード譜エディタ</h2>
            <div class="preview-section" id="preview-area">
            </div>
            <p class="instruction">コード名をスペース区切りで入力してください。改行すると次の行になります。</p>
            <textarea id="chord-input" placeholder="C#m F# B&#13;&#10;E G#7 Am7"></textarea>
        </div>
    </div>

    <script>
        // ユーザー提供のコードライブラリ
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

        // --- 描画ロジック ---
        function createChordSVG(chordName) {
            const data = chordLib[chordName];
            if (!data) return `<div style="height:75px; color:#ccc; font-size:10px;">No Data</div>`;

            const startFret = data.barre ? data.barre : Math.min(...data.strings.filter(n => typeof n === 'number' && n > 0)) || 1;
            let elements = "";

            // 弦（横線）
            for (let i = 0; i < 6; i++) {
                const y = 15 + i * 10;
                elements += `<line x1="25" y1="${y}" x2="95" y2="${y}" stroke="#ddd" stroke-width="1"/>`;
            }
            // フレット（縦線）
            for (let i = 0; i < 5; i++) {
                const x = 25 + i * 17.5;
                elements += `<line x1="${x}" y1="15" x2="${x}" y2="65" stroke="#ddd" stroke-width="1"/>`;
            }

            // セーハ
            if (data.barre) {
                const bx = 25 + (17.5 / 2);
                elements += `<line x1="${bx}" y1="15" x2="${bx}" y2="65" stroke="black" stroke-width="8" stroke-linecap="round"/>`;
            }

            data.strings.forEach((f, i) => {
                const y = 15 + i * 10;
                if (f === "x") {
                    elements += `<text x="5" y="${y + 4}" font-size="12" font-weight="bold">×</text>`;
                } else if (f === 0) {
                    elements += `<circle cx="12" cy="${y}" r="4" fill="none" stroke="black" stroke-width="1"/>`;
                } else if (f !== data.barre) {
                    const x = 25 + ((f - startFret + 1) * 17.5) - (17.5 / 2);
                    elements += `<circle cx="${x}" cy="${y}" r="4" fill="black"/>`;
                }
            });

            if (startFret > 0) {
                elements += `<text x="18" y="10" font-size="10" font-weight="bold">${startFret}</text>`;
            }

            return `<svg class="chord-svg" viewBox="0 0 100 80">${elements}</svg>`;
        }

        // --- エディタ更新ロジック ---
        function updatePreview() {
            const input = document.getElementById('chord-input').value;
            const previewArea = document.getElementById('preview-area');
            previewArea.innerHTML = '';

            // 行ごとに分割
            const lines = input.split('\n');

            lines.forEach(line => {
                const lineDiv = document.createElement('div');
                lineDiv.className = 'line-container';

                const chordRow = document.createElement('div');
                chordRow.className = 'chord-row';

                // スペース区切りでコードを取得
                const chords = line.trim().split(/\s+/);

                chords.forEach(name => {
                    if (!name) return;
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'chord-item';
                    itemDiv.innerHTML = `
                            <div class="chord-name">${name}</div>
                            ${createChordSVG(name)}
                        `;
                    chordRow.appendChild(itemDiv);
                });

                lineDiv.appendChild(chordRow);
                previewArea.appendChild(lineDiv);
            });
        }

        // Bladeの変数からIDを取得（新規ならnull）
        const songId = @json($song->id ?? null);

        async function saveToDB() {
            const title = document.getElementById('song-title').value;
            const content = document.getElementById('chord-input').value;

            // IDがあればPUT（更新）、なければPOST（新規）
            const url = songId ? `/songs/${songId}` : '/songs';
            const method = songId ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ title, content })
            });

            if (response.ok) {
                alert("保存しました！");
                if (!songId) window.location.href = '/songs'; // 新規なら一覧へ
            }
        }

        // イベント登録
        document.getElementById('chord-input').addEventListener('input', updatePreview);

        // 初期表示
        window.onload = updatePreview;
    </script>

@endsection