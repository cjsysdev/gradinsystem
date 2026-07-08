<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Introduction to JavaScript</title>
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css') ?>">

    <!-- Highlight.js CSS Theme -->
    <link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">

    <!-- Highlight.js JS -->
    <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
    <script>
        hljs.highlightAll();
    </script>
</head>

<body>

    <header>
        <h1>Introduction to JavaScript</h1>
        <p>Understand how JavaScript brings interaction and behavior to webpages.</p>
    </header>

    <div class="content">

        <h2>What is JavaScript?</h2>
        <p>
            Webpages are built using three main technologies:
        </p>

        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Language</th>
                    <th>Role</th>
                    <th>Example Use</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>HTML</td>
                    <td>Content / Structure</td>
                    <td>Text, images, paragraphs, headings</td>
                </tr>
                <tr>
                    <td>CSS</td>
                    <td>Design / Style</td>
                    <td>Colors, layout, fonts</td>
                </tr>
                <tr>
                    <td><b>JavaScript</b></td>
                    <td><b>Behavior / Interaction</b></td>
                    <td><b>Responding to user actions</b></td>
                </tr>
            </tbody>
        </table>

        <p>
            Without JavaScript, a webpage is like a <b>poster</b> — it may look nice, but it does <b>nothing</b> when you interact with it.
        </p>

        <h2>C vs JavaScript vs PHP</h2>

        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Feature</th>
                    <th>C</th>
                    <th>JavaScript</th>
                    <th>PHP</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Type of Language</td>
                    <td>General-purpose compiled language</td>
                    <td>Client-side scripting language</td>
                    <td>Server-side scripting language</td>
                </tr>
                <tr>
                    <td>Where it Runs</td>
                    <td>Computer (compiled executable)</td>
                    <td>Browser (Chrome, Firefox, etc.)</td>
                    <td>Web Server (XAMPP, WAMP, etc.)</td>
                </tr>
                <tr>
                    <td>Main Usage</td>
                    <td>System/low-level programming</td>
                    <td>Interactivity and dynamic webpages</td>
                    <td>Processing data, backend logic, database interaction</td>
                </tr>
                <tr>
                    <td>Variable Declaration</td>
                    <td>Strict typing</td>
                    <td>Flexible typing</td>
                    <td>Flexible typing</td>
                </tr>
                <tr>
                    <td>Output</td>
                    <td><code>printf()</code> to console</td>
                    <td><code>document.write()</code> or webpage elements</td>
                    <td><code>echo</code> to webpage</td>
                </tr>
            </tbody>
        </table>

        <h2>Declaring Variables</h2>
        <p>In JavaScript, we use <code>let</code> to declare variables.</p>

        <!-- <h3>C Version</h3> -->
        <pre><code class="language-c">int age = 20;
printf("%d", age);</code></pre>

        <!-- <h3>PHP Version</h3> -->
        <pre><code class="language-php">$age = 20;
echo $age;</code></pre>

        <!-- <h3>JavaScript Version</h3> -->
        <pre><code class="language-javascript">let age = 20;
document.write(age);</code></pre>

        <p><strong>Key Difference:</strong> JavaScript does not need data types like <code>int</code>, <code>char</code>, or <code>float</code>. It automatically detects the type based on the value.</p>

        <h2>Displaying Output</h2>
        <p>JavaScript displays output directly on the webpage using <code>document.write()</code>.</p>

        <pre><code class="language-html">&lt;script&gt;
let name = "Juan";
document.write("Hello " + name);
&lt;/script&gt;</code></pre>

        <p>This prints directly inside the webpage.</p>

        <h3>Example:</h3>

        <pre><code class="language-html">&lt;!-- index.html --&gt;
&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
  &lt;title&gt;JS Intro&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;

  &lt;h1 id="title"&gt;Hello World&lt;/h1&gt;

  &lt;script src="app.js"&gt;&lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;
</code></pre>

        <pre><code class="language-javascript">// app.js
console.log("The JS file is connected!");
</code></pre>

        <p>Reload your webpage and check the console to verify that it works.</p>

        <h2>The DOM (Document Object Model)</h2>
        <p>
            The browser turns HTML elements into objects that JavaScript can access and modify.
            The most important command for now is:
        </p>

        <pre><code class="language-javascript">document.getElementById("title").innerHTML = "Welcome!";
</code></pre>

        <p>This locates an element by its <code>id</code> and changes its text.</p>

        <pre><code class="language-javascript">document.getElementById("title").style.color = "blue";
</code></pre>

        <p>This changes the color of the same element.</p>

        <h2>Basic Interaction</h2>
        <p>We use <code>onclick</code> directly in HTML to trigger changes.</p>

        <h3>Example:</h3>
        <pre><code class="language-html">&lt;h1 id="message"&gt;Click the button below&lt;/h1&gt;

&lt;button onclick="document.getElementById('message').innerHTML = 'Button Clicked!'"&gt;
  Change Text
&lt;/button&gt;

&lt;button onclick="document.getElementById('message').style.color = 'red'"&gt;
  Change Color
&lt;/button&gt;
</code></pre>

        <h2>Hands-On Activity</h2>
        <ul>
            <li>Add a new button that changes the background color.</li>
            <li>Add a Button That Changes the Text of a Paragraph.</li>
            <li>Create an Input Field and Button That Displays User Input on the Page.</li>
            <li>Add a new button that changes the text.</li>
            <li>Add a new button that displays an alert with a message.</li>
        </ul>
        <!-- <pre><code class="language-html">&lt;button onclick="document.body.style.background = 'lightyellow'"&gt;
  Change Background
&lt;/button&gt;
</code></pre> -->

        <h2>Summary</h2>
        <ul>
            <li>JavaScript makes webpages interactive.</li>
            <li><code>console.log()</code> is used to test output in the console.</li>
            <li><code>document.getElementById()</code> selects elements on the page.</li>
            <li><code>.innerHTML</code> and <code>.style</code> allow us to change content and appearance.</li>
        </ul>

        <div class="references">
            <h2>References</h2>
            <ul>
                <li><a href="https://www.w3schools.com/js/" target="_blank">W3Schools: JavaScript Tutorial</a></li>
                <li><a href="https://developer.mozilla.org/en-US/docs/Web/JavaScript" target="_blank">MDN: JavaScript Guide</a></li>
            </ul>
        </div>

    </div>

</body>

</html>