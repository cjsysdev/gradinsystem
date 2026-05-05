<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WAMP: File Directories & URLs</title>
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
                <div class="header-title">🗂️ WAMP: File Directories & URLs</div>
                <a class="button header-close" href="<?= base_url('discussion') ?>" style="text-decoration:none;">✕</a>
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
        <div class="congrats-text">You mastered WAMP file directories & URLs!</div>
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
                title: "The WAMP Foundation",
                lesson: `<div class="lesson-title">Understanding WAMP & the Web</div>
                    <div class="lesson-text">
                        <span class="highlight">WAMP</span> stands for <strong>Windows, Apache, MySQL, and PHP</strong>. 
                        It's a complete local development environment for building web applications.
                    </div>
                    <div class="lesson-text">
                        When you type a URL in your browser (like <code style="background: #f0f9ff; padding: 0.2em 0.4em; border-radius: 3px;">http://localhost</code>), 
                        your browser sends a request to <span class="highlight">Apache</span>, the web server. 
                        Apache then finds the files on your computer and sends them back.
                    </div>
                    <div class="lesson-text"><strong>The Key Insight:</strong> Your computer's folder structure translates into web addresses!</div>
                    <div class="info-box">
                        <strong>💡 Remember:</strong> Files on your disk → Apache → Web URLs in browser
                    </div>
                    <div class="lesson-text"><strong>Why does this matter?</strong> When you build a website, you need to understand how Apache finds your files using URLs. This knowledge helps you organize projects, link resources, and debug issues.</div>`,
                quiz: {
                    question: "In WAMP, which component is responsible for receiving URL requests and serving files?",
                    options: [
                        "MySQL (database server)",
                        "Apache (web server)",
                        "PHP (programming language)",
                        "Windows (operating system)"
                    ],
                    correct: 1
                }
            },
            {
                id: 1,
                title: "The WAMP Directory Structure",
                lesson: `<div class="lesson-title">Where Files Live: The Document Root</div>
                    <div class="lesson-text">
                        When you install WAMP (via XAMPP or WAMPSERVER), it creates a folder structure. 
                        The most important folder is the <span class="highlight">document root</span> — this is where all your web projects live.
                    </div>
                    <div class="file-tree">
<span class="file-tree-folder">📁 C:\\wamp64</span> ← Main WAMP installation
  <span class="file-tree-folder">📁 www</span> ← <strong>Document Root (all web projects here!)</strong>
    <span class="file-tree-folder">📁 myapp</span>
      <span class="file-tree-file">📄 index.php</span>
      <span class="file-tree-file">📄 about.php</span>
    <span class="file-tree-folder">📁 quiz</span>
      <span class="file-tree-file">📄 results.php</span>
      <span class="file-tree-file">📄 submit.php</span>
    <span class="file-tree-folder">📁 admin</span>
      <span class="file-tree-file">📄 dashboard.php</span>
                    </div>
                    <div class="info-box">
                        <strong>⭐ Critical:</strong> The <code style="background: white; padding: 0.2em 0.4em;">www</code> folder is Apache's home base. 
                        Everything you create goes inside this folder!
                    </div>
                    <div class="lesson-text">
                        When Apache looks for a file, it always starts from <code style="background: #f0f9ff; padding: 0.2em 0.4em;">C:\\wamp64\\www\\</code>. 
                        This path is called the <span class="highlight">document root</span>.
                    </div>`,
                quiz: {
                    question: "On Windows with WAMP installed, where is the document root folder typically located?",
                    options: [
                        "C:\\Program Files\\WAMP\\",
                        "C:\\wamp64\\www\\",
                        "C:\\Users\\YourName\\Documents\\",
                        "C:\\Windows\\System32\\"
                    ],
                    correct: 1
                }
            },
            {
                id: 2,
                title: "Converting Paths to URLs",
                lesson: `<div class="lesson-title">The Path-to-URL Translation</div>
                    <div class="lesson-text">
                        Here's where the magic happens: When Apache sees a URL, it converts it to a file path. 
                        And vice versa — when you create a file in your folders, it becomes accessible via a URL.
                    </div>
                    <div class="lesson-text"><strong>The Golden Rule:</strong></div>
                    <div class="info-box">
                        <strong>Drop everything before \\www\\ from your file path to get the URL!</strong><br>
                        Then convert backslashes (\\) to forward slashes (/)
                    </div>
                    <div class="lesson-text"><strong>Example 1: Simple File</strong></div>
                    <div class="mapping-diagram">
                        <div class="mapping-row">
                            <div class="mapping-item"><strong>File Path:</strong><br>C:\\wamp64\\www\\index.php</div>
                            <div class="mapping-arrow">→</div>
                            <div class="mapping-item"><strong>URL:</strong><br>http://localhost/index.php</div>
                        </div>
                    </div>
                    <div class="lesson-text"><strong>Example 2: File in a Subfolder</strong></div>
                    <div class="mapping-diagram">
                        <div class="mapping-row">
                            <div class="mapping-item"><strong>File Path:</strong><br>C:\\wamp64\\www\\myapp\\about.php</div>
                            <div class="mapping-arrow">→</div>
                            <div class="mapping-item"><strong>URL:</strong><br>http://localhost/myapp/about.php</div>
                        </div>
                    </div>
                    <div class="lesson-text"><strong>Example 3: Nested Folders</strong></div>
                    <div class="mapping-diagram">
                        <div class="mapping-row">
                            <div class="mapping-item"><strong>File Path:</strong><br>C:\\wamp64\\www\\admin\\users\\list.php</div>
                            <div class="mapping-arrow">→</div>
                            <div class="mapping-item"><strong>URL:</strong><br>http://localhost/admin/users/list.php</div>
                        </div>
                    </div>
                    <div class="info-box">
                        <strong>✅ Notice:</strong> The folder structure is preserved in the URL. If you have 3 nested folders, you get 3 levels in the URL.
                    </div>`,
                quiz: {
                    question: "If a file is located at C:\\wamp64\\www\\students\\roster.php, what is its URL?",
                    options: [
                        "http://localhost/C:/wamp64/www/students/roster.php",
                        "http://localhost/students/roster.php",
                        "http://www/students/roster.php",
                        "http://localhost:8080/students/roster.php"
                    ],
                    correct: 1
                }
            },
            {
                id: 3,
                title: "Reverse Engineering: URL to Path",
                lesson: `<div class="lesson-title">Going Backwards: From URL to File</div>
                    <div class="lesson-text">
                        Sometimes you need to reverse the process. You see a URL and need to find where the file actually lives on your computer.
                    </div>
                    <div class="lesson-text"><strong>The Reverse Golden Rule:</strong></div>
                    <div class="info-box">
                        <strong>Add C:\\wamp64\\www\\ to the beginning of the URL path, and convert / to \\</strong>
                    </div>
                    <div class="lesson-text"><strong>Example 1: Simple URL</strong></div>
                    <div class="mapping-diagram">
                        <div class="mapping-row">
                            <div class="mapping-item"><strong>URL:</strong><br>http://localhost/contact.php</div>
                            <div class="mapping-arrow">←</div>
                            <div class="mapping-item"><strong>File Path:</strong><br>C:\\wamp64\\www\\contact.php</div>
                        </div>
                    </div>
                    <div class="lesson-text"><strong>Example 2: URL with Folder Structure</strong></div>
                    <div class="mapping-diagram">
                        <div class="mapping-row">
                            <div class="mapping-item"><strong>URL:</strong><br>http://localhost/api/v1/users.php</div>
                            <div class="mapping-arrow">←</div>
                            <div class="mapping-item"><strong>File Path:</strong><br>C:\\wamp64\\www\\api\\v1\\users.php</div>
                        </div>
                    </div>
                    <div class="info-box">
                        <strong>💡 Pro Tip:</strong> When debugging, always translate URLs to file paths to verify the file actually exists!
                    </div>`,
                quiz: {
                    question: "If you see the URL http://localhost/quiz/questions/math.php in your browser, where should that file be located?",
                    options: [
                        "C:\\quiz\\questions\\math.php",
                        "C:\\wamp64\\www\\quiz\\questions\\math.php",
                        "C:\\www\\quiz\\questions\\math.php",
                        "C:\\wamp64\\quiz\\questions\\math.php"
                    ],
                    correct: 1
                }
            },
            {
                id: 4,
                title: "CodeIgniter & Dynamic Routing",
                lesson: `<div class="lesson-title">Beyond Direct File Paths: CodeIgniter Routing</div>
                    <div class="lesson-text">
                        Here's where things get interesting! When you use <span class="highlight">CodeIgniter</span>, 
                        URLs don't always match file paths directly. Instead, CodeIgniter uses <strong>routing</strong> to map URLs to controllers and methods.
                    </div>
                    <div class="lesson-text"><strong>CodeIgniter Project Structure:</strong></div>
                    <div class="file-tree">
<span class="file-tree-folder">📁 C:\\wamp64\\www\\cmc_ira</span> ← Your CodeIgniter project
  <span class="file-tree-folder">📁 app</span>
    <span class="file-tree-folder">📁 Controllers</span>
      <span class="file-tree-file">📄 Quiz.php</span>
      <span class="file-tree-file">📄 Home.php</span>
    <span class="file-tree-folder">📁 Views</span>
      <span class="file-tree-file">📄 quiz_results.php</span>
  <span class="file-tree-folder">📁 public</span>
    <span class="file-tree-folder">📁 css</span>
      <span class="file-tree-file">📄 style.css</span>
    <span class="file-tree-folder">📁 js</span>
      <span class="file-tree-file">📄 confetti.js</span>
                    </div>
                    <div class="lesson-text"><strong>CodeIgniter URL Format:</strong></div>
                    <div class="url-path">http://localhost/cmc_ira/controller/method/parameter</div>
                    <div class="lesson-text"><strong>Real Examples:</strong></div>
                    <div class="comparison-grid">
                        <div class="comparison-col">
                            <div class="comparison-label">🌐 URL</div>
                            <code style="display: block; font-family: 'Courier New', monospace; font-size: 0.9rem;">http://localhost/cmc_ira/quiz/show/5</code>
                        </div>
                        <div class="comparison-col">
                            <div class="comparison-label">📂 Means</div>
                            <ul>
                                <li>Controller: Quiz</li>
                                <li>Method: show()</li>
                                <li>Param: 5</li>
                            </ul>
                        </div>
                    </div>
                    <div class="info-box">
                        <strong>⚠️ Important:</strong> In CodeIgniter, the URL structure is <strong>NOT</strong> a direct file path. 
                        It's a routing system that translates URLs to controller methods. <code style="background: white; padding: 0.2em 0.4em;">base_url()</code> 
                        automatically generates correct URLs for you!
                    </div>`,
                quiz: {
                    question: "In CodeIgniter, what does the URL http://localhost/cmc_ira/quiz/results/12 do?",
                    options: [
                        "Loads the file C:\\wamp64\\www\\cmc_ira\\quiz\\results\\12",
                        "Calls the results() method of the Quiz controller with parameter 12",
                        "Directly accesses a PHP file named 12",
                        "Loads results from the 12th row of the database"
                    ],
                    correct: 1
                }
            },
            {
                id: 5,
                title: "Practical Debugging",
                lesson: `<div class="lesson-title">Using Path-to-URL Knowledge to Debug</div>
                    <div class="lesson-text">
                        Now that you understand how file paths map to URLs, you can use this knowledge to solve common web problems!
                    </div>
                    <div class="lesson-text"><strong>Common Issue #1: 404 Not Found</strong></div>
                    <div class="info-box">
                        <strong>Problem:</strong> You get a "404 Not Found" error when visiting a URL.
                    </div>
                    <div class="lesson-text"><strong>How to Debug:</strong></div>
                    <div class="lesson-text">
                        <ul>
                            <li>Convert the URL to a file path (add C:\\wamp64\\www\\ and use backslashes)</li>
                            <li>Check if the file actually exists at that location</li>
                            <li>Verify folder names match exactly (case-sensitive on some servers)</li>
                            <li>Check the spelling of the filename in the URL</li>
                        </ul>
                    </div>
                    <div class="lesson-text"><strong>Example:</strong></div>
                    <div class="mapping-diagram">
                        <div class="mapping-row">
                            <div class="mapping-item"><strong>URL in browser:</strong><br>http://localhost/myapp/contact.php</div>
                        </div>
                        <div class="mapping-row">
                            <div class="mapping-item"><strong>Expected file path:</strong><br>C:\\wamp64\\www\\myapp\\contact.php</div>
                        </div>
                        <div class="mapping-row">
                            <div class="mapping-item"><strong>Debugging:</strong><br>Does this file exist? If not, check spelling or create it.</div>
                        </div>
                    </div>
                    <div class="lesson-text"><strong>Common Issue #2: Asset Links Broken (CSS, JS not loading)</strong></div>
                    <div class="info-box">
                        <strong>Use base_url():</strong> In CodeIgniter, always use <code style="background: white; padding: 0.2em 0.4em;">&lt;?= base_url('css/style.css') ?&gt;</code> 
                        instead of hardcoding paths!
                    </div>
                    <div class="lesson-text"><strong>Why?</strong> <code style="background: #f0f9ff; padding: 0.2em 0.4em;">base_url()</code> automatically generates the correct path whether you're on localhost or a live server.</div>`,
                quiz: {
                    question: "You're getting a 404 error for http://localhost/myapp/images/logo.png. What should you check first?",
                    options: [
                        "Restart Apache",
                        "Clear your browser cache",
                        "Verify the file exists at C:\\wamp64\\www\\myapp\\images\\logo.png",
                        "Check if PHP is enabled"
                    ],
                    correct: 2
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
