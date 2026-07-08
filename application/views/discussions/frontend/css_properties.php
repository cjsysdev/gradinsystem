<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CSS Essential Properties Discussion</title>

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
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: #f5f5f5;
    }

    header {
      background-color: #04AA6D;
      color: white;
      padding: 16px;
      text-align: center;
    }

    .content {
      max-width: 900px;
      margin: 30px auto;
      background: white;
      padding: 20px 40px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
      color: #04AA6D;
      margin-top: 30px;
    }

    .example {
      background-color: #f1f1f1;
      border-left: 5px solid #04AA6D;
      padding: 15px;
      margin: 15px 0;
    }

    .tryit {
      background-color: #04AA6D;
      color: white;
      border: none;
      padding: 8px 16px;
      cursor: pointer;
      border-radius: 5px;
    }

    .tryit:hover {
      background-color: #028a57;
    }

    pre {
      background: #f1f1f1;
      padding: 10px;
      border-radius: 5px;
      overflow-x: auto;
    }
  </style>
</head>

<body>

  <header>
    <h1>CSS Essential Properties</h1>
    <p>Learn the most common and useful CSS properties that make your web pages beautiful and structured!</p>
  </header>

  <div class="content">

    <h2>1. color and background-color</h2>
    <p>The <code>color</code> property sets the text color, while <code>background-color</code> defines the background color of an element.</p>

    <div class="example">
      <pre> <code class="language-css">
p {
  color: white;
  background-color: teal;
}
    </pre> </code>
    </div>

    <button class="tryit" onclick="alert('This would color your text white and set a teal background!')">Try It Yourself »</button>

    <h2>2. font-family and font-size</h2>
    <p>These properties define the typeface and the size of the text. It’s best to include backup fonts for compatibility.</p>

    <div class="example">
      <pre> <code class="language-css">
p {
  font-family: 'Arial', sans-serif;
  font-size: 18px;
}
    </pre> </code>
    </div>

    <h2>3. margin and padding</h2>
    <p><code>margin</code> controls the space outside an element’s border, while <code>padding</code> controls the space inside the border.</p>

    <div class="example">
      <pre> <code class="language-css">
div {
  margin: 20px;
  padding: 10px;
}
    </pre> </code>
    </div>

    <h2>4. border</h2>
    <p>The <code>border</code> property adds outlines around elements. You can set width, style, and color.</p>

    <div class="example">
      <pre> <code class="language-css">
div {
  border: 2px solid #04AA6D;
}
    </pre> </code>
    </div>

    <h2>5. width and height</h2>
    <p>These define the size of elements. Use percentages for responsiveness, or pixels for fixed dimensions.</p>

    <div class="example">
      <pre> <code class="language-css">
img {
  width: 100px;
  height: 100px;
}
    </pre> </code>
    </div>

    <h2>6. display and position</h2>
    <p><code>display</code> controls how elements are shown (block, inline, flex, etc.), and <code>position</code> determines their placement on the page.</p>

    <div class="example">
      <pre> <code class="language-css">
div {
  display: flex;
  position: relative;
}
    </pre> </code>
    </div>

    <h2>7. text-align</h2>
    <p>The <code>text-align</code> property specifies the horizontal alignment of text inside an element.</p>

    <div class="example">
      <pre> <code class="language-css">
h1 {
  text-align: center;
}
    </pre> </code>
    </div>

    <h2>8. background-image</h2>
    <p>Add an image as a background using <code>background-image</code>. Combine it with <code>background-size</code> and <code>background-repeat</code> for better control.</p>

    <div class="example">
      <pre> <code class="language-css">
body {
  background-image: url('background.jpg');
  background-size: cover;
  background-repeat: no-repeat;
}
    </pre> </code>
    </div>

    <h2>9. box-shadow and border-radius</h2>
    <p>Use these for subtle visual effects. <code>box-shadow</code> adds depth, and <code>border-radius</code> makes corners round.</p>

    <div class="example">
      <pre> <code class="language-css">
div {
  box-shadow: 0 4px 6px rgba(0,0,0,0.2);
  border-radius: 10px;
}
    </pre> </code>
    </div>

    <h2>10. hover (pseudo-class)</h2>
    <p>The <code>:hover</code> pseudo-class changes the style of an element when a user hovers over it.</p>

    <div class="example">
      <pre> <code class="language-css">
button:hover {
  background-color: orange;
}
    </pre> </code>
    </div>

    <h2>11. align-items and justify-content</h2>
    <p>These properties are used inside flex containers to control the alignment of items.
      <code>justify-content</code> aligns items horizontally, while <code>align-items</code> aligns them vertically.
    </p>

    <div class="example">
      <pre> <code class="language-css">
.container {
  display: flex;
  justify-content: center;  /* horizontal alignment */
  align-items: center;      /* vertical alignment */
  height: 200px;
  background-color: #f1f1f1;
}
    </pre> </code>
    </div>

    <p style="margin-top: 40px;">These properties form the foundation of CSS styling. Mastering them helps you design modern, responsive, and attractive web pages.</p>

  </div>

</body>

</html>