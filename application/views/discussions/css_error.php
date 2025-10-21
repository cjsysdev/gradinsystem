<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CSS Common Errors</title>

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
      line-height: 1.6;
      background-color: #f5f5f5;
      margin: 0;
      padding: 0;
      color: #333;
    }

    header {
      background-color: #0d6efd;
      color: #fff;
      padding: 20px;
      text-align: center;
    }

    main {
      max-width: 900px;
      margin: 30px auto;
      background: #fff;
      border-radius: 8px;
      padding: 20px 30px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    h1 {
      color: #fff;
      margin: 0;
    }

    h2,
    h3 {
      color: #0d6efd;
    }

    pre {
      background-color: #f1f1f1;
      border-left: 4px solid #04AA6D;
      padding: 10px;
      overflow-x: auto;
    }

    .tip {
      background-color: #e7f3fe;
      border-left: 6px solid #2196F3;
      padding: 10px 15px;
      margin: 10px 0;
    }

    .example {
      background-color: #f9f9f9;
      border: 1px solid #ddd;
      padding: 15px;
      border-radius: 6px;
      margin: 15px 0;
    }

    footer {
      text-align: center;
      font-size: 14px;
      color: #777;
      margin: 30px 0 10px;
    }

    a {
      color: #0d6efd;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>

  <header>
    <h1>CSS Common Errors</h1>
    <p>Learn about frequent mistakes developers make when writing CSS — and how to avoid them.</p>
  </header>

  <main>
    <h2>1. Missing Semicolons</h2>
    <p>Each CSS property declaration should end with a semicolon (<code>;</code>). Forgetting one can cause the following properties to fail.</p>

    <div class="example">
      <h3>Incorrect:</h3>
      <pre><code class="language-css">h1 {
  color: blue
  text-align: center;
}</code></pre>

      <h3>Correct:</h3>
      <pre><code class="language-css">h1 {
  color: blue;
  text-align: center;
}</code></pre>
    </div>

    <h2>2. Using the Wrong Property Name</h2>
    <p>CSS property names are case-sensitive and must be spelled correctly. Typos will cause them to be ignored.</p>

    <div class="example">
      <h3>Incorrect:</h3>
      <pre><code class="language-css">p {
  font-colour: red;
}</code></pre>

      <h3>Correct:</h3>
      <pre><code class="language-css">p {
  color: red;
}</code></pre>
    </div>

    <h2>3. Missing or Mismatched Braces</h2>
    <p>Forgetting a closing brace (<code>}</code>) breaks your stylesheet, as CSS will not know where a rule ends.</p>

    <div class="example">
      <h3>Incorrect:</h3>
      <pre><code class="language-css">div {
  background-color: yellow;
  color: black;
</code></pre>

      <h3>Correct:</h3>
      <pre><code class="language-css">div {
  background-color: yellow;
  color: black;
}</code></pre>
    </div>

    <h2>4. Wrong Selector Syntax</h2>
    <p>Make sure to use the correct symbols for selectors. A dot (<code>.</code>) is used for classes, and a hash (<code>#</code>) is used for IDs.</p>

    <div class="example">
      <h3>Incorrect:</h3>
      <pre><code class="language-css">p%intro {
  color: green;
}</code></pre>

      <h3>Correct:</h3>
      <pre><code class="language-css">#intro {
  color: green;
}</code></pre>
    </div>

    <h2>5. Forgetting Units</h2>
    <p>Numeric values for dimensions (like width, height, margin) need units such as <code>px</code>, <code>em</code>, or <code>%</code>.</p>

    <div class="example">
      <h3>Incorrect:</h3>
      <pre><code class="language-css">div {
  width: 100;
}</code></pre>

      <h3>Correct:</h3>
      <pre><code class="language-css">div {
  width: 100px;
}</code></pre>
    </div>

    <h2>6. Incorrect Comment Syntax</h2>
    <p>CSS comments must start with <code>/*</code> and end with <code>*/</code>. HTML-style comments (<code>&lt;!-- ... --&gt;</code>) will not work.</p>

    <div class="example">
      <h3>Incorrect:</h3>
      <pre><code class="language-css">/-- This is a comment --/
p { color: red; }</code></pre>

      <h3>Correct:</h3>
      <pre><code class="language-css">/* This is a comment */
p { color: red; }</code></pre>
    </div>

    <h2>7. Misusing Shorthand Properties</h2>
    <p>When using shorthand (like <code>margin</code> or <code>background</code>), ensure the correct order and values are provided.</p>

    <div class="example">
      <h3>Incorrect:</h3>
      <pre><code class="language-css">margin: 10px 5px 2px;</code></pre>

      <h3>Correct:</h3>
      <pre><code class="language-css">margin: 10px 5px 2px 5px;</code></pre>
    </div>

    <h2>8. Case Sensitivity Issues</h2>
    <p>CSS property names and values are case-sensitive in some cases. Stick to lowercase for consistency.</p>

    <div class="example">
      <h3>Incorrect:</h3>
      <pre><code class="language-css">BODY {
  Background-Color: Red;
}</code></pre>

      <h3>Correct:</h3>
      <pre><code class="language-css">body {
  background-color: red;
}</code></pre>
    </div>

    <h2>9. Incorrect Use of Quotes</h2>
    <p>When specifying font names or URLs that include spaces, use quotes properly.</p>

    <div class="example">
      <h3>Incorrect:</h3>
      <pre><code class="language-css">font-family: Times New Roman;</code></pre>

      <h3>Correct:</h3>
      <pre><code class="language-css">font-family: "Times New Roman", serif;</code></pre>
    </div>

    <h2>10. Using Inline CSS Excessively</h2>
    <p>Overusing inline CSS makes your code harder to maintain. Use external or internal stylesheets instead.</p>

    <div class="example">
      <h3>Example:</h3>
      <pre><code class="language-html">&lt;p style="color:blue; font-size:16px;"&gt;Hello&lt;/p&gt;</code></pre>

      <p>Better approach:</p>
      <pre><code class="language-css">p {
  color: blue;
  font-size: 16px;
}</code></pre>
    </div>

    <!-- <div class="tip">
      <strong>Tip:</strong> Always validate your CSS using the
      <a href="https://jigsaw.w3.org/css-validator/" target="_blank">W3C CSS Validator</a>
      to catch syntax errors early.
    </div> -->

    <footer>
      <p>Reference: <a href="https://www.w3schools.com/css/css_errors.asp" target="_blank">W3Schools CSS Errors</a></p>
    </footer>
  </main>
</body>

</html>