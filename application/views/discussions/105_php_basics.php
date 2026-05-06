<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Basics - Interactive Learning</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #4a90e2;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --light: #f5f5f5;
            --dark: #2c3e50;
            --border: #ddd;
            --bg: #ffffff;
        }
        
        html, body {
            width: 100%;
            height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #f8f5ff 100%);
            overflow: hidden;
        }
        
        /* Container */
        .container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100%;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, var(--primary) 0%, #357abd 100%);
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
        
        /* Stats Bar */
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
        
        /* Progress Bar */
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
        
        /* Main Content Area */
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
        
        /* Section Container */
        .section-container {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        
        /* Lesson Content */
        .lesson-section {
            background: white;
            padding: 1.5rem;
            animation: slideIn 0.4s ease;
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
        
        .highlight {
            background: #fff3cd;
            padding: 0.2em 0.4em;
            border-radius: 3px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .code-block {
            background: #f4f4f4;
            border: 1px solid #e0e0e0;
            border-left: 4px solid var(--primary);
            padding: 1rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            line-height: 1.6;
            color: var(--dark);
            overflow-x: auto;
            margin: 1rem 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin: 1rem 0;
        }
        
        .comparison-col {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 8px;
            border-top: 3px solid var(--primary);
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
            content: "•";
            color: var(--primary);
            font-weight: bold;
            margin-right: 0.5rem;
        }
        
        /* Quiz Section */
        .quiz-section {
            background: white;
            padding: 1.5rem;
            border-top: 1px solid #f0f0f0;
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
            background: #f0f4ff;
        }
        
        .option.selected {
            background: #f0f4ff;
            border-color: var(--primary);
            color: var(--primary);
            font-weight: 700;
        }
        
        .option.correct {
            background: #d4edda;
            border-color: var(--success);
            color: #155724;
            font-weight: 700;
        }
        
        .option.incorrect {
            background: #f8d7da;
            border-color: var(--danger);
            color: #721c24;
            font-weight: 700;
        }
        
        .option.disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        /* Feedback */
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
            background: #d4edda;
            border-color: var(--success);
            color: #155724;
        }
        
        .feedback.incorrect {
            background: #f8d7da;
            border-color: var(--danger);
            color: #721c24;
        }
        
        /* Streak Popup */
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
        
        /* Congratulations Modal */
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
            background: linear-gradient(135deg, var(--primary) 0%, #357abd 100%);
            padding: 1.5rem 1rem;
            border-radius: 12px;
            color: white;
        }
        
        .stat-card.success {
            background: linear-gradient(135deg, var(--success) 0%, #27ae60 100%);
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
            background: linear-gradient(135deg, var(--primary), #357abd);
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
        
        /* Button Section */
        .button-section {
            background: linear-gradient(135deg, #f0f4ff 0%, #f8f5ff 100%);
            padding: 1.25rem 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.5);
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: center;
            flex-shrink: 0;
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
            background: linear-gradient(135deg, var(--primary), #357abd);
            color: white;
            flex: 1;
            max-width: 100%;
            box-shadow: 0 2px 8px rgba(74, 144, 226, 0.3);
        }
        
        .btn-submit:active:not(:disabled) {
            transform: scale(0.98);
            box-shadow: 0 1px 4px rgba(74, 144, 226, 0.2);
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
        
        /* Scrollbar */
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
        
        /* Tablet */
        @media (min-width: 768px) {
            .content-scroll {
                padding: 2rem;
                max-width: 650px;
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
        }
        
        /* Desktop */
        @media (min-width: 1024px) {
            .content-scroll {
                padding: 2.5rem;
                max-width: 700px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="header-title">PHP Basics</div>
                <button class="header-close" onclick="confirmExit()">✕</button>
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
                    <div class="stat-value" id="progress">1/10</div>
                </div>
            </div>
            
            <div class="progress-section">
                <div class="progress-fill" id="progressFill"></div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <div class="content-scroll">
                <div class="section-container">
                    <!-- Lesson -->
                    <div class="lesson-section" id="lessonSection"></div>
                    
                    <!-- Quiz -->
                    <div class="quiz-section">
                        <div class="quiz-label">Question</div>
                        <div class="question-text" id="questionText"></div>
                        <div class="options" id="optionsContainer"></div>
                        <div class="feedback" id="feedback"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Button Section -->
        <div class="button-section">
            <button class="btn-back" id="backBtn" onclick="previousSection()">← Back</button>
            <button class="btn-submit" id="submitBtn" onclick="submitAnswer()">Submit</button>
        </div>
    </div>
    
    <!-- Modals -->
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
        <div class="congrats-text">You completed all 10 sections!</div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-label">Final Score</div>
                <div class="stat-card-value" id="finalScore">100</div>
            </div>
            <div class="stat-card success">
                <div class="stat-card-label">Best Streak</div>
                <div class="stat-card-value" id="bestStreak">10</div>
            </div>
        </div>
        
        <button class="congrats-button" onclick="restartQuiz()">Start Over</button>
    </div>
    
    <script>
        // Data
        const sections = [
            {
                id: 0,
                title: "Introduction: PHP vs C",
                lesson: `<div class="lesson-title">What is PHP?</div>
                    <div class="lesson-text">
                        PHP is a <span class="highlight">server-side scripting language</span> for web development. 
                        Unlike C, which runs on your machine, PHP code runs on a web server and sends HTML to your browser.
                    </div>
                    <div class="lesson-text"><strong>Key Comparison:</strong></div>
                    <div class="comparison">
                        <div class="comparison-col">
                            <div class="comparison-label">C</div>
                            <ul>
                                <li>Compiled</li>
                                <li>Manual memory</li>
                                <li>Local execution</li>
                                <li>Static types</li>
                            </ul>
                        </div>
                        <div class="comparison-col">
                            <div class="comparison-label">PHP</div>
                            <ul>
                                <li>Interpreted</li>
                                <li>Auto memory</li>
                                <li>Server execution</li>
                                <li>Dynamic types</li>
                            </ul>
                        </div>
                    </div>`,
                quiz: {
                    question: "Which statement is TRUE about PHP?",
                    options: [
                        "PHP runs on your local machine",
                        "PHP executes on the web server",
                        "PHP requires manual memory management",
                        "PHP must be compiled before running"
                    ],
                    correct: 1
                }
            },
            {
                id: 1,
                title: "Variables & $ Symbol",
                lesson: `<div class="lesson-title">Variables in PHP</div>
                    <div class="lesson-text">All PHP variables start with the <span class="highlight">$</span> symbol. You don't need to declare types—PHP handles types automatically.</div>
                    <div class="code-block">$name = "Alice";
$age = 25;
$score = 95.5;
$active = true;</div>
                    <div class="lesson-text">This is different from C where you declare types upfront:</div>
                    <div class="code-block">char name[] = "Alice";
int age = 25;
float score = 95.5;</div>`,
                quiz: {
                    question: "Which is the correct PHP variable syntax?",
                    options: [
                        "name = 'John';",
                        "$name = 'John';",
                        "var $name = 'John';",
                        "#name = 'John';"
                    ],
                    correct: 1
                }
            },
            {
                id: 2,
                title: "Data Types",
                lesson: `<div class="lesson-title">Basic Data Types</div>
                    <div class="lesson-text">PHP supports multiple data types that are automatically determined:</div>
                    <div class="code-block">$int = 42;           // Integer
$float = 3.14;       // Float
$string = "Hello";   // String
$bool = true;        // Boolean
$array = [1, 2, 3];  // Array
$null = null;        // Null</div>
                    <div class="lesson-text">Use <span class="highlight">var_dump()</span> to check the type and value of any variable.</div>`,
                quiz: {
                    question: "What will var_dump($x) show if $x = '42';?",
                    options: [
                        "int(42)",
                        "string(2) \"42\"",
                        "float(42)",
                        "bool(true)"
                    ],
                    correct: 1
                }
            },
            {
                id: 3,
                title: "String Interpolation",
                lesson: `<div class="lesson-title">Embed Variables in Strings</div>
                    <div class="lesson-text">One of PHP's best features: embed variables directly in <span class="highlight">double-quoted strings</span>!</div>
                    <div class="code-block">$name = "Bob";
$age = 30;

echo "Hello, $name! You are $age years old.";
// Output: Hello, Bob! You are 30 years old.</div>
                    <div class="lesson-text">This is much simpler than C's printf(). <strong>Important:</strong> This only works with double quotes, NOT single quotes:</div>
                    <div class="code-block">echo "Name: $name";   // Shows: Name: Bob
echo 'Name: $name';   // Shows: Name: $name</div>`,
                quiz: {
                    question: "What is the output of: echo \"Age: $age\"; where $age = 25;?",
                    options: [
                        "Age: $age",
                        "Age: 25",
                        "$age",
                        "Error"
                    ],
                    correct: 1
                }
            },
            {
                id: 4,
                title: "Control Structures: If/Else",
                lesson: `<div class="lesson-title">If, Else If, Else</div>
                    <div class="lesson-text">PHP if/else syntax is nearly identical to C. You already know this!</div>
                    <div class="code-block">$score = 85;

if ($score >= 90) {
    echo "A Grade";
} else if ($score >= 80) {
    echo "B Grade";
} else {
    echo "C Grade";
}</div>
                    <div class="lesson-text">You can also use the ternary operator just like in C:</div>
                    <div class="code-block">$status = ($age >= 18) ? "Adult" : "Minor";</div>`,
                quiz: {
                    question: "What will this code output if $x = 10?",
                    code: `if ($x > 5) {
    echo "Big";
} else {
    echo "Small";
}`,
                    options: [
                        "Small",
                        "Big",
                        "10",
                        "Syntax Error"
                    ],
                    correct: 1
                }
            },
            {
                id: 5,
                title: "Loops: For & While",
                lesson: `<div class="lesson-title">Loops in PHP</div>
                    <div class="lesson-text">For and while loops work exactly like in C. Plus, PHP has a bonus: <span class="highlight">foreach</span>!</div>
                    <div class="code-block">// C-style for loop
for ($i = 0; $i < 5; $i++) {
    echo "$i ";  // Output: 0 1 2 3 4
}

// While loop
$count = 0;
while ($count < 3) {
    echo "Count: $count";
    $count++;
}</div>
                    <div class="lesson-text">The <span class="highlight">foreach</span> loop is perfect for iterating through arrays:</div>
                    <div class="code-block">$fruits = ["Apple", "Banana", "Orange"];
foreach ($fruits as $fruit) {
    echo "$fruit ";  // Output: Apple Banana Orange
}</div>`,
                quiz: {
                    question: "What does this code output?",
                    code: `$arr = [10, 20, 30];
foreach ($arr as $val) {
    echo $val . " ";
}`,
                    options: [
                        "10 20 30",
                        "1 2 3",
                        "Array",
                        "Error"
                    ],
                    correct: 0
                }
            },
            {
                id: 6,
                title: "Arrays: Indexed & Associative",
                lesson: `<div class="lesson-title">Arrays in PHP</div>
                    <div class="lesson-text">PHP arrays are more powerful than C arrays. You can use numbers (indexed) OR strings (associative) as keys.</div>
                    <div class="code-block">// Indexed array (like C)
$colors = ["Red", "Green", "Blue"];
echo $colors[0];  // Red

// Associative array (like a struct)
$student = [
    "name" => "John",
    "age" => 20,
    "gpa" => 3.8
];
echo $student["name"];  // John</div>
                    <div class="lesson-text">Add elements dynamically:</div>
                    <div class="code-block">$colors[] = "Yellow";  // Adds to end
$student["major"] = "CS";  // Adds key-value pair</div>`,
                quiz: {
                    question: "How do you access the value 'John'?",
                    code: `$student = [
    'name' => 'John',
    'age' => 20
];`,
                    options: [
                        "$student[0]",
                        "$student['name']",
                        "$student->name",
                        "$student.name"
                    ],
                    correct: 1
                }
            },
            {
                id: 7,
                title: "Functions",
                lesson: `<div class="lesson-title">Defining & Calling Functions</div>
                    <div class="lesson-text">Function syntax in PHP is similar to C, but without type declarations:</div>
                    <div class="code-block">function add($a, $b) {
    return $a + $b;
}

$result = add(5, 3);  // $result = 8</div>
                    <div class="lesson-text">PHP allows <span class="highlight">default parameters</span>—much easier than C:</div>
                    <div class="code-block">function greet($name, $greeting = "Hello") {
    echo "$greeting, $name!";
}

greet("Alice");         // Hello, Alice!
greet("Bob", "Hi");     // Hi, Bob!</div>`,
                quiz: {
                    question: "What will this output?",
                    code: `function greet($name, $msg = 'Welcome') {
    echo "$msg, $name";
}
greet('Eve');`,
                    options: [
                        "Eve",
                        "Welcome, Eve",
                        "Hello, Eve",
                        "Error"
                    ],
                    correct: 1
                }
            },
            {
                id: 8,
                title: "Operators & Comparisons",
                lesson: `<div class="lesson-title">Operators in PHP</div>
                    <div class="lesson-text">Most operators are identical to C:</div>
                    <div class="code-block">// Arithmetic
$sum = 10 + 5;      // 15
$diff = 10 - 5;     // 5
$prod = 10 * 5;     // 50

// Comparison
10 == 5;    // false
10 != 5;    // true
10 > 5;     // true

// Logical
true && false;  // false
true || false;  // true
!true;          // false</div>
                    <div class="lesson-text">PHP also has <span class="highlight">string concatenation</span> with the <strong>.</strong> operator:</div>
                    <div class="code-block">$first = "Hello";
$second = "World";
echo $first . " " . $second;  // Hello World</div>`,
                quiz: {
                    question: "What is the output?",
                    code: `$x = 'Hello' . ' ' . 'PHP';
echo $x;`,
                    options: [
                        "HelloPHP",
                        "Hello PHP",
                        "Error",
                        "undefined"
                    ],
                    correct: 1
                }
            },
            {
                id: 9,
                title: "Web Basics: PHP & HTML",
                lesson: `<div class="lesson-title">PHP in Web Pages</div>
                    <div class="lesson-text">PHP runs on the <span class="highlight">server</span>, not the browser. The server executes your PHP code and sends HTML to users.</div>
                    <div class="code-block">&lt;?php
$name = "John";
echo "&lt;h1&gt;Welcome, $name!&lt;/h1&gt;";
?&gt;</div>
                    <div class="lesson-text">The user's browser never sees the PHP code—only the HTML output:</div>
                    <div class="code-block">&lt;h1&gt;Welcome, John!&lt;/h1&gt;</div>
                    <div class="lesson-text">This separation is powerful: you can generate dynamic HTML while keeping your code secret!</div>`,
                quiz: {
                    question: "What does the browser see when it requests a .php file?",
                    options: [
                        "The raw PHP code",
                        "The HTML output generated by PHP",
                        "The server file path",
                        "Nothing until you refresh"
                    ],
                    correct: 1
                }
            }
        ];
        
        let currentSection = 0;
        let score = 0;
        let streak = 0;
        let selectedOption = null;
        let answered = false;
        let currentShuffledOptions = [];
        let currentCorrectIndex = 0;
        
        // Fisher-Yates shuffle algorithm
        function shuffleArray(array) {
            const shuffled = [...array];
            for (let i = shuffled.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
            }
            return shuffled;
        }
        
        // Create option mapping for correct answer tracking
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
        
        // Initialize
        function init() {
            renderContent();
        }
        
        // Render content
        function renderContent() {
            const section = sections[currentSection];
            
            // Shuffle options and get new correct index
            const shuffled = createShuffledOptions(section.quiz.options, section.quiz.correct);
            currentShuffledOptions = shuffled.options;
            currentCorrectIndex = shuffled.correctIndex;
            
            // Lesson
            document.getElementById('lessonSection').innerHTML = section.lesson;
            
            // Question
            let questionHTML = `<p>${section.quiz.question}</p>`;
            if (section.quiz.code) {
                questionHTML += `<div class="question-code">${section.quiz.code}</div>`;
            }
            document.getElementById('questionText').innerHTML = questionHTML;
            renderOptions();
            
            // Reset feedback
            const feedback = document.getElementById('feedback');
            feedback.className = 'feedback';
            feedback.textContent = '';
            
            answered = false;
            selectedOption = null;
            updateUI();
        }
        
        // Render options
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
        
        // Select option
        function selectOption(index) {
            if (answered) return;
            
            document.querySelectorAll('.option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            document.querySelector(`[data-index="${index}"]`).classList.add('selected');
            selectedOption = index;
        }
        
        // Submit answer
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
            
            // Disable all options
            options.forEach(opt => opt.classList.add('disabled'));
            answered = true;
            
            // Show result
            if (correct) {
                document.querySelector(`[data-index="${selectedOption}"]`).classList.add('correct');
                feedback.className = 'feedback show correct';
                feedback.textContent = '✓ Correct! +2 points';
                score += 2;
                streak++;
                
                // Show streak popup every 3
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
        
        // Show streak popup
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
        
        // Navigation
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
            document.getElementById('bestStreak').textContent = streak;
            
            modal.classList.add('show');
            backdrop.classList.add('show');
        }
        
        function restartQuiz() {
            currentSection = 0;
            score = 0;
            streak = 0;
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
        
        // Update UI
        function updateUI() {
            document.getElementById('score').textContent = score;
            document.getElementById('streak').textContent = streak;
            document.getElementById('progress').textContent = (currentSection + 1) + '/10';
            
            // Update progress bar
            const progress = ((currentSection + 1) / sections.length) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
            
            // Update buttons
            document.getElementById('backBtn').disabled = currentSection === 0;
            
            if (answered) {
                document.getElementById('submitBtn').textContent = currentSection === sections.length - 1 ? 'Finish' : 'Next →';
            } else {
                document.getElementById('submitBtn').textContent = 'Submit';
            }
        }
        
        // Confirm exit
        function confirmExit() {
            if (score > 0) {
                if (confirm('Are you sure? Your progress will be lost.')) {
                    window.close();
                }
            } else {
                window.close();
            }
        }
        
        // Initialize on load
        window.addEventListener('load', init);
    </script>
</body>
</html>