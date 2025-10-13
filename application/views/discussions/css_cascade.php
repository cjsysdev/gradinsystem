<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CSS Cascading and Selector Priority</title>
  <style>
    body {
      font-family: "Segoe UI", Arial, sans-serif;
      margin: 0;
      background-color: #f6f6f6;
      color: #333;
    }

    header {
      background-color: #04AA6D;
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
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 {
      border-left: 6px solid #04AA6D;
      padding-left: 10px;
      color: #333;
    }

    pre {
      background-color: #f1f1f1;
      border-left: 4px solid #04AA6D;
      padding: 10px;
      overflow-x: auto;
    }

    table {
      border-collapse: collapse;
      width: 100%;
      margin-bottom: 20px;
    }

    table, th, td {
      border: 1px solid #ddd;
    }

    th, td {
      text-align: left;
      padding: 8px;
    }

    th {
      background-color: #04AA6D;
      color: white;
    }

    code {
      background: #eee;
      padding: 2px 4px;
      border-radius: 3px;
      font-family: Consolas, monospace;
      color: #d63384;
    }

    ul {
      line-height: 1.6;
    }

    strong {
      color: #04AA6D;
    }

    .references {
      background: #f1f1f1;
      border-left: 5px solid #04AA6D;
      padding: 15px;
      margin-top: 30px;
    }

    .references a {
      color: #04AA6D;
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

    .activity-link {
      display: inline-block;
      background-color: #04AA6D;
      color: white;
      text-decoration: none;
      padding: 10px 15px;
      border-radius: 5px;
      margin-top: 20px;
    }

    .activity-link:hover {
      background-color: #028a57;
    }
  </style>
</head>
<body>

<header>
  <h1>CSS Cascading and Selector Priority</h1>
  <p>Learn how CSS decides which styles are applied when rules conflict</p>
</header>

<div class="content">
  <h2>What is CSS Cascading?</h2>
  <p>
    When multiple CSS rules target the same HTML element, the <strong>cascade</strong> determines which style is applied.
  </p>

  <h3>The Cascade Order</h3>
  <table>
    <tr><th>Priority</th><th>Factor</th><th>Description</th></tr>
    <tr><td>1</td><td><strong>Importance</strong></td><td>Rules with <code>!important</code> override all others.</td></tr>
    <tr><td>2</td><td><strong>Specificity</strong></td><td>More specific selectors win over general ones.</td></tr>
    <tr><td>3</td><td><strong>Source Order</strong></td><td>If specificity is the same, the rule appearing <em>later</em> wins.</td></tr>
  </table>

  <h3>Specificity Calculation</h3>
  <table>
    <tr><th>Selector Type</th><th>Example</th><th>Specificity Value</th></tr>
    <tr><td>Inline styles</td><td><code>&lt;h1 style="color:red"&gt;</code></td><td><strong>1000</strong></td></tr>
    <tr><td>ID selector</td><td><code>#title { }</code></td><td><strong>100</strong></td></tr>
    <tr><td>Class, attribute, pseudo-class</td><td><code>.main</code>, <code>[type="text"]</code>, <code>:hover</code></td><td><strong>10</strong></td></tr>
    <tr><td>Element or pseudo-element</td><td><code>p</code>, <code>div</code>, <code>::before</code></td><td><strong>1</strong></td></tr>
  </table>

  <p><strong>Example:</strong></p>
  <pre><code>#main p.highlight {
  color: blue;
}</code></pre>
  <p><strong>Specificity:</strong> 100 (ID) + 10 (class) + 1 (element) = 111</p>

  <h3>Source Order Example</h3>
  <pre><code>p {
  color: red;
}
p {
  color: blue;
}</code></pre>
  <p><strong>Result:</strong> Text appears <span style="color:blue;">blue</span> because the last rule wins.</p>

  <!-- <p><strong>Next:</strong> Try the <a href="css-cascading-activity.html" class="activity-link">Interactive Activity →</a></p> -->

  <div class="references">
    <h2>References</h2>
    <ul>
      <li><a href="https://www.w3schools.com/css/css_howto.asp" target="_blank">W3Schools: CSS How To</a></li>
      <li><a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Cascade" target="_blank">MDN Web Docs: CSS Cascade</a></li>
      <li><a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Specificity" target="_blank">MDN Web Docs: CSS Specificity</a></li>
      <li><a href="https://www.freecodecamp.org/news/css-specificity-ovens-and-cascades-d38f56ef8aad/" target="_blank">FreeCodeCamp: Understanding CSS Specificity and Cascade</a></li>
    </ul>
  </div>
</div>

<div class="footer">
  © 2025 CSS Learning Module | Styled like W3Schools
</div>

</body>
</html>
