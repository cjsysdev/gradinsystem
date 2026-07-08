<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap Framework Basics</title>
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css') ?>">
    <!-- Highlight.js CSS Theme -->
    <!-- Highlight.js CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
    <!-- Highlight.js JS -->
    <script src="<?= base_url("assets/highlights/11.7.0-highlight.min.js") ?> "></script>
    <script>
        hljs.highlightAll();
    </script>
</head>

<body>

    <header>
        <h1>Bootstrap Framework Basics</h1>
        <p>Learn how to install and use Bootstrap containers and the grid system</p>
    </header>

    <div class="content">

        <h2>What is Bootstrap?</h2>
        <p>
            <strong>Bootstrap</strong> is a popular front-end framework that helps developers create
            responsive, mobile-first websites easily. It includes ready-made
            <strong>CSS, JavaScript, and components</strong> for faster web development.
        </p>

        <h2>How to Install Bootstrap</h2>
        <p>You can add Bootstrap to your project in two common ways:</p>

        <h3>Option 1: Using CDN (Recommended for Beginners)</h3>
        <p>Add the following links inside the <code>&lt;head&gt;</code> of your HTML document:</p>
        <pre><code>&lt;link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"&gt;
&lt;script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"&gt;&lt;/script&gt;
</code></pre>

        <h3>Option 2: Downloading Bootstrap</h3>
        <p>Download it from the <a href="https://getbootstrap.com" target="_blank">official Bootstrap website</a>
            and include the CSS and JS files manually.</p>

        <h2>Why Use Bootstrap?</h2>
        <ul>
            <li>✅ <strong>Responsive Design</strong> — your layout adjusts to any screen size.</li>
            <li>⚡ <strong>Faster Development</strong> — includes pre-designed components.</li>
            <li>🎨 <strong>Consistent Design</strong> — ensures a uniform look and feel.</li>
            <li>🔧 <strong>Customizable</strong> — easily change colors, spacing, and typography.</li>
        </ul>

        <h3>Example:</h3>
        <pre><code>&lt;button class="btn btn-primary"&gt;Click Me&lt;/button&gt;</code></pre>
        <p>Output:</p>
        <button class="btn btn-primary mb-3">Click Me</button>

        <h2>Understanding Containers</h2>
        <p>
            Containers are the building blocks of Bootstrap layouts.
            They help align content and give it a proper width depending on the screen size.
        </p>

        <table>
            <tr>
                <th>Container Type</th>
                <th>Description</th>
            </tr>
            <tr>
                <td><code>.container</code></td>
                <td>Fixed-width container that adjusts based on screen size.</td>
            </tr>
            <tr>
                <td><code>.container-fluid</code></td>
                <td>Full-width container that spans the entire viewport.</td>
            </tr>
        </table>

        <h3>Example:</h3>
        <pre><code>&lt;div class="container"&gt;
  &lt;h3&gt;Fixed Width Container&lt;/h3&gt;
&lt;/div&gt;

&lt;div class="container-fluid bg-light"&gt;
  &lt;h3&gt;Full Width Container&lt;/h3&gt;
&lt;/div&gt;
</code></pre>

        <h2>Bootstrap Grid System Basics</h2>
        <p>
            The <strong>Bootstrap grid system</strong> helps you design web pages that automatically adjust
            for different screen sizes. It uses a flexible 12-column layout, allowing you to organize
            content easily.
        </p>

        <h3>How the Grid Works</h3>
        <ul>
            <li>The grid system is built with <code>.container</code> (or <code>.container-fluid</code>), <code>.row</code>, and <code>.col</code> classes.</li>
            <li>Content must be placed inside columns, and only columns may be immediate children of rows.</li>
            <li>Rows are used to create horizontal groups of columns.</li>
            <li>Columns automatically resize to fill the row — or you can specify their width.</li>
        </ul>

        <table>
            <tr>
                <th>Class</th>
                <th>Screen Size</th>
                <th>Description</th>
            </tr>
            <tr>
                <td><code>.col-</code></td>
                <td>Extra small (&lt;576px)</td>
                <td>Always horizontal</td>
            </tr>
            <tr>
                <td><code>.col-sm-</code></td>
                <td>Small (≥576px)</td>
                <td>Stack vertically below 576px</td>
            </tr>
            <tr>
                <td><code>.col-md-</code></td>
                <td>Medium (≥768px)</td>
                <td>Stack below 768px</td>
            </tr>
            <tr>
                <td><code>.col-lg-</code></td>
                <td>Large (≥992px)</td>
                <td>Stack below 992px</td>
            </tr>
            <tr>
                <td><code>.col-xl-</code></td>
                <td>Extra large (≥1200px)</td>
                <td>Stack below 1200px</td>
            </tr>
        </table>

        <h3>Example 1: Equal Columns</h3>
        <pre><code>&lt;div class="container"&gt;
  &lt;div class="row text-center"&gt;
    &lt;div class="col bg-primary text-white p-3"&gt;Column 1&lt;/div&gt;
    &lt;div class="col bg-success text-white p-3"&gt;Column 2&lt;/div&gt;
    &lt;div class="col bg-warning text-dark p-3"&gt;Column 3&lt;/div&gt;
  &lt;/div&gt;
&lt;/div&gt;
</code></pre>

        <div class="container mb-3">
            <div class="row text-center">
                <div class="col bg-primary text-white p-3">Column 1</div>
                <div class="col bg-success text-white p-3">Column 2</div>
                <div class="col bg-warning text-dark p-3">Column 3</div>
            </div>
        </div>

        <h3>Example 2: Responsive Columns</h3>
        <p>This example shows how columns stack on smaller devices and align side-by-side on larger screens.</p>
        <pre><code>&lt;div class="container"&gt;
  &lt;div class="row text-center"&gt;
    &lt;div class="col-sm-4 bg-info text-white p-3"&gt;col-sm-4&lt;/div&gt;
    &lt;div class="col-sm-8 bg-secondary text-white p-3"&gt;col-sm-8&lt;/div&gt;
  &lt;/div&gt;
&lt;/div&gt;
</code></pre>

        <div class="container mb-3">
            <div class="row text-center">
                <div class="col-sm-4 bg-info text-white p-3">col-sm-4</div>
                <div class="col-sm-8 bg-secondary text-white p-3">col-sm-8</div>
            </div>
        </div>

        <h3>Example 3: Mixed Column Sizes</h3>
        <pre><code>&lt;div class="container"&gt;
  &lt;div class="row text-center"&gt;
    &lt;div class="col-sm-3 bg-danger text-white p-3"&gt;col-sm-3&lt;/div&gt;
    &lt;div class="col-sm-6 bg-success text-white p-3"&gt;col-sm-6&lt;/div&gt;
    &lt;div class="col-sm-3 bg-warning text-dark p-3"&gt;col-sm-3&lt;/div&gt;
  &lt;/div&gt;
&lt;/div&gt;
</code></pre>

        <div class="container mb-3">
            <div class="row text-center">
                <div class="col-sm-3 bg-danger text-white p-3">col-sm-3</div>
                <div class="col-sm-6 bg-success text-white p-3">col-sm-6</div>
                <div class="col-sm-3 bg-warning text-dark p-3">col-sm-3</div>
            </div>
        </div>

        <h3>Example 4: Nesting Columns</h3>
        <p>You can place a new <code>.row</code> inside an existing column to create nested layouts.</p>
        <pre><code>&lt;div class="container"&gt;
  &lt;div class="row text-center"&gt;
    &lt;div class="col-sm-8 bg-light border p-3"&gt;
      &lt;p&gt;Main Column&lt;/p&gt;
      &lt;div class="row"&gt;
        &lt;div class="col-6 bg-primary text-white p-2"&gt;Nested 1&lt;/div&gt;
        &lt;div class="col-6 bg-success text-white p-2"&gt;Nested 2&lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
    &lt;div class="col-sm-4 bg-secondary text-white p-3"&gt;Side Column&lt;/div&gt;
  &lt;/div&gt;
&lt;/div&gt;
</code></pre>

        <div class="container">
            <div class="row text-center">
                <div class="col-sm-8 bg-light border p-3">
                    <p>Main Column</p>
                    <div class="row">
                        <div class="col-6 bg-primary text-white p-2">Nested 1</div>
                        <div class="col-6 bg-success text-white p-2">Nested 2</div>
                    </div>
                </div>
                <div class="col-sm-4 bg-secondary text-white p-3">Side Column</div>
            </div>
        </div>

        <h2>Summary</h2>
        <ul>
            <li>Bootstrap uses a 12-column grid for flexible layouts.</li>
            <li>Use <code>.container</code> (or <code>.container-fluid</code>), <code>.row</code>, and <code>.col</code> classes to structure content.</li>
            <li>Columns can be responsive, automatically stacking or aligning based on screen size.</li>
            <li>Nesting allows more complex layouts inside columns.</li>
        </ul>

        <div class="references">
            <h2>References</h2>
            <ul>
                <li><a href="https://getbootstrap.com/docs/5.3/layout/grid/" target="_blank">Bootstrap 5 Documentation: Grid System</a></li>
                <li><a href="https://www.w3schools.com/bootstrap4/bootstrap_grid_basic.asp" target="_blank">W3Schools: Bootstrap Grid Basic</a></li>
                <li><a href="https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Responsive_Design" target="_blank">MDN: Responsive Design</a></li>
            </ul>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>