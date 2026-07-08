<!DOCTYPE html>
<html>

<head>
    <title>CSS Colors and Backgrounds</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }

        .example {
            background-color: #f9f9f9;
            border-left: 6px solid #2196F3;
            padding: 10px 20px;
            margin: 20px 0;
        }

        .code {
            background-color: #eee;
            padding: 10px;
            border-radius: 6px;
            font-family: Consolas, monospace;
        }

        .tryit {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 6px;
        }

        #demo {
            padding: 20px;
            text-align: center;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <h1>CSS Colors and Backgrounds</h1>
    <p>CSS allows you to set the colors and background styles of HTML elements using properties like <code>color</code>, <code>background-color</code>, <code>background-image</code>, and more.</p>

    <hr>

    <h2>🎨 Text Color</h2>
    <p>The <code>color</code> property sets the color of text. You can use color names, HEX, RGB, or HSL values.</p>

    <div class="example">
        <div class="code">
            p { color: blue; } <br>
            h1 { color: #ff6600; } <br>
            h2 { color: rgb(0, 128, 0); }
        </div>
    </div>

    <button class="tryit" onclick="tryColor()">Try It Yourself</button>

    <div id="demo">
        <h1>Heading Example</h1>
        <p>This is a paragraph with color applied.</p>
    </div>

    <script>
        function tryColor() {
            document.getElementById("demo").innerHTML = `
    <h1 style="color: #ff6600;">Heading Example</h1>
    <p style="color: blue;">This is a paragraph with blue text.</p>
    <p style="color: rgb(0,128,0);">This is another paragraph with green text.</p>
  `;
        }
    </script>

    <hr>

    <h2>🌈 Background Color</h2>
    <p>The <code>background-color</code> property defines the background color of an element.</p>

    <div class="example">
        <div class="code">
            div { background-color: lightblue; } <br>
            p { background-color: yellow; }
        </div>
    </div>

    <button class="tryit" onclick="tryBackground()">Try It Yourself</button>

    <div id="demo2">
        <p>This paragraph will change its background color.</p>
    </div>

    <script>
        function tryBackground() {
            document.getElementById("demo2").innerHTML = `
    <p style="background-color: lightblue;">This paragraph now has a light blue background!</p>
  `;
        }
    </script>

    <hr>

    <h2>🖼️ Background Image</h2>
    <p>The <code>background-image</code> property allows you to set an image as the background of an element.</p>

    <div class="example">
        <div class="code">
            div { <br>
            &nbsp;&nbsp;background-image: url('https://www.w3schools.com/css/img_tree.png'); <br>
            &nbsp;&nbsp;background-repeat: no-repeat; <br>
            &nbsp;&nbsp;background-size: cover; <br>
            }
        </div>
    </div>

    <button class="tryit" onclick="tryImage()">Try It Yourself</button>

    <div id="demo3">
        <p>Click the button to apply a background image.</p>
    </div>

    <script>
        function tryImage() {
            document.getElementById("demo3").style.backgroundImage = "url('https://www.w3schools.com/css/img_tree.png')";
            document.getElementById("demo3").style.backgroundRepeat = "no-repeat";
            document.getElementById("demo3").style.backgroundSize = "contain";
            document.getElementById("demo3").style.padding = "60px";
        }
    </script>

    <hr>

    <h2>✨ Summary</h2>
    <ul>
        <li><code>color</code> – sets the text color.</li>
        <li><code>background-color</code> – sets the background color.</li>
        <li><code>background-image</code> – sets an image as background.</li>
        <li><code>background-repeat</code>, <code>background-size</code>, and <code>background-position</code> – control how the image is displayed.</li>
    </ul>

</body>

</html>