<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap Tables</title>
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
    <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
    <script>hljs.highlightAll();</script>
</head>

<body>

    <header>
        <h1>Bootstrap Tables</h1>
        <p>Learn how to create clean, responsive, and user-friendly tables using Bootstrap 4.</p>
    </header>

    <div class="content">

        <h2>What are Bootstrap Tables?</h2>
        <p>
            Bootstrap provides built-in classes that style tables with consistent spacing, borders, hover effects,
            striped rows, and responsive layouts.
            These styles are applied using simple class names like <code>.table</code>, <code>.table-striped</code>,
            <code>.table-hover</code>, and
            <code>.table-responsive</code>.
        </p>

        <h2>Basic Table</h2>
        <p>A basic Bootstrap table uses the <code>.table</code> class.</p>

        <h3>Example:</h3>
        <pre><code>&lt;table class="table"&gt;
  &lt;thead&gt;
    &lt;tr&gt;
      &lt;th&gt;#&lt;/th&gt;
      &lt;th&gt;First&lt;/th&gt;
      &lt;th&gt;Last&lt;/th&gt;
      &lt;th&gt;Handle&lt;/th&gt;
    &lt;/tr&gt;
  &lt;/thead&gt;
  &lt;tbody&gt;
    &lt;tr&gt;
      &lt;th scope="row"&gt;1&lt;/th&gt;
      &lt;td&gt;Mark&lt;/td&gt;
      &lt;td&gt;Otto&lt;/td&gt;
      &lt;td&gt;@mdo&lt;/td&gt;
    &lt;/tr&gt;
    &lt;tr&gt;
      &lt;th scope="row"&gt;2&lt;/th&gt;
      &lt;td&gt;Jacob&lt;/td&gt;
      &lt;td&gt;Thornton&lt;/td&gt;
      &lt;td&gt;@fat&lt;/td&gt;
    &lt;/tr&gt;
  &lt;/tbody&gt;
&lt;/table&gt;</code></pre>

        <p>Output:</p>
        <table class="table mb-4">
            <thead>
                <tr>
                    <th>#</th>
                    <th>First</th>
                    <th>Last</th>
                    <th>Handle</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">1</th>
                    <td>Mark</td>
                    <td>Otto</td>
                    <td>@mdo</td>
                </tr>
                <tr>
                    <th scope="row">2</th>
                    <td>Jacob</td>
                    <td>Thornton</td>
                    <td>@fat</td>
                </tr>
            </tbody>
        </table>

        <h2>Striped Tables</h2>
        <p>Add <code>.table-striped</code> to alternate row background colors.</p>

        <pre><code>&lt;table class="table table-striped"&gt;...&lt;/table&gt;</code></pre>

        <table class="table table-striped mb-4">
            <thead>
                <tr>
                    <th>#</th>
                    <th>First</th>
                    <th>Last</th>
                    <th>Handle</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">1</th>
                    <td>Anna</td>
                    <td>Smith</td>
                    <td>@anna</td>
                </tr>
                <tr>
                    <th scope="row">2</th>
                    <td>Carl</td>
                    <td>Johnson</td>
                    <td>@cj</td>
                </tr>
            </tbody>
        </table>

        <h2>Hoverable Rows</h2>
        <p>Use <code>.table-hover</code> to add a hover highlight when the mouse moves over rows.</p>

        <pre><code>&lt;table class="table table-hover"&gt;...&lt;/table&gt;</code></pre>

        <table class="table table-hover mb-4">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">1</th>
                    <td>Learning C</td>
                    <td>J. Doe</td>
                    <td>2020</td>
                </tr>
                <tr>
                    <th scope="row">2</th>
                    <td>Web Dev Guide</td>
                    <td>M. Ray</td>
                    <td>2021</td>
                </tr>
            </tbody>
        </table>

        <h2>Responsive Tables</h2>
        <p>Wrap tables inside <code>.table-responsive</code> so they become scrollable on small screens.</p>

        <pre><code>&lt;div class="table-responsive"&gt;
  &lt;table class="table"&gt;...&lt;/table&gt;
&lt;/div&gt;</code></pre>

        <div class="table-responsive mb-4">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Device</th>
                        <th>OS</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>Phone A</td>
                        <td>Android</td>
                        <td>10</td>
                        <td>Active</td>
                        <td>Test Device</td>
                    </tr>
                    <tr>
                        <th scope="row">2</th>
                        <td>Phone B</td>
                        <td>iOS</td>
                        <td>14</td>
                        <td>Inactive</td>
                        <td>Needs Update</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h2>Summary</h2>
        <ul>
            <li>Use <code>.table</code> for basic table styling.</li>
            <li>Apply <code>.table-striped</code> or <code>.table-hover</code> for readability.</li>
            <li>Wrap with <code>.table-responsive</code> for mobile-friendly layouts.</li>
        </ul>

        <div class="references">
            <h2>References</h2>
            <ul>
                <li><a href="https://www.w3schools.com/bootstrap4/bootstrap_tables.asp" target="_blank">W3Schools: Bootstrap Tables</a></li>
                <li><a href="https://getbootstrap.com/docs/4.5/content/tables/" target="_blank">Bootstrap 4 Documentation: Tables</a></li>
            </ul>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
