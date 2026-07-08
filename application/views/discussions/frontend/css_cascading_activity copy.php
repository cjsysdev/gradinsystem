<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>CSS Cascading and Selector Priority Interactive</title>

	<!-- Highlight.js CSS Theme -->
	<!-- Highlight.js CSS -->
	<link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
	<!-- Highlight.js JS -->
	<script src="<?= base_url("assets/highlights/11.7.0-highlight.min.js") ?> "></script>
	<script>
		hljs.highlightAll();
	</script>

	<style>
		body {
			font-family: "Segoe UI", Arial, sans-serif;
			background-color: #f6f6f6;
			margin: 0;
			color: #333;
		}

		header {
			background-color: #2196f3;
			color: white;
			padding: 20px;
			text-align: center;
		}

		.content {
			max-width: 900px;
			margin: 20px auto;
			background: white;
			padding: 20px 30px;
			border-radius: 8px;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
		}

		h2 {
			border-left: 6px solid #04aa6d;
			padding-left: 10px;
			color: #333;
		}

		pre {
			background-color: #f8f8f8;
			border-left: 4px solid #04aa6d;
			padding: 10px;
			overflow-x: auto;
		}

		.activity {
			background-color: #f9f9f9;
			border-left: 5px solid #2196f3;
			padding: 15px;
			margin: 20px 0;
		}

		button {
			background-color: #04aa6d;
			color: white;
			border: none;
			padding: 8px 16px;
			border-radius: 5px;
			cursor: pointer;
			margin-top: 10px;
		}

		button:hover {
			background-color: #03995e;
		}

		.result {
			background-color: #fff8e1;
			border-left: 4px solid #ff9800;
			padding: 10px 15px;
			margin-top: 10px;
			display: none;
		}

		.references {
			background: #f1f1f1;
			border-left: 5px solid #04aa6d;
			padding: 15px;
			margin-top: 30px;
		}

		.references a {
			color: #04aa6d;
			text-decoration: none;
		}

		.references a:hover {
			text-decoration: underline;
		}

		.footer {
			background-color: #333;
			color: white;
			text-align: center;
			padding: 15px;
			margin-top: 30px;
			font-size: 14px;
		}
	</style>
</head>

<body>
	<header>
		<h1>CSS Cascading and Selector Priority</h1>
		<p>Interactive Guessing Game – Which Style Wins?</p>
	</header>

	<div class="content">
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
	</div>

	<div class="footer">
		© 2025 CSS Learning Module | Interactive Cascading Activity
	</div>

	<script>
		function showAnswer(num) {
			const ans = document.getElementById("result" + num);
			ans.style.display = ans.style.display === "block" ? "none" : "block";
		}
	</script>
</body>

</html>