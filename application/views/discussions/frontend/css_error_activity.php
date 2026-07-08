<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Error Activity</title>
    <!-- Include your CSS styling here -->
</head>

<body>
    <!-- Styling for activity boxes -->
    <style>
        .activity {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .activity h3 {
            color: #0d6efd;
        }

        .result {
            display: none;
            margin-top: 8px;
            padding: 8px;
            background-color: #e9f7ef;
            border-radius: 6px;
        }

        button {
            margin-top: 8px;
            background-color: #0d6efd;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0b5ed7;
        }
    </style>
    <hr>
    <h2>Interactive Activity: Find the CSS Error</h2>
    <p>Each question below contains a CSS snippet with a mistake. Try to identify the error before clicking “Show Answer.”</p>

    <div class="activity">
        <h3>Activity 1 – Missing Semicolon</h3>
        <pre><code class="language-css">h1 {
  color: blue
  text-align: center;
}</code></pre>
        <button onclick="showAnswer(1)">Show Answer</button>
        <div id="result1" class="result">Answer: Missing semicolon (;) after <code>color: blue</code>.</div>
    </div>

    <div class="activity">
        <h3>Activity 2 – Wrong Property Name</h3>
        <pre><code class="language-css">p {
  font-colour: red;
}</code></pre>
        <button onclick="showAnswer(2)">Show Answer</button>
        <div id="result2" class="result">Answer: The correct property is <code>color</code>, not <code>font-colour</code>.</div>
    </div>

    <div class="activity">
        <h3>Activity 3 – Missing Brace</h3>
        <pre><code class="language-css">div {
  background-color: yellow;
  color: black;
</code></pre>
        <button onclick="showAnswer(3)">Show Answer</button>
        <div id="result3" class="result">Answer: Missing closing brace <code>}</code> at the end of the rule.</div>
    </div>

    <div class="activity">
        <h3>Activity 4 – Wrong Selector Symbol</h3>
        <pre><code class="language-css">p#intro {
  color: green;
}</code></pre>
        <button onclick="showAnswer(4)">Show Answer</button>
        <div id="result4" class="result">Answer: Use <code>#intro</code> directly (no tag name before ID).</div>
    </div>

    <div class="activity">
        <h3>Activity 5 – Missing Unit</h3>
        <pre><code class="language-css">div {
  width: 100;
}</code></pre>
        <button onclick="showAnswer(5)">Show Answer</button>
        <div id="result5" class="result">Answer: Missing unit; should be <code>width: 100px;</code>.</div>
    </div>

    <div class="activity">
        <h3>Activity 6 – Incorrect Comment Style</h3>
        <pre><code class="language-css"><!-- Comment -->
p { color: red; }</code></pre>
        <button onclick="showAnswer(6)">Show Answer</button>
        <div id="result6" class="result">Answer: CSS comments use <code>/* comment */</code>, not <code>&lt;!-- --&gt;</code>.</div>
    </div>

    <div class="activity">
        <h3>Activity 7 – Shorthand Property Error</h3>
        <pre><code class="language-css">margin: 10px 5px 2px;</code></pre>
        <button onclick="showAnswer(7)">Show Answer</button>
        <div id="result7" class="result">Answer: Shorthand for <code>margin</code> needs four values (top, right, bottom, left).</div>
    </div>

    <div class="activity">
        <h3>Activity 8 – Case Sensitivity Issue</h3>
        <pre><code class="language-css">BODY {
  Background-Color: Red;
}</code></pre>
        <button onclick="showAnswer(8)">Show Answer</button>
        <div id="result8" class="result">Answer: Use lowercase consistently: <code>body { background-color: red; }</code>.</div>
    </div>

    <div class="activity">
        <h3>Activity 9 – Missing Quotes in Font Name</h3>
        <pre><code class="language-css">p {
  font-family: Times New Roman;
}</code></pre>
        <button onclick="showAnswer(9)">Show Answer</button>
        <div id="result9" class="result">Answer: Use quotes around multi-word fonts: <code>"Times New Roman"</code>.</div>
    </div>

    <div class="activity">
        <h3>Activity 10 – Inline CSS Overuse</h3>
        <pre><code class="language-html">&lt;p style="color:blue; font-size:16px;"&gt;Hello&lt;/p&gt;</code></pre>
        <button onclick="showAnswer(10)">Show Answer</button>
        <div id="result10" class="result">Answer: Avoid inline CSS; use external or internal stylesheets for maintainability.</div>
    </div>

    <!-- JavaScript for Show Answer Buttons -->
    <script>
        function showAnswer(num) {
            const result = document.getElementById("result" + num);
            result.style.display = "block";
        }
    </script>
</body>

</html>