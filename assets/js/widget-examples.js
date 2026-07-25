// Example config JSON per widget_key — shown as the textarea's placeholder
// so the admin doesn't have to go dig through root/docs/paperless-midterm-plan.md
// every time. Keep in sync with each widgets/*.php view's expected $config shape.
// Shared by manage_assessments.php and class_assessments.php (the Add/Edit
// assessment modal) — a single source of truth for the widget config examples.
const widgetExamples = {
    worksheet: {
        hint: 'Table-style form. "min_rows" pre-fills that many blank rows; "allow_add_rows" lets the student add more.',
        example: {
            columns: ['Technology', 'Problem Solved', 'Why It Succeeded'],
            min_rows: 3,
            allow_add_rows: true
        }
    },
    quiz: {
        hint: 'Auto-graded. Empty/omitted "choices" = free-text question (case-insensitive match).',
        example: {
            questions: [
                { question: '2 + 2 = ?', choices: ['3', '4', '5'], answer: '4' },
                { question: 'Capital of France?', choices: [], answer: 'Paris' }
            ]
        }
    },
    secure_quiz: {
        hint: 'Same question format as Multiple Choice Quiz, but students take it in a dedicated fullscreen/timed page instead of an inline form (timer auto-submits, tab-switching shows a warning, one attempt). Auto-graded.',
        example: {
            questions: [
                { question: '2 + 2 = ?', choices: ['3', '4', '5'], answer: '4' },
                { question: 'Capital of France?', choices: [], answer: 'Paris' }
            ]
        }
    },
    card_sort: {
        hint: '"require_justification" adds a text box per placed item.',
        example: {
            bins: ['Incremental', 'Disruptive'],
            items: ['Android OS', 'Netflix', 'ChatGPT', 'LED Bulbs'],
            require_justification: true
        }
    },
    diagram: {
        hint: 'Fixed sequence of labeled boxes — student fills in the text inside each one.',
        example: {
            nodes: ['Sense', 'Transmit', 'Store', 'Act']
        }
    },
    decision_matrix: {
        hint: 'Fixed rows; each column is typed ("text" or "select" with "options").',
        example: {
            rows: ['Smart irrigation', 'Fish tank monitor', 'Offshore fishing boat'],
            columns: [
                { name: 'Cost', type: 'text' },
                { name: 'Best Fit', type: 'select', options: ['WiFi', 'Bluetooth', 'LoRa', 'Cellular', 'Satellite'] }
            ]
        }
    },
    calculator: {
        hint: '"formula" can use +, -, *, /, parentheses and each input\'s "key" as a variable name.',
        example: {
            inputs: [
                { label: 'Equipment Cost (₱)', key: 'cost' },
                { label: 'Monthly Savings (₱)', key: 'savings' }
            ],
            formula: 'cost / savings',
            result_label: 'Months to Break Even'
        }
    },
    brainstorm: {
        hint: 'Shared class-wide board, not per-student — "max_votes_per_student" limits dot-voting.',
        example: {
            prompt: 'How could IS help Maria the farmer?',
            max_votes_per_student: 3
        }
    },
    iq_discussion: {
        hint: 'Pick the topic from the dropdown below — not per-student either, students are redirected to the topic.',
        example: null
    },
    lab_worksheet: {
        hint: 'Fixed sequence of experiments (instructions + Predict/Observe/Explain prompts). Not auto-graded — score it manually like Worksheet Form.',
        example: {
            intro: '<p>Optional objectives/timeline HTML shown above the experiments.</p>',
            experiments: [
                {
                    title: 'Experiment 1.1 — Declare an array and print the first element',
                    instructions: '<p>Inside <code>main()</code>, type:</p><pre><code>int scores[5] = {85, 90, 78, 92, 88};\n\nprintf("%d\\n", scores[0]);</code></pre>',
                    warning: false,
                    prompts: [
                        { tag: 'predict', label: 'PREDICT', text: 'What number will print?' },
                        { tag: 'observe', label: 'OBSERVE', text: 'Compile. Run. What actually printed?' },
                        { tag: 'explain', label: 'EXPLAIN', text: 'Why does scores[0] give the first value?' }
                    ]
                },
                {
                    title: 'Experiment 1.2 — Go out of bounds',
                    instructions: '<p>Change the printf line to print <code>scores[5]</code>.</p>',
                    warning: true,
                    prompts: [
                        { tag: 'predict', label: 'PREDICT', text: 'Error, crash, or a number?' },
                        { tag: 'observe', label: 'OBSERVE', text: 'What actually happened?' },
                        { tag: 'explain', label: 'EXPLAIN', text: 'Why did C let you ask for a index that does not exist?' }
                    ],
                    note: 'Fix it back: change scores[5] back to scores[4] before continuing.'
                }
            ],
            exit_question: 'In one or two sentences: what surprised you the most today, and why?'
        }
    },
    case_study: {
        hint: 'Narrative story panel + fixed sections of heterogeneous questions (text/list/choice-with-rationale/toggle-grid) for case-study activities. Not auto-graded. This example is the full "Meet Maria" Session 1.2 worksheet — ready to use as-is, or adapt the story/sections for a different case study.',
        example: {
            story: {
                eyebrow: 'Session 1.2 · Field Notebook',
                title: "Innovation in Bohol: Maria's Calamansi Farm",
                intro: '<p>Maria grows calamansi on a small farm just outside Tagbilaran. Three things are working against her every season:</p>',
                stats: [
                    { label: 'NO FERTILIZER CREDIT', text: 'She has to pay full price upfront for fertilizer — or skip it and get a smaller harvest.' },
                    { label: "CAN'T PREDICT YIELD", text: 'No data on rainfall, pests, or demand — she plants the same amount every year and hopes.' },
                    { label: 'MIDDLEMEN TAKE ~70%', text: 'She sells at the farm gate to a consolidator, who resells in Tagbilaran and Cebu markets for far more.' }
                ]
            },
            sections: [
                {
                    label: 'Meet Maria',
                    timing: '3–15 min · Problem Intro',
                    questions: [
                        { type: 'text', badge: 'core', prompt: "In ONE sentence, state Maria's core problem — not a solution yet, just the problem.", rows: 2, placeholder: "Maria's problem is..." },
                        { type: 'list', badge: 'core', prompt: "Maria's situation is actually 3 separate problems bundled together. Name each one in a few words.", lines: 3, placeholders: ['1. ...', '2. ...', '3. ...'] },
                        {
                            type: 'choice', badge: 'core', prompt: 'Which of the 3 is hardest to solve with technology ALONE (no policy or lending changes)?',
                            options: [
                                { text: 'Fertilizer credit', note: "Credit is often a policy/finance problem before it's a tech problem — an app can't fix a bank's risk appetite." },
                                { text: 'Yield prediction', note: 'Actually the most solvable by tech alone — sensors, weather data, and simple forecasting apps directly attack this.' },
                                { text: 'Middlemen / market access', note: 'Partly tech (an app connecting farmers to buyers), but mostly about trust, logistics, and getting enough farmers to switch at once.' }
                            ]
                        }
                    ]
                },
                {
                    label: 'Innovation Ideation Mural',
                    timing: '15–40 min · Hands-On',
                    questions: [
                        { type: 'list', badge: 'core', prompt: 'Brainstorm (10 min). Write down every idea that could help Maria — quantity over judgment, no discussing yet.', lines: 6, placeholders: ['Idea 1', 'Idea 2', 'Idea 3', 'Idea 4', 'Idea 5', 'Idea 6'] },
                        { type: 'text', badge: 'core', prompt: 'Cluster (5 min). Sort your ideas into 2–3 categories — e.g. "Data/Prediction tools," "Financing tools," "Market access tools."', rows: 3, placeholder: 'Category A: ...\nCategory B: ...\nCategory C: ...' },
                        { type: 'list', badge: 'core', prompt: 'Vote (5 min). As a group, agree on your TOP 3 ideas, ranked.', lines: 3, placeholders: ['#1 (top pick)', '#2', '#3'] }
                    ]
                },
                {
                    label: 'Gallery Walk & Discussion',
                    timing: '40–55 min · Peer Feedback',
                    questions: [
                        { type: 'text', badge: 'core', prompt: 'For your #1 idea: roughly, what would it cost to build and run?', rows: 2, placeholder: 'Cost estimate + what drives that cost...' },
                        { type: 'text', badge: 'core', prompt: 'Who actually uses it day-to-day? Maria herself? Her buyer? A co-op officer?', rows: 2, placeholder: 'Name the specific person/role...' },
                        {
                            type: 'choice', badge: 'core', prompt: "For your top idea — what's the harder part?",
                            options: [
                                { text: 'Building the tech', note: "Fair — some of these ideas need sensors, connectivity, or apps that don't exist cheaply yet in rural Bohol." },
                                { text: 'Getting people to adopt it', note: 'This is the most common answer in real Philippine agri-tech cases — the tech usually exists, but getting farmers, buyers, and middlemen to actually change behavior is the hard part.' },
                                { text: 'Both, equally', note: "Also valid — and it's exactly what the Innovation Triangle (Week 2) is built to explain: tech, people, and business model all have to work together." }
                            ]
                        }
                    ]
                },
                {
                    label: 'Stress-Test Your Idea',
                    timing: 'Optional · Go Deeper',
                    questions: [
                        {
                            type: 'toggle_grid', badge: 'bonus', prompt: 'Tap each side of the triangle below to mark it a STRENGTH for your top idea. Leave the weak ones untapped.',
                            items: [
                                { title: 'TECH', text: 'Does the technology actually exist and work reliably?' },
                                { title: 'PEOPLE', text: 'Will Maria, the buyer, and the co-op actually use it?' },
                                { title: 'BUSINESS', text: 'Is there a way to pay for it that makes sense?' }
                            ]
                        },
                        { type: 'text', badge: 'bonus', prompt: 'MASIFAGCA is a real calamansi farmer group (Nueva Ecija) that faced almost this exact middleman problem. If you can look them up — what did they actually do?', rows: 2, placeholder: 'What you found...' },
                        { type: 'text', badge: 'bonus', prompt: 'Name one other Bohol industry (fishing, tourism, transport, weaving) with a similar "farmer\'s dilemma" — no data, no credit, middlemen. What would need to change?', rows: 2, placeholder: 'Industry + what would change...' }
                    ]
                },
                {
                    label: 'Reflection',
                    timing: '55–60 min · Wrap-Up',
                    questions: [
                        { type: 'text', badge: 'core', prompt: 'In one sentence — what makes something an innovation, not just an invention? Bring this answer to Session 2.1.', rows: 2, placeholder: 'Your answer here...' }
                    ]
                }
            ]
        }
    },
    case_dossier: {
        hint: 'Hook question + read-only framework explainer + multiple parallel case dossiers (each rated 1-5 per factor with a cited-evidence text field) + reflection questions. Not auto-graded. This example is the full Session 2.1 "Innovation Triangle" worksheet (GCash/Kodak/Friendster) — ready to use as-is.',
        example: {
            meta: {
                eyebrow: 'Session 2.1 · Field Notebook',
                title: 'Why Inventions Fail: The Innovation Triangle',
                sub: 'IS Innovations & New Technologies · Carmen Municipal College · Week 2'
            },
            hook: {
                label: 'The Best Widget Nobody Bought',
                timing: '0–5 min · Hook Question',
                intro: "<p>Imagine you invent the best widget ever. It's better than anything on the market. But almost nobody buys it.</p>",
                questions: [
                    { type: 'list', badge: 'core', prompt: 'Give 3 reasons the best widget ever could still fail to sell. (Don\'t just say "it broke" — think beyond the tech itself.)', lines: 3, placeholders: ['1. ...', '2. ...', '3. ...'] }
                ]
            },
            framework: {
                label: 'The Innovation Triangle',
                timing: '5–12 min · Mini-Lecture',
                intro: '<p>An innovation only succeeds if three things ALL line up:</p>',
                factors: [
                    { title: 'TECH', text: 'Does the technology actually work and solve the stated problem?' },
                    { title: 'PEOPLE', text: 'Do real users adopt it — is it trusted, accessible, and easy enough to use?' },
                    { title: 'BUSINESS', text: 'Is there a viable model to fund, distribute, and sustain it at scale?' }
                ],
                anchor: 'Tech alone is not enough.'
            },
            groups: [
                {
                    name: 'GCash',
                    accent: 'mango',
                    dossier: {
                        title: 'Case Dossier — GCash',
                        facts: [
                            "Launched October 2004 as an SMS-based money-transfer service — Globe Telecom's answer to Smart Padala, not a technological breakthrough.",
                            "As of Dec. 31, 2025: ~90 million registered users and 39.1 million monthly active users — nearly half the Philippines' adult population.",
                            '78% of active users are OUTSIDE Metro Manila, and 92% belong to lower-income segments — real financial inclusion, not just a Manila app.',
                            'Processed ₱17 trillion in gross transaction value in 2025 (56.7M transactions/day average); parent company Mynt filed in June 2026 for a ₱92.3-billion IPO — set to be the largest in Philippine stock market history.'
                        ],
                        source: 'Sources: Wikipedia "GCash"; BusinessWorld/Philstar/Inquirer, Mynt IPO filing coverage (June 2026).'
                    },
                    factors: [
                        { title: 'TECH', question: 'Did the technology work?' },
                        { title: 'PEOPLE', question: 'Did people actually adopt it?' },
                        { title: 'BUSINESS', question: 'Was there a model to profit & scale?' }
                    ]
                },
                {
                    name: 'Kodak',
                    accent: 'teal',
                    dossier: {
                        title: 'Case Dossier — Kodak',
                        facts: [
                            "Kodak engineer Steven Sasson invented the first digital camera in 1975 — inside Kodak's own labs.",
                            'Kodak shelved the digital camera to protect its dominant, highly profitable film business rather than bring it to market.',
                            'Despite inventing the core technology nearly two decades before digital cameras went mainstream, Kodak filed for bankruptcy in January 2012.'
                        ],
                        source: 'Sources: The Guardian, "Kodak\'s Digital Moment" (2012).'
                    },
                    factors: [
                        { title: 'TECH', question: 'Did the technology work?' },
                        { title: 'PEOPLE', question: 'Did people actually adopt it?' },
                        { title: 'BUSINESS', question: 'Was there a model to profit & scale?' }
                    ]
                },
                {
                    name: 'Friendster (Philippines)',
                    accent: 'purple',
                    dossier: {
                        title: 'Case Dossier — Friendster in the Philippines',
                        facts: [
                            "Launched March 2002 and became the Philippines' first mass social network, spread through internet cafés nationwide — by 2008, the Philippines accounted for 39% of ALL Friendster traffic worldwide, its single largest market anywhere.",
                            "In 2003, Friendster turned down a $30 million buyout offer from Google — later called one of Silicon Valley's biggest blunders.",
                            'Chronic server crashes and slow load times, worsened by the disproportionate volume of Philippine traffic, drove users away; Facebook overtook Friendster in the Philippines by 2009.',
                            'Friendster actually relaunched in the Philippines in April 2026 as a stripped-down, ad-free app — a nostalgia-driven comeback, 11 years after it shut down.'
                        ],
                        source: 'Sources: Wikipedia "Friendster"; GMA News Online; M2 Comms (2026).'
                    },
                    factors: [
                        { title: 'TECH', question: 'Did the technology work?' },
                        { title: 'PEOPLE', question: 'Did people actually adopt it?' },
                        { title: 'BUSINESS', question: 'Was there a model to profit & scale?' }
                    ]
                }
            ],
            reflection: {
                label: 'Reflection',
                timing: '40–60 min · Applied Work & Wrap-Up',
                questions: [
                    { type: 'text', badge: 'core', prompt: 'If Kodak had launched the digital camera in 1975, what business change would have had to happen alongside it? Write 2–3 sentences.', rows: 3, placeholder: 'Kodak would have had to...' },
                    {
                        type: 'choice', badge: 'core', prompt: 'Which corner of the Innovation Triangle did Kodak fail hardest on?',
                        options: [
                            { text: 'Tech', note: "Worth reconsidering — Kodak's technology was genuinely ahead of its time. Rating Tech as the failure confuses invention with commercialization." },
                            { text: 'People', note: "Defensible, but downstream — nobody adopted the digital camera because it was never released to them. That's a real gap, but it's a symptom of a deeper failure." },
                            { text: 'Business', note: 'The strongest answer. The core failure was a strategic business decision — protecting film profits and refusing to commercialize a threat to the existing model.' }
                        ]
                    },
                    { type: 'text', badge: 'bonus', prompt: 'Name one other company or product you know of that had great technology but still failed. What corner did it miss?', rows: 2, placeholder: 'Company/product + which corner it missed...' }
                ]
            }
        }
    },
    chapter_worksheet: {
        hint: 'Read-only timed-move table + a worked-example "Model" callout + a fixed sequence of typed steps (text/grid/choice/checklist) + a "Trap" warning callout + a peer-check question + a team/date/filed/peer-checked-by sign-off. Not auto-graded. This example is the full Worksheet 1 "The Problem" chapter from the Feasibility Study Worksheet Pack (IS Innovations, 10x45min dossier pack) — ready to use as-is; the other 9 worksheets in the pack follow this exact same shape.',
        example: {
            meta: {
                eyebrow: 'WORKSHEET 1 · 45 MINUTES · PRODUCES DOSSIER CHAPTER 1',
                title: 'The Problem',
                sub: 'You leave this session with: Chapter 1 — your problem, stated with evidence instead of anecdote'
            },
            timeline: {
                label: 'How this session runs',
                moves: [
                    { time: '0–5', move: 'Read the model', detail: "Read Ch. 1 of the Carmen Market study. Notice it never says the word 'app'." },
                    { time: '5–12', move: 'Name it', detail: 'Write your problem in ONE sentence, with no solution in it.' },
                    { time: '12–25', move: 'Evidence hunt', detail: 'Fill the evidence grid — who said it, what number, what you saw.' },
                    { time: '25–35', move: 'Make it an IS problem', detail: 'Write the paragraph explaining why this is a records/data problem.' },
                    { time: '35–42', move: 'Peer check', detail: 'Partner hunts for a solution hiding inside your problem statement.' },
                    { time: '42–45', move: 'File it', detail: 'Date, initial, file.' }
                ]
            },
            model: {
                label: 'THE MODEL — how the Carmen Market study did it',
                html: '<p><strong>Problem sentence:</strong> “Carmen market vendors have no transaction record, so they cannot qualify for microfinance — and the treasurer reconciles rental income by hand with no audit trail.”</p>' +
                      '<p><strong>Notice:</strong> no solution appears anywhere in that sentence. No QR, no app, no GCash. Just the problem.</p>' +
                      '<p>Their evidence had three layers — vendor quotes (what people said), national statistics (56% of adults banked; 52.8% of PH retail payments now digital), and a process observation (paper logbook, manual cash count).</p>'
            },
            steps: [
                {
                    type: 'text',
                    label: 'STEP 1 — Your problem, in one sentence',
                    instruction: "Banned words: app, system, website, platform, digital, automate, AI. If you need one of these to state your problem, you are stating a solution, not a problem.",
                    prefix: 'Our problem is:',
                    rows: 2,
                    placeholder: 'State the pain, not the fix...'
                },
                {
                    type: 'grid',
                    label: 'STEP 2 — Evidence grid',
                    instruction: 'Three kinds of evidence. You need at least two rows filled to pass; the third is where good teams separate themselves.',
                    columns: [
                        { name: 'What you actually have', type: 'text' },
                        { name: 'Where it came from', type: 'text' }
                    ],
                    rows: [
                        { label: 'Someone said it', sub: '(a real quote)' },
                        { label: 'A number', sub: '(a real statistic)' },
                        { label: 'You observed it', sub: '(a real process)' }
                    ],
                    note: 'CORE: two evidence rows filled, one from a real named source. BONUS: all three rows, with a citation your instructor could verify.'
                },
                {
                    type: 'text',
                    label: 'STEP 3 — Why is this an Information Systems problem?',
                    instruction: "Not 'why does it matter' — why is it about records, data, or information flow? If you cannot answer this, you may have picked a problem for a different course.",
                    prefix: 'This is an IS problem because:',
                    rows: 3
                }
            ],
            trap: {
                label: 'THE TRAP',
                html: "<p>The most common failure in this worksheet is a problem sentence with the answer already inside it — 'our barangay needs a mobile app for reporting floods.' That is not a problem, it is a solution wearing a problem's clothes. It locks your team into one answer before Chapter 4 has had a chance to tell you something better already exists. State the pain, not the fix.</p>"
            },
            peer_check: {
                label: 'PEER CHECK',
                instruction: 'Swap with another team. Their job is not to be nice — it is to find the specific weakness named below.',
                task: "Read their problem sentence. Is there a solution hidden in it? Circle the word. Then ask: 'what number or quote proves this problem is real, and not just annoying?' Write their answer — or write NONE GIVEN.",
                rows: 3
            },
            file_it: {
                label: 'FILE IT',
                instruction: "Date it, initial it, and add it to your team's dossier folder. A chapter that is not filed does not exist."
            }
        }
    },
    project_proposal: {
        hint: 'Title/type/client/problem header fields + a repeatable features table where each planned feature is tagged Create/Read/Update/Delete. "require_all_crud" shows a soft, non-blocking coverage hint (never blocks submit). Not auto-graded — score it manually like Worksheet Form.',
        example: {
            instructions: 'Propose a CRUD system you will build this term. It may be a C console app or a Web application. Be specific about who will use it and what problems it solves.',
            project_types: ['C Programming', 'Web'],
            min_features: 4,
            require_all_crud: true
        }
    }
};
