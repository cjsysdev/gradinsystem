<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Introduction to Frameworks</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --success: #16a34a;
            --danger: #dc2626;
            --warning: #ea580c;
            --light: #f5f5f5;
            --dark: #1f2937;
            --border: #ddd;
            --bg: #ffffff;
        }

        html, body {
            width: 100%;
            height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
            overflow: hidden;
        }

        .container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100%;
        }

        .header {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            padding: 1rem;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .header-title {
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .header-close {
            background: transparent;
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            min-width: 40px;
            min-height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
            flex-shrink: 0;
        }

        .header-close:active {
            color: rgba(255, 255, 255, 0.8);
        }

        .stats-bar {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stat-item {
            text-align: center;
            min-width: 75px;
        }

        .stat-label {
            font-size: 0.7rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.4rem;
            font-weight: 700;
        }

        .progress-section {
            background: rgba(255, 255, 255, 0.2);
            height: 6px;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: white;
            transition: width 0.4s ease;
        }

        .content-wrapper {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            background: transparent;
        }

        .content-scroll {
            display: flex;
            flex-direction: column;
            padding: 1.5rem 1rem;
        }

        .section-container {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .lesson-section {
            background: white;
            padding: 1.5rem;
            animation: slideIn 0.4s ease;
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .lesson-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
            word-wrap: break-word;
        }

        .lesson-text {
            font-size: 1rem;
            line-height: 1.6;
            color: #555;
            margin-bottom: 1rem;
            word-wrap: break-word;
        }

        .lesson-text:last-child {
            margin-bottom: 0;
        }

        .lesson-text ul {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }

        .lesson-text li {
            margin-bottom: 0.5rem;
        }

        .highlight {
            background: #dbeafe;
            padding: 0.2em 0.4em;
            border-radius: 3px;
            font-weight: 600;
            color: var(--dark);
        }

        .file-tree {
            background: #1f2937;
            color: #f3f4f6;
            padding: 1.25rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            line-height: 1.7;
            margin: 1rem 0;
            overflow-x: auto;
            border-left: 4px solid var(--warning);
        }

        .file-tree-folder {
            color: #fbbf24;
            font-weight: 600;
        }

        .file-tree-file {
            color: #86efac;
        }

        .url-path {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-left: 4px solid var(--primary);
            padding: 0.75rem 1rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            color: var(--dark);
            margin: 1rem 0;
            word-break: break-all;
        }

        .comparison-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin: 1rem 0;
        }

        .comparison-col {
            background: #f0f9ff;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }

        .comparison-label {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .comparison-col ul {
            list-style: none;
            padding-left: 0;
        }

        .comparison-col li {
            padding: 0.4rem 0;
            color: #555;
            font-size: 0.9rem;
        }

        .comparison-col li::before {
            content: "→ ";
            color: var(--primary);
            font-weight: bold;
            margin-right: 0.5rem;
        }

        .mapping-diagram {
            background: white;
            border: 2px solid var(--primary);
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .mapping-row {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1rem;
        }

        .mapping-row:last-child {
            margin-bottom: 0;
        }

        .mapping-item {
            background: #f0f9ff;
            padding: 0.75rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            word-break: break-all;
            border-left: 3px solid var(--primary);
        }

        .mapping-arrow {
            text-align: center;
            font-size: 1.2rem;
            color: var(--warning);
            font-weight: 700;
        }

        .quiz-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            animation: slideIn 0.4s ease;
            animation-delay: 0.1s;
            animation-fill-mode: both;
        }

        .quiz-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--warning);
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .question-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1.25rem;
            word-wrap: break-word;
            line-height: 1.5;
        }

        .question-code {
            background: #f4f4f4;
            border: 1px solid #e0e0e0;
            border-left: 4px solid var(--primary);
            padding: 1rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            line-height: 1.6;
            color: var(--dark);
            margin: 1rem 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .options {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .option {
            padding: 1rem 1.25rem;
            background: white;
            border: 2px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--dark);
            transition: all 0.2s ease;
            text-align: left;
            word-wrap: break-word;
        }

        .option:active:not(.disabled) {
            transform: scale(0.98);
        }

        .option:hover:not(.disabled) {
            border-color: var(--primary);
            background: #eff6ff;
        }

        .option.selected {
            background: #eff6ff;
            border-color: var(--primary);
            color: var(--primary);
            font-weight: 700;
        }

        .option.correct {
            background: #dcfce7;
            border-color: var(--success);
            color: #15803d;
            font-weight: 700;
        }

        .option.incorrect {
            background: #fee2e2;
            border-color: var(--danger);
            color: #991b1b;
            font-weight: 700;
        }

        .option.disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        .feedback {
            display: none;
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 600;
            border-left: 4px solid;
            animation: slideIn 0.3s ease;
        }

        .feedback.show {
            display: block;
        }

        .feedback.correct {
            background: #dcfce7;
            border-color: var(--success);
            color: #15803d;
        }

        .feedback.incorrect {
            background: #fee2e2;
            border-color: var(--danger);
            color: #991b1b;
        }

        .streak-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2.5rem 2rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            animation: popIn 0.4s ease;
            display: none;
            max-width: 90%;
            width: 300px;
        }

        .streak-popup.show {
            display: block;
        }

        .streak-emoji {
            font-size: 3.5rem;
            margin-bottom: 0.75rem;
            animation: bounce 0.6s ease infinite;
            display: inline-block;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        .streak-text {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--warning);
            margin-bottom: 0.5rem;
        }

        .streak-subtext {
            font-size: 1rem;
            color: #666;
        }

        @keyframes popIn {
            0% {
                transform: translate(-50%, -50%) scale(0.5);
                opacity: 0;
            }
            50% {
                transform: translate(-50%, -50%) scale(1.1);
            }
            100% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }

        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            display: none;
            z-index: 999;
        }

        .modal-backdrop.show {
            display: block;
        }

        .congrats-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2.5rem 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            z-index: 1001;
            animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: none;
            max-width: 90%;
            width: 340px;
        }

        .congrats-modal.show {
            display: block;
        }

        .congrats-emoji {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 0.8s ease infinite;
            display: inline-block;
        }

        .congrats-title {
            font-size: 2rem;
            font-weight: 900;
            color: var(--dark);
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary), var(--warning));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .congrats-text {
            font-size: 1rem;
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
            padding: 1.5rem 1rem;
            border-radius: 12px;
            color: white;
        }

        .stat-card.success {
            background: linear-gradient(135deg, var(--success) 0%, #15803d 100%);
        }

        .stat-card-label {
            font-size: 0.8rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .stat-card-value {
            font-size: 2rem;
            font-weight: 900;
        }

        .congrats-button {
            background: linear-gradient(135deg, var(--primary), #1e40af);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            letter-spacing: 0.5px;
        }

        .congrats-button:active {
            transform: scale(0.98);
        }

        .button-section {
            background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
            padding: 1.25rem 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.5);
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: center;
            flex-shrink: 0;
            padding-bottom: 60px;
        }

        button {
            padding: 0.85rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            letter-spacing: 0.5px;
            min-width: 110px;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary), #1e40af);
            color: white;
            flex: 1;
            max-width: 100%;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
        }

        .btn-submit:active:not(:disabled) {
            transform: scale(0.98);
            box-shadow: 0 1px 4px rgba(37, 99, 235, 0.2);
        }

        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary);
            border: 2px solid white;
        }

        .btn-back:active {
            background: white;
        }

        .btn-back:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            border-color: #ccc;
            color: #ccc;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #999;
        }

        @media (min-width: 768px) {
            .content-scroll {
                padding: 2rem;
                max-width: 700px;
                margin: 0 auto;
                width: 100%;
            }

            .lesson-section, .quiz-section {
                padding: 2rem;
            }

            .header-title {
                font-size: 1.4rem;
            }

            .lesson-title {
                font-size: 1.75rem;
            }

            .comparison-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 767px) {
            .comparison-grid {
                grid-template-columns: 1fr;
            }

            .mapping-row {
                grid-template-columns: 1fr;
            }

            .mapping-arrow {
                transform: rotate(90deg);
                margin: 0.5rem 0;
            }
        }

        @keyframes slideUp {
            0% {
                transform: translate(-50%, -50%) translateY(30px);
                opacity: 0;
            }
            100% {
                transform: translate(-50%, -50%) translateY(0);
                opacity: 1;
            }
        }

        .info-box {
            background: #dbeafe;
            border-left: 4px solid var(--primary);
            padding: 1rem;
            border-radius: 6px;
            margin: 1rem 0;
        }

        .info-box strong {
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-top">
                <div class="header-title">🧩 Introduction to Frameworks</div>
                <a class="button header-close" href="<?= base_url('interactive_quiz/topics') ?>" style="text-decoration:none;">✕</a>
            </div>
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-label">Score</div>
                    <div class="stat-value" id="score">0</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Streak 🔥</div>
                    <div class="stat-value" id="streak">0</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Progress</div>
                    <div class="stat-value" id="progress">1/6</div>
                </div>
            </div>
            <div class="progress-section">
                <div class="progress-fill" id="progressFill"></div>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="content-scroll">
                <div class="section-container">
                    <div class="lesson-section" id="lessonSection"></div>
                    <div class="quiz-section">
                        <div class="quiz-label">Question</div>
                        <div class="question-text" id="questionText"></div>
                        <div class="options" id="optionsContainer"></div>
                        <div class="feedback" id="feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="button-section">
            <button class="btn-back" id="backBtn" onclick="previousSection()">← Back</button>
            <button class="btn-submit" id="submitBtn" onclick="submitAnswer()">Submit</button>
        </div>
    </div>

    <div class="modal-backdrop" id="backdrop"></div>
    <div class="streak-popup" id="streakPopup">
        <div class="streak-emoji">🔥</div>
        <div class="streak-text"><span id="streakCount">3</span> in a row!</div>
        <div class="streak-subtext">Keep it up!</div>
    </div>

    <div class="modal-backdrop" id="congratsBackdrop"></div>
    <div class="congrats-modal" id="congratsModal">
        <div class="congrats-emoji">🎉</div>
        <div class="congrats-title">Congratulations!</div>
        <div class="congrats-text">You've got a solid handle on what frameworks are and why we use them!</div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-label">Final Score</div>
                <div class="stat-card-value" id="finalScore">10</div>
            </div>
            <div class="stat-card success">
                <div class="stat-card-label">Best Streak</div>
                <div class="stat-card-value" id="bestStreak">5</div>
            </div>
        </div>
        <button class="congrats-button" onclick="restartQuiz()">Start Over</button>
    </div>

    <script>
        const sections = [
            {
                id: 0,
                title: "What Is a Framework?",
                lesson: `<div class="lesson-title">Building With a Framework vs. Building From Scratch</div>
                    <div class="lesson-text">
                        A <span class="highlight">framework</span> is a pre-built collection of code, rules, and structure
                        that gives you a foundation to build an application on — instead of writing every piece yourself.
                    </div>
                    <div class="lesson-text">
                        Think of building a house. You <em>could</em> cut every plank, mix your own cement, and design the
                        wiring layout from zero. Or you could start from a pre-engineered frame that already has the walls,
                        plumbing routes, and electrical layout planned — you just add the rooms you need.
                    </div>
                    <div class="info-box">
                        <strong>💡 Remember:</strong> A framework doesn't remove your control — it removes the need to
                        rebuild the same foundation every single project.
                    </div>
                    <div class="lesson-text"><strong>In web development</strong>, a framework typically already includes routing,
                        database access helpers, security defaults, and a folder structure — like CodeIgniter, the framework
                        used in this course.</div>`,
                quiz: {
                    question: "Which of these best describes what a framework provides?",
                    options: [
                        "A finished website you can't change",
                        "A pre-built foundation and structure you build your application on top of",
                        "A single function for connecting to a database",
                        "A code editor for writing PHP"
                    ],
                    correct: 1
                }
            },
            {
                id: 1,
                title: "Why Use a Framework? (The Problem It Solves)",
                lesson: `<div class="lesson-title">The Problem: Rebuilding the Same Things Over and Over</div>
                    <div class="lesson-text">
                        Every web application needs some version of the same basic plumbing: reading URLs, talking to a
                        database, validating user input, managing login sessions, and preventing common attacks like SQL
                        injection.
                    </div>
                    <div class="lesson-text">
                        Without a framework, a developer has to write all of that from scratch — and re-solve the same
                        problems (and make the same mistakes) on every new project.
                    </div>
                    <div class="comparison-grid">
                        <div class="comparison-col">
                            <div class="comparison-label">🛠️ Without a Framework</div>
                            <ul>
                                <li>Write your own routing logic</li>
                                <li>Write your own DB connection/escaping</li>
                                <li>Write your own session handling</li>
                                <li>Re-solve security issues yourself</li>
                            </ul>
                        </div>
                        <div class="comparison-col">
                            <div class="comparison-label">🧩 With a Framework</div>
                            <ul>
                                <li>Routing is already built in</li>
                                <li>A query builder handles the database safely</li>
                                <li>A session library is ready to use</li>
                                <li>Security defaults are already applied</li>
                            </ul>
                        </div>
                    </div>
                    <div class="info-box">
                        <strong>⭐ Key takeaway:</strong> Frameworks exist so developers can focus on what makes
                        <em>their</em> application unique, instead of re-solving problems every project already has.
                    </div>`,
                quiz: {
                    question: "What is the main problem that frameworks are designed to solve?",
                    options: [
                        "Making websites load faster on mobile phones",
                        "Repeatedly rebuilding the same common plumbing (routing, DB access, security) on every project",
                        "Replacing the need to learn a programming language",
                        "Designing the visual appearance of a website"
                    ],
                    correct: 1
                }
            },
            {
                id: 2,
                title: "MVC: The Common Framework Pattern",
                lesson: `<div class="lesson-title">Model, View, Controller — Three Separate Jobs</div>
                    <div class="lesson-text">
                        Most web frameworks — including CodeIgniter — organize code using the
                        <span class="highlight">MVC pattern</span>: Model, View, Controller. Each piece has exactly one job.
                    </div>
                    <div class="mapping-diagram">
                        <div class="mapping-row">
                            <div class="mapping-item"><strong>Controller</strong><br>Receives the request, decides what to do, and directs traffic.</div>
                        </div>
                        <div class="mapping-row">
                            <div class="mapping-item"><strong>Model</strong><br>The only part allowed to talk to the database — fetches or saves data.</div>
                        </div>
                        <div class="mapping-row">
                            <div class="mapping-item"><strong>View</strong><br>Displays the final result to the user. No database queries here.</div>
                        </div>
                    </div>
                    <div class="lesson-text">
                        A simple way to picture it: if this were a school office, the <strong>Controller</strong> is the
                        front desk that greets you and figures out what you need, the <strong>Model</strong> is the
                        registrar who is the only one allowed to open the filing cabinets, and the <strong>View</strong>
                        is the classroom where the information actually gets shown.
                    </div>
                    <div class="info-box">
                        <strong>✅ Why it matters:</strong> Keeping these jobs separate makes code easier to find, easier
                        to fix, and easier to reuse — a bug in how data displays won't be hiding inside your database logic.
                    </div>`,
                quiz: {
                    question: "In the MVC pattern, which part is responsible for directly querying the database?",
                    options: [
                        "The View",
                        "The Controller",
                        "The Model",
                        "None — all three query the database equally"
                    ],
                    correct: 2
                }
            },
            {
                id: 3,
                title: "Popular Frameworks in Web Development",
                lesson: `<div class="lesson-title">Different Languages, Same Idea</div>
                    <div class="lesson-text">
                        Almost every major programming language used for the web has at least one popular framework built
                        around the same core idea: give developers a structure so they don't start from zero.
                    </div>
                    <div class="file-tree">
<span class="file-tree-folder">PHP</span>
  <span class="file-tree-file">CodeIgniter</span>  ← used in this course
  <span class="file-tree-file">Laravel</span>
<span class="file-tree-folder">Python</span>
  <span class="file-tree-file">Django</span>
  <span class="file-tree-file">Flask</span>
<span class="file-tree-folder">JavaScript (Node.js)</span>
  <span class="file-tree-file">Express</span>
<span class="file-tree-folder">Ruby</span>
  <span class="file-tree-file">Ruby on Rails</span>
                    </div>
                    <div class="lesson-text">
                        This LMS you're using right now is itself built with <span class="highlight">CodeIgniter 3</span> —
                        its <code style="background: #f0f9ff; padding: 0.2em 0.4em;">application/</code> folder holds the
                        controllers, models, and views that make attendance, grading, and discussions work.
                    </div>
                    <div class="info-box">
                        <strong>💡 Note:</strong> The specific framework changes, but the reasons for using one — avoiding
                        repeated work, safer defaults, shared structure — stay the same across all of them.
                    </div>`,
                quiz: {
                    question: "Which of the following is a PHP framework?",
                    options: [
                        "Django",
                        "Express",
                        "CodeIgniter",
                        "Ruby on Rails"
                    ],
                    correct: 2
                }
            },
            {
                id: 4,
                title: "Framework vs. Library: Who's in Control?",
                lesson: `<div class="lesson-title">The Key Difference: Who Calls Whom</div>
                    <div class="lesson-text">
                        People often mix up <span class="highlight">frameworks</span> and
                        <span class="highlight">libraries</span>, but there's one clear difference: <strong>who is in
                        control of the program's flow.</strong>
                    </div>
                    <div class="comparison-grid">
                        <div class="comparison-col">
                            <div class="comparison-label">📚 A Library</div>
                            <ul>
                                <li>You call it when you need it</li>
                                <li>You stay in control of the flow</li>
                                <li>Example: calling a date-formatting function</li>
                            </ul>
                        </div>
                        <div class="comparison-col">
                            <div class="comparison-label">🧩 A Framework</div>
                            <ul>
                                <li>It calls your code at the right moment</li>
                                <li>It controls the overall flow</li>
                                <li>Example: it calls your controller method when a URL is visited</li>
                            </ul>
                        </div>
                    </div>
                    <div class="info-box">
                        <strong>⭐ This is sometimes called "Inversion of Control":</strong> instead of your code calling
                        the tool, the tool calls your code, at the moment it decides is appropriate.
                    </div>
                    <div class="lesson-text">
                        In CodeIgniter, you never write <code style="background: #f0f9ff; padding: 0.2em 0.4em;">index.php</code>
                        yourself calling your controller directly — the framework receives the request first and calls
                        your controller's method for you.
                    </div>`,
                quiz: {
                    question: "What is the key difference between a framework and a library?",
                    options: [
                        "Frameworks are always faster than libraries",
                        "A framework calls your code at the right time; with a library, you call it yourself",
                        "Libraries can only be used in PHP",
                        "There is no real difference between them"
                    ],
                    correct: 1
                }
            },
            {
                id: 5,
                title: "When (and When Not) to Use a Framework",
                lesson: `<div class="lesson-title">Frameworks Aren't Always the Right Tool</div>
                    <div class="lesson-text">
                        A framework adds real value for medium-to-large applications — but for a 10-line script that just
                        needs to run once, all that extra structure can be overkill.
                    </div>
                    <div class="lesson-text"><strong>Good reasons to use a framework:</strong></div>
                    <div class="lesson-text">
                        <ul>
                            <li>The project will grow and be maintained by more than one person</li>
                            <li>You need reliable security defaults (input escaping, session handling)</li>
                            <li>You want a consistent, predictable folder structure across the team</li>
                            <li>The project needs a database, routing, and user accounts</li>
                        </ul>
                    </div>
                    <div class="lesson-text"><strong>When it might be overkill:</strong></div>
                    <div class="lesson-text">
                        <ul>
                            <li>A one-off script that runs once and is thrown away</li>
                            <li>A tiny personal tool with no database or routing needs</li>
                            <li>Learning the raw language fundamentals first, before adding structure</li>
                        </ul>
                    </div>
                    <div class="info-box">
                        <strong>✅ Bottom line:</strong> A framework is a tool for managing complexity. If there isn't much
                        complexity yet, the framework's structure may cost more than it saves.
                    </div>`,
                quiz: {
                    question: "Which situation is the framework LEAST likely to be worth the extra structure it adds?",
                    options: [
                        "A gradebook system used by an entire college, maintained by several developers",
                        "A one-off 10-line script you'll run once and delete",
                        "A student portal that needs logins, database access, and routing",
                        "An LMS that multiple people will maintain for years"
                    ],
                    correct: 1
                }
            }
        ];

        let currentSection = 0;
        let score = 0;
        let streak = 0;
        let bestStreak = 0;
        let selectedOption = null;
        let answered = false;
        let currentShuffledOptions = [];
        let currentCorrectIndex = 0;

        function shuffleArray(array) {
            const shuffled = [...array];
            for (let i = shuffled.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
            }
            return shuffled;
        }

        function createShuffledOptions(options, correctIndex) {
            const optionsWithIndex = options.map((option, index) => ({
                text: option,
                originalIndex: index
            }));
            const shuffled = shuffleArray(optionsWithIndex);
            const newCorrectIndex = shuffled.findIndex(opt => opt.originalIndex === correctIndex);
            return {
                options: shuffled.map(opt => opt.text),
                correctIndex: newCorrectIndex
            };
        }

        function init() {
            renderContent();
        }

        function renderContent() {
            const section = sections[currentSection];
            const shuffled = createShuffledOptions(section.quiz.options, section.quiz.correct);
            currentShuffledOptions = shuffled.options;
            currentCorrectIndex = shuffled.correctIndex;

            document.getElementById('lessonSection').innerHTML = section.lesson;
            document.getElementById('questionText').innerHTML = section.quiz.question;
            renderOptions();

            const feedback = document.getElementById('feedback');
            feedback.className = 'feedback';
            feedback.textContent = '';

            answered = false;
            selectedOption = null;
            updateUI();
        }

        function renderOptions() {
            const container = document.getElementById('optionsContainer');
            container.innerHTML = '';
            currentShuffledOptions.forEach((option, index) => {
                const opt = document.createElement('div');
                opt.className = 'option';
                opt.textContent = option;
                opt.dataset.index = index;
                opt.onclick = () => selectOption(index);
                container.appendChild(opt);
            });
        }

        function selectOption(index) {
            if (answered) return;
            document.querySelectorAll('.option').forEach(opt => opt.classList.remove('selected'));
            document.querySelector(`[data-index="${index}"]`).classList.add('selected');
            selectedOption = index;
        }

        function submitAnswer() {
            if (answered) {
                nextSection();
                return;
            }
            if (selectedOption === null) {
                alert('Please select an option!');
                return;
            }

            const correct = selectedOption === currentCorrectIndex;
            const feedback = document.getElementById('feedback');
            const options = document.querySelectorAll('.option');

            options.forEach(opt => opt.classList.add('disabled'));
            answered = true;

            if (correct) {
                document.querySelector(`[data-index="${selectedOption}"]`).classList.add('correct');
                feedback.className = 'feedback show correct';
                feedback.textContent = '✓ Correct! +2 points';
                score += 2;
                streak++;
                bestStreak = Math.max(bestStreak, streak);

                if (streak > 0 && streak % 3 === 0) {
                    showStreakPopup(streak);
                }
            } else {
                document.querySelector(`[data-index="${selectedOption}"]`).classList.add('incorrect');
                document.querySelector(`[data-index="${currentCorrectIndex}"]`).classList.add('correct');
                feedback.className = 'feedback show incorrect';
                feedback.textContent = `✗ Incorrect. The correct answer is option ${currentCorrectIndex + 1}.`;
                streak = 0;
            }

            updateUI();
            document.getElementById('submitBtn').textContent = 'Next →';
        }

        function showStreakPopup(count) {
            const popup = document.getElementById('streakPopup');
            const backdrop = document.getElementById('backdrop');
            document.getElementById('streakCount').textContent = count;
            popup.classList.add('show');
            backdrop.classList.add('show');
            setTimeout(() => {
                popup.classList.remove('show');
                backdrop.classList.remove('show');
            }, 1500);
        }

        function nextSection() {
            if (currentSection < sections.length - 1) {
                currentSection++;
                renderContent();
            } else {
                showCongratsModal();
            }
        }

        function showCongratsModal() {
            const modal = document.getElementById('congratsModal');
            const backdrop = document.getElementById('congratsBackdrop');
            document.getElementById('finalScore').textContent = score;
            document.getElementById('bestStreak').textContent = bestStreak;
            modal.classList.add('show');
            backdrop.classList.add('show');
        }

        function restartQuiz() {
            currentSection = 0;
            score = 0;
            streak = 0;
            bestStreak = 0;
            selectedOption = null;
            answered = false;
            document.getElementById('congratsModal').classList.remove('show');
            document.getElementById('congratsBackdrop').classList.remove('show');
            renderContent();
        }

        function previousSection() {
            if (currentSection > 0) {
                currentSection--;
                renderContent();
            }
        }

        function updateUI() {
            document.getElementById('score').textContent = score;
            document.getElementById('streak').textContent = streak;
            document.getElementById('progress').textContent = (currentSection + 1) + '/' + sections.length;
            const progress = ((currentSection + 1) / sections.length) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
            document.getElementById('backBtn').disabled = currentSection === 0;
            if (answered) {
                document.getElementById('submitBtn').textContent = currentSection === sections.length - 1 ? 'Finish' : 'Next →';
            } else {
                document.getElementById('submitBtn').textContent = 'Submit';
            }
        }

        function confirmExit() {
            if (score > 0) {
                if (confirm('Are you sure? Your progress will be lost.')) {
                    window.close();
                }
            } else {
                window.close();
            }
        }

        window.addEventListener('load', init);
    </script>
</body>
</html>
