<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CSS display property — Interactive tutorial</title>
    <style>
        :root {
            --bg: #0f1724;
            --card: #0b1220;
            --muted: #9aa4b2;
            --accent: #60a5fa;
            --glass: rgba(255, 255, 255, 0.03);
        }

        html,
        body {
            height: 100%;
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            background: linear-gradient(180deg, #081129 0%, #071427 100%);
            color: #e6eef6
        }

        .wrap {
            max-width: 1100px;
            margin: 28px auto;
            padding: 22px
        }

        header {
            display: flex;
            gap: 18px;
            align-items: center
        }

        header h1 {
            font-size: 20px;
            margin: 0
        }

        .lead {
            color: var(--muted);
            margin: 6px 0 18px
        }

        .grid {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 18px
        }

        .card {
            background: var(--card);
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(2, 6, 23, 0.6)
        }

        /* left column list */
        .types {
            display: flex;
            flex-direction: column;
            gap: 10px
        }

        .types button {
            background: var(--glass);
            border: 1px solid rgba(255, 255, 255, 0.03);
            padding: 10px;
            border-radius: 8px;
            color: #dbe9ff;
            text-align: left;
            cursor: pointer
        }

        .types button.active {
            outline: 2px solid rgba(96, 165, 250, 0.18);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), transparent)
        }

        .types .title {
            font-weight: 600
        }

        .explain {
            color: var(--muted);
            font-size: 14px;
            margin-top: 8px
        }

        /* playground */
        .playground {
            display: flex;
            flex-direction: column;
            gap: 12px
        }

        .controls {
            display: flex;
            gap: 8px;
            flex-wrap: wrap
        }

        .controls select,
        .controls button {
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            background: #061223;
            color: #dfeffb
        }

        .stage {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), transparent);
            padding: 18px;
            border-radius: 10px;
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .sample-container {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center
        }

        .sample {
            padding: 10px;
            border-radius: 6px;
            background: #0b2338;
            border: 1px solid rgba(255, 255, 255, 0.03);
            min-width: 36px;
            min-height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600
        }

        .sample.small {
            width: 60px
        }

        .codebox {
            background: #071827;
            padding: 12px;
            border-radius: 8px;
            overflow: auto;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, "Roboto Mono", monospace;
            font-size: 13px
        }

        pre {
            margin: 0;
            color: #cfeaff
        }

        .row {
            display: flex;
            gap: 12px
        }

        .hint {
            color: var(--muted);
            font-size: 13px
        }

        .copy {
            margin-left: auto
        }

        footer {
            margin-top: 14px;
            color: var(--muted);
            font-size: 13px
        }

        /* small responsive */
        @media (max-width:900px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <header>
            <div>
                <h1>CSS <code>display</code> property — Interactive tutorial</h1>
                <div class="lead">Explore common display values with live examples and a small playground. Click any value to see what it does.</div>
            </div>
        </header>

        <div class="grid">
            <div class="card">
                <h3 style="margin-top:0">Display types</h3>
                <div class="types" id="typesList">
                    <!-- buttons injected by JS -->
                </div>
                <div class="explain" id="typeExplain">Select a display value to see details.</div>
            </div>

            <div class="card">
                <div class="playground">
                    <div class="row">
                        <div class="controls">
                            <label for="displaySelect" class="hint">Choose display:</label>
                            <select id="displaySelect" aria-label="Choose display">
                                <option value="block">block</option>
                                <option value="inline">inline</option>
                                <option value="inline-block">inline-block</option>
                                <option value="flex">flex</option>
                                <option value="grid">grid</option>
                                <option value="table">table</option>
                                <option value="none">none</option>
                            </select>
                            <button id="applyBtn">Apply</button>
                            <button id="resetBtn">Reset</button>
                        </div>
                        <div class="hint" style="margin-left:auto">Try changing the display and observe how boxes rearrange.</div>
                    </div>

                    <div class="stage" id="stage">
                        <div class="sample-container" id="sampleContainer" role="list">
                            <div class="sample" role="listitem">A</div>
                            <div class="sample" role="listitem">B</div>
                            <div class="sample" role="listitem">C</div>
                            <div class="sample" role="listitem">D</div>
                        </div>
                    </div>

                    <div>
                        <div class="row" style="align-items:flex-start">
                            <div style="flex:1">
                                <h4 style="margin:6px 0">Live CSS</h4>
                                <div class="codebox">
                                    <pre id="liveCode">.sample-container { display: block; }
.sample { margin: 6px; }</pre>
                                </div>
                            </div>
                            <div style="width:220px">
                                <h4 style="margin:6px 0">Quick notes</h4>
                                <div class="hint">You can also select <strong>display:none</strong> to hide the whole container. Use <strong>flex</strong> and <strong>grid</strong> to build layouts.</div>
                                <div style="display:flex;margin-top:8px">
                                    <button id="copyBtn" class="copy">Copy CSS</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <footer>Built for teaching — shows behaviour differences between <code>block</code>, <code>inline</code>, <code>inline-block</code>, <code>flex</code>, <code>grid</code>, <code>table</code>, and <code>none</code>.</footer>
            </div>
        </div>
    </div>

    <script>
        // Data model for display types
        const types = [{
                key: 'block',
                title: 'block',
                short: 'Takes full width; starts on a new line.',
                example: 'A block-level element fills horizontal space and forces a line break.'
            },
            {
                key: 'inline',
                title: 'inline',
                short: 'Takes only necessary width; flows with text.',
                example: 'Inline elements do not start on a new line and width/height mostly ignored.'
            },
            {
                key: 'inline-block',
                title: 'inline-block',
                short: 'Inline flow but accepts width/height.',
                example: 'Combines inline flow with block box sizing.'
            },
            {
                key: 'flex',
                title: 'flex',
                short: 'Creates a flexible container for layout.',
                example: 'Children become flex items, enabling alignment, wrapping, and distribution.'
            },
            {
                key: 'grid',
                title: 'grid',
                short: 'Two-dimensional layout system.',
                example: 'Place children into rows and columns.'
            },
            {
                key: 'table',
                title: 'table',
                short: 'Behaves like table display.',
                example: 'Useful for emulating table layout without actual <table>.'
            },
            {
                key: 'none',
                title: 'none',
                short: 'Removes element from layout (no rendering).',
                example: 'The element and its children are not displayed and do not take space.'
            }
        ];

        const typesList = document.getElementById('typesList');
        const explain = document.getElementById('typeExplain');
        const displaySelect = document.getElementById('displaySelect');
        const applyBtn = document.getElementById('applyBtn');
        const resetBtn = document.getElementById('resetBtn');
        const sampleContainer = document.getElementById('sampleContainer');
        const liveCode = document.getElementById('liveCode');
        const copyBtn = document.getElementById('copyBtn');

        // build buttons list
        types.forEach(t => {
            const btn = document.createElement('button');
            btn.innerHTML = `<div class="title">${t.title}</div><div style=\"font-size:13px;color:var(--muted)\">${t.short}</div>`;
            btn.dataset.key = t.key;
            btn.addEventListener('click', () => {
                selectType(t.key)
            });
            typesList.appendChild(btn);
        });

        function selectType(key) {
            // mark active
            Array.from(typesList.children).forEach(el => el.classList.toggle('active', el.dataset.key === key));
            const t = types.find(x => x.key === key);
            explain.innerHTML = `<strong>${t.title}</strong> — ${t.short}<div style=\"margin-top:8px;color:var(--muted)\">${t.example}</div>`;
            displaySelect.value = key;
            applyDisplay(key);
        }

        function applyDisplay(key) {
            // clear inline styles from children
            sampleContainer.style.display = '';
            // For grid and flex we add some extra demo rules
            switch (key) {
                case 'flex':
                    sampleContainer.style.display = 'flex';
                    sampleContainer.style.flexWrap = 'wrap';
                    sampleContainer.style.justifyContent = 'center';
                    liveCode.textContent = `.sample-container { display: flex; flex-wrap: wrap; justify-content: center; }
.sample { margin: 6px; }`;
                    break;
                case 'grid':
                    sampleContainer.style.display = 'grid';
                    sampleContainer.style.gridTemplateColumns = 'repeat(auto-fit, minmax(80px, 1fr))';
                    sampleContainer.style.gap = '12px';
                    liveCode.textContent = `.sample-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); gap: 12px; }
.sample { margin: 0; }`;
                    break;
                case 'inline':
                    sampleContainer.style.display = 'inline';
                    sampleContainer.style.whiteSpace = 'normal';
                    liveCode.textContent = `.sample-container { display: inline; }
.sample { margin: 6px; }`;
                    break;
                case 'inline-block':
                    sampleContainer.style.display = 'inline-block';
                    sampleContainer.style.padding = '6px';
                    liveCode.textContent = `.sample-container { display: inline-block; }
.sample { display: inline-block; margin: 6px; }`;
                    break;
                case 'block':
                    sampleContainer.style.display = 'block';
                    sampleContainer.style.width = '100%';
                    liveCode.textContent = `.sample-container { display: block; }
.sample { margin: 6px; }`;
                    break;
                case 'table':
                    sampleContainer.style.display = 'table';
                    sampleContainer.style.width = 'auto';
                    // make children act like table-cell
                    Array.from(sampleContainer.children).forEach(ch => ch.style.display = 'table-cell');
                    liveCode.textContent = `.sample-container { display: table; }
.sample { display: table-cell; padding: 8px; }`;
                    break;
                case 'none':
                    sampleContainer.style.display = 'none';
                    liveCode.textContent = `.sample-container { display: none; }`;
                    break;
                default:
                    sampleContainer.style.display = '';
                    liveCode.textContent = `.sample-container { /* default */ }
.sample { margin: 6px; }`;
            }

            // cleanup: if switching away from table, reset children display
            if (key !== 'table') {
                Array.from(sampleContainer.children).forEach(ch => ch.style.display = 'flex');
            }

        }

        applyBtn.addEventListener('click', () => {
            applyDisplay(displaySelect.value);
            // mark matching button active
            Array.from(typesList.children).forEach(el => el.classList.toggle('active', el.dataset.key === displaySelect.value));
        });

        resetBtn.addEventListener('click', () => {
            // reset to default layout
            Array.from(typesList.children).forEach(el => el.classList.remove('active'));
            sampleContainer.style = '';
            Array.from(sampleContainer.children).forEach(ch => {
                ch.style.display = 'flex';
                ch.style.margin = '';
            });
            displaySelect.value = 'block';
            liveCode.textContent = `.sample-container { display: block; }
.sample { margin: 6px; }`;
            explain.textContent = 'Select a display value to see details.';
        });

        // clicking a value in left list also applies the display
        // initial state
        selectType('block');

        // copy CSS
        copyBtn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(liveCode.textContent);
                copyBtn.textContent = 'Copied!';
                setTimeout(() => copyBtn.textContent = 'Copy CSS', 1200);
            } catch (e) {
                copyBtn.textContent = 'Unavailable';
                setTimeout(() => copyBtn.textContent = 'Copy CSS', 1200);
            }
        });

        // keyboard accessibility: allow Enter on type buttons
        typesList.addEventListener('keydown', (ev) => {
            if (ev.key === 'Enter' && ev.target && ev.target.tagName === 'BUTTON') ev.target.click();
        });
    </script>
</body>

</html>