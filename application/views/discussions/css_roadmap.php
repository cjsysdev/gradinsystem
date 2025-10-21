<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CSS Roadmap — Learn CSS</title>
    <style>
        :root {
            --bg: #0f1724;
            --card: #0b1220;
            --muted: #9aa4b2;
            --accent: #60a5fa;
            --glass: rgba(255, 255, 255, 0.04);
            --card-2: #071028;
        }

        * {
            box-sizing: border-box
        }

        html,
        body {
            height: 100%;
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            background: linear-gradient(180deg, #071028 0%, #071122 60%);
            color: #e6eef6
        }

        .wrap {
            max-width: 1200px;
            margin: 40px auto;
            padding: 24px
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px
        }

        h1 {
            font-size: 20px;
            margin: 0
        }

        p.lead {
            color: var(--muted);
            margin: 6px 0 0;
            font-size: 13px
        }

        .controls {
            display: flex;
            gap: 8px;
            align-items: center
        }

        .btn {
            background: var(--glass);
            border: 1px solid rgba(255, 255, 255, 0.04);
            padding: 8px 12px;
            border-radius: 10px;
            color: var(--muted);
            cursor: pointer;
            font-size: 13px
        }

        .btn:active {
            transform: translateY(1px)
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-top: 18px
        }

        .col {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), transparent);
            border-radius: 14px;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.03);
            min-height: 280px
        }

        .col h3 {
            margin: 0 0 10px;
            font-size: 14px
        }

        .node {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 10px;
            border: 1px solid rgba(255, 255, 255, 0.03);
            display: flex;
            flex-direction: column;
            gap: 6px
        }

        .node .title {
            font-weight: 600
        }

        .node .meta {
            font-size: 12px;
            color: var(--muted)
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.02);
            font-size: 12px;
            color: var(--muted);
            border: 1px solid rgba(255, 255, 255, 0.02)
        }

        /* connectors (visual only) */
        .col:before {
            content: "";
            position: absolute
        }

        .grid {
            position: relative
        }

        /* responsive */
        @media (max-width:980px) {
            .grid {
                grid-template-columns: repeat(2, 1fr)
            }
        }

        @media (max-width:560px) {
            .grid {
                grid-template-columns: 1fr
            }

            .wrap {
                padding: 12px;
                margin: 18px auto
            }
        }

        /* tooltip */
        .tooltip {
            position: relative
        }

        .tooltip:hover::after {
            content: attr(data-tip);
            position: absolute;
            left: 0;
            top: calc(100% + 10px);
            background: var(--card);
            padding: 8px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.03);
            white-space: nowrap;
            font-size: 12px;
            color: var(--muted)
        }

        /* simple legend */
        .legend {
            display: flex;
            gap: 8px;
            align-items: center
        }

        .legend .item {
            display: flex;
            gap: 8px;
            align-items: center;
            font-size: 13px;
            color: var(--muted)
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            background: var(--accent);
            box-shadow: 0 4px 12px rgba(96, 165, 250, 0.12)
        }

        /* dark mode switch visual */
        .dark-toggle {
            display: flex;
            align-items: center;
            gap: 8px
        }

        .collapse {
            font-size: 12px;
            color: var(--muted);
            cursor: pointer
        }

        /* small helpers */
        .small {
            font-size: 13px;
            color: var(--muted)
        }

        .pill {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.02);
            font-size: 12px
        }
    </style>
</head>

<body>
    <div class="wrap">
        <header>
            <div>
                <h1>CSS Roadmap</h1>
                <p class="lead">A lightweight single-file roadmap to learn CSS — beginner to advanced. Click nodes for resources or mark them done.</p>
            </div>
            <div class="controls">
                <div class="legend">
                    <div class="item"><span class="dot"></span><span class="small">Recommended path</span></div>
                </div>
                <button class="btn" id="toggleView">Toggle compact</button>
                <button class="btn" id="darkMode">Toggle dark</button>
            </div>
        </header>

        <main>
            <div class="grid" id="roadmap">
                <section class="col" data-level="beginner">
                    <h3>Beginner</h3>
                    <div class="node tooltip" data-tip="Basics you should know first" data-id="selectors">
                        <div class="title">Selectors & Specificity</div>
                        <div class="meta">Element, class, id, attribute selectors — + specificity rules</div>
                        <div class="small">Resources: MDN, simple exercises</div>
                    </div>

                    <div class="node tooltip" data-tip="Box model & layout foundation" data-id="boxmodel">
                        <div class="title">Box Model & Display</div>
                        <div class="meta">margin, border, padding, width, display: block/inline/inline-block</div>
                    </div>

                    <div class="node tooltip" data-tip="Positioning and floats" data-id="positioning">
                        <div class="title">Positioning & Flow</div>
                        <div class="meta">position, float, clear, z-index</div>
                    </div>

                </section>

                <section class="col" data-level="intermediate">
                    <h3>Intermediate</h3>
                    <div class="node tooltip" data-tip="Modern layout: flexbox" data-id="flex">
                        <div class="title">Flexbox</div>
                        <div class="meta">Align, justify, container & items, common patterns</div>
                    </div>

                    <div class="node tooltip" data-tip="Grid for 2D layouts" data-id="grid">
                        <div class="title">CSS Grid</div>
                        <div class="meta">Grid tracks, areas, placement, responsive grids</div>
                    </div>

                    <div class="node tooltip" data-tip="Design tokens & variables" data-id="vars">
                        <div class="title">Custom Properties</div>
                        <div class="meta">--variables, theming, runtime updates</div>
                    </div>

                </section>

                <section class="col" data-level="advanced">
                    <h3>Advanced</h3>
                    <div class="node tooltip" data-tip="Transitions & animations" data-id="anim">
                        <div class="title">Animations & Transitions</div>
                        <div class="meta">keyframes, transition shorthand, performance tips</div>
                    </div>

                    <div class="node tooltip" data-tip="Advanced selectors & functions" data-id="advanced-selectors">
                        <div class="title">Advanced Selectors & Functions</div>
                        <div class="meta">:nth-child, :has, calc(), clamp(), min(), max()</div>
                    </div>

                    <div class="node tooltip" data-tip="Architecture & scale" data-id="architecture">
                        <div class="title">Architecture (BEM / OOCSS)</div>
                        <div class="meta">Methodologies for large projects, componentization</div>
                    </div>

                </section>

                <section class="col" data-level="tools">
                    <h3>Tools & Ecosystem</h3>
                    <div class="node tooltip" data-tip="Preprocessors" data-id="sass">
                        <div class="title">Sass / Less</div>
                        <div class="meta">Variables, nesting, mixins — when to use</div>
                    </div>

                    <div class="node tooltip" data-tip="Utility-first frameworks" data-id="tailwind">
                        <div class="title">Tailwind / Utility CSS</div>
                        <div class="meta">Atomic classes, productivity trade-offs</div>
                    </div>

                    <div class="node tooltip" data-tip="Testing & performance" data-id="perf">
                        <div class="title">Performance & Debugging</div>
                        <div class="meta">Critical CSS, devtools, Lighthouse</div>
                    </div>

                </section>
            </div>

            <section style="margin-top:18px;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                <div class="pill">Click a node to open a curated resource</div>
                <div class="pill" id="completedCount">Completed: 0</div>
                <div class="small">Tip: press <strong>D</strong> to toggle compact view.</div>
            </section>
        </main>
    </div>

    <script>
        // Small interactive behaviors: mark done, open resource (placeholder), compact toggle, dark/light
        const roadmap = document.getElementById('roadmap');
        const nodes = roadmap.querySelectorAll('.node');
        const completedCount = document.getElementById('completedCount');
        let compact = false;

        nodes.forEach(node => {
            node.style.cursor = 'pointer';
            node.addEventListener('click', (e) => {
                // toggle done state
                node.classList.toggle('done');
                if (node.classList.contains('done')) {
                    node.style.opacity = '0.6';
                    node.style.textDecoration = 'line-through';
                } else {
                    node.style.opacity = '1';
                    node.style.textDecoration = 'none';
                }
                updateCount();
            });
            node.addEventListener('dblclick', (e) => {
                // open curated resource (placeholder) — could be replaced with real links
                const id = node.dataset.id || 'resource';
                const map = {
                    selectors: 'https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Selectors',
                    boxmodel: 'https://developer.mozilla.org/en-US/docs/Learn/CSS/Building_blocks/The_box_model',
                    positioning: 'https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Positioning',
                    flex: 'https://css-tricks.com/snippets/css/a-guide-to-flexbox/',
                    grid: 'https://css-tricks.com/snippets/css/complete-guide-grid/',
                    vars: 'https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties',
                    anim: 'https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Animations',
                    'advanced-selectors': 'https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Selectors',
                    architecture: 'https://en.wikipedia.org/wiki/CSS',
                    sass: 'https://sass-lang.com/',
                    tailwind: 'https://tailwindcss.com/',
                    perf: 'https://web.dev/learn/performance/'
                };
                const url = map[id] || 'https://developer.mozilla.org/en-US/docs/Web/CSS';
                window.open(url, '_blank');
            });
        });

        function updateCount() {
            const done = roadmap.querySelectorAll('.node.done').length;
            completedCount.textContent = `Completed: ${done}`;
        }

        document.getElementById('toggleView').addEventListener('click', () => {
            compact = !compact;
            document.querySelectorAll('.node').forEach(n => {
                if (compact) {
                    n.style.padding = '8px';
                    n.querySelector('.meta').style.display = 'none';
                } else {
                    n.style.padding = '10px';
                    n.querySelector('.meta').style.display = 'block';
                }
            });
        });

        document.getElementById('darkMode').addEventListener('click', () => {
            if (document.documentElement.style.getPropertyValue('--bg') === '') {
                // quick theme toggle (this file uses dark by default), but allow light
                document.documentElement.style.setProperty('--bg', '#f6f7fb');
                document.body.style.background = 'linear-gradient(180deg,#f6f7fb 0%, #eef2ff 60%)';
                document.body.style.color = '#0b1220';
                document.querySelectorAll('.btn').forEach(b => b.style.color = '#0b1220');
            } else {
                document.documentElement.style.removeProperty('--bg');
                document.body.style.background = 'linear-gradient(180deg,#071028 0%, #071122 60%)';
                document.body.style.color = '#e6eef6';
                document.querySelectorAll('.btn').forEach(b => b.style.color = 'var(--muted)');
            }
        });

        // keyboard shortcut D to toggle compact
        document.addEventListener('keydown', (e) => {
            if (e.key.toLowerCase() === 'd') {
                document.getElementById('toggleView').click();
            }
        });

        // small accessibility: focus on nodes via keyboard
        let nodeIndex = 0;
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown') {
                nodeIndex = Math.min(nodes.length - 1, nodeIndex + 1);
                nodes[nodeIndex].focus();
            }
            if (e.key === 'ArrowUp') {
                nodeIndex = Math.max(0, nodeIndex - 1);
                nodes[nodeIndex].focus();
            }
        });

        // make nodes focusable
        nodes.forEach(n => {
            n.setAttribute('tabindex', '0');
            n.style.outline = 'none';
            n.addEventListener('focus', () => {
                n.style.boxShadow = '0 6px 18px rgba(2,6,23,0.6)';
            });
            n.addEventListener('blur', () => {
                n.style.boxShadow = 'none';
            });
        });
    </script>
</body>

</html>