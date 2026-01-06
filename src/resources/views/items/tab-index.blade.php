@extends('layouts/tab')

@section('title')
    <title>Tab譜</title>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/brothers-items.css') }}">
@endsection

@section('content')
    <div class="controls">
        BPM: <input type="number" id="bpm" value="100" style="width: 50px;">
        <button onclick="startAutoScroll()">▶ 自動スクロール開始</button>
        <button onclick="stopScroll()">■ 停止</button>
    </div>

    <div id="song-container"></div>

    <script>
        /**
         * コードライブラリ
         * barre: セーハするフレット番号 (nullなら無し)
         * strings: [1弦, 2弦, 3弦, 4弦, 5弦, 6弦] のフレット番号
         * 'x' はミュート、0 は開放弦
         */
        const chordLib = {
            "C": { barre: null, strings: [0, 1, 0, 2, 3, "x"] },
            "C7": { barre: null, strings: [0, 1, 3, 2, 3, "x"] },
            "C6": { barre: null, strings: [0, 1, 2, 2, 3, "x"] },
            "Caug": { barre: null, strings: ["x", 1, 1, 2, 3, "x"] },
            "Cm": { barre: 3, strings: [3, 4, 5, 5, 3, "x"] },
            "CmM7": { barre: 3, strings: [3, 4, 4, 5, 3, "x"] },
            "Cm7": { barre: null, strings: [0, 0, 0, 2, 3, "x"] },
            "Cm6": { barre: 3, strings: [3, 1, 2, 1, 3, "x"] },
            "Cm7b5": { barre: 3, strings: ["x", 4, 3, 4, 3, "x"] },
            "Cadd9": { barre: null, strings: [0, 3, 0, 2, 3, "x"] },
            "Csus4": { barre: null, strings: [1, 1, 0, 3, 3, "x"] },
            "C7sus4": { barre: 3, strings: [3, 6, 3, 5, 3, "x"] },
            "Cdim7": { barre: null, strings: ["x", 4, 2, 4, 3, "x"] },

            "C#": { barre: 4, strings: [4, 6, 6, 6, 4, "x"] },
            "C#m7": { barre: 4, strings: [4, 6, 5, 6, 4, "x"] },
            "C#7": { barre: 4, strings: [4, 6, 4, 6, 4, "x"] },
            "C#6": { barre: 4, strings: [6, 6, 6, 6, 4, "x"] },
            "C#aug": { barre: 4, strings: ["x", 2, 2, 3, 4, "x"] },
            "C#m": { barre: 4, strings: [4, 5, 6, 6, 4, "x"] },
            "C#mM7": { barre: 4, strings: [4, 5, 5, 6, 4, "x"] },
            "C#m7": { barre: 4, strings: [4, 5, 4, 6, 4, "x"] },
            "C#m6": { barre: 4, strings: [4, 2, 3, 2, 4, "x"] },
            "C#m7b5": { barre: 4, strings: ["x", 5, 4, 5, 4, "x"] },
            "C#add9": { barre: 4, strings: [4, 4, 6, 6, 4, "x"] },
            "C#sus4": { barre: 4, strings: [4, 7, 6, 6, 4, "x"] },
            "C#7sus4": { barre: 4, strings: [4, 7, 4, 6, 4, "x"] },
            "C#dim7": { barre: null, strings: ["x", 5, 3, 5, 4, "x"] },

            

            "F#": { barre: 2, strings: [2, 2, 3, 4, 4, 2] },
            "B": { barre: 2, strings: [2, 4, 4, 4, 2, "x"] },
            "F#m": { barre: 2, strings: [2, 2, 2, 4, 4, 2] },
            "E": { barre: null, strings: [0, 0, 1, 2, 2, 0] },
            "G#7": { barre: 4, strings: [4, 4, 5, 4, 6, 4] },
            "Am7": { barre: null, strings: [0, 1, 0, 2, 0, "x"] },
            "F": { barre: 1, strings: [1, 1, 2, 3, 3, 1] },
            "G": { barre: null, strings: [3, 0, 0, 0, 2, 3] },


        };

        const songData = [
            { chord: "C#m", lyrics: "Woo-woo-" }, { chord: "F#", lyrics: "" }, { chord: "B", lyrics: "" },
            { chord: "F#m", lyrics: "" }, { chord: "E", lyrics: "" }, { chord: "G#7", lyrics: "" },
            { chord: "C#m", lyrics: "Woo-woo-" }, { chord: "Cm7", lyrics: "" }
        ];

        function createChordSVG(chordName) {
            const data = chordLib[chordName];
            if (!data) return '';

            // 開始フレットの決定（1フレット以外から始まる場合のため）
            const startFret = data.barre ? data.barre : Math.min(...data.strings.filter(n => n > 0)) || 1;

            let elements = "";

            // 1. 弦（横線 6本：上が1弦、下が6弦）
            for (let i = 0; i < 6; i++) {
                const y = 20 + i * 10;
                elements += `<line x1="25" y1="${y}" x2="95" y2="${y}" stroke="#ccc" stroke-width="1"/>`;
            }

            // 2. フレット（縦線 5本）
            for (let i = 0; i < 5; i++) {
                const x = 25 + i * 17.5;
                elements += `<line x1="${x}" y1="20" x2="${x}" y2="70" stroke="#ccc" stroke-width="1"/>`;
            }

            // 3. セーハ（黒い太棒）
            if (data.barre) {
                // セーハは常に表示エリアの左端（最初のフレット隙間）に配置
                const bx = 25 + (17.5 / 2);
                elements += `<line x1="${bx}" y1="20" x2="${bx}" y2="70" stroke="black" stroke-width="8" stroke-linecap="round"/>`;
            }

            // 4. 指のポジション・開放弦・ミュートの描画
            data.strings.forEach((f, i) => {
                const y = 20 + i * 10; // 弦の高さ位置

                if (f === "x") {
                    // ×：ミュート（弦の左側に表示）
                    elements += `<text x="5" y="${y + 4}" font-size="12" font-family="Arial" font-weight="bold">×</text>`;
                } else if (f === 0) {
                    // ○：開放弦
                    elements += `<circle cx="12" cy="${y}" r="4" fill="none" stroke="black" stroke-width="1"/>`;
                } else {
                    // ●：押さえる場所
                    // 【修正ポイント】セーハ(data.barre)と同じフレットを押さえている場合は●を描画しない
                    if (f !== data.barre) {
                        // フレット内での相対位置を計算
                        const x = 25 + ((f - startFret + 1) * 17.5) - (17.5 / 2);
                        elements += `<circle cx="${x}" cy="${y}" r="4" fill="black"/>`;
                    }
                }
            });

            // 5. フレット番号の表示
            if (startFret > 0) {
                elements += `<text x="18" y="12" font-size="10" font-weight="bold">${startFret}</text>`;
            }

            return `<svg class="chord-svg" viewBox="0 0 100 85">${elements}</svg>`;
        }

        function init() {
            const container = document.getElementById('song-container');
            songData.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'chord-block';
                div.id = `block-${index}`;
                div.innerHTML = `
                                            <div class="chord-name">${item.chord}</div>
                                            ${createChordSVG(item.chord)}
                                            <div class="lyrics">${item.lyrics}</div>
                                        `;
                container.appendChild(div);
            });
        }

        let interval;
        function startAutoScroll() {
            let index = 0;
            const bpm = document.getElementById('bpm').value;
            const msPerBeat = (60 / bpm) * 1000;
            stopScroll();
            interval = setInterval(() => {
                document.querySelectorAll('.chord-block').forEach(el => el.classList.remove('active'));
                if (index >= songData.length) { stopScroll(); return; }
                const currentEl = document.getElementById(`block-${index}`);
                currentEl.classList.add('active');
                currentEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                index++;
            }, msPerBeat * 2);
        }

        function stopScroll() { clearInterval(interval); }
        window.onload = init;
    </script>
@endsection