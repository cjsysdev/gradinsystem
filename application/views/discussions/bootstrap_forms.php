<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap Forms</title>
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
        <h1>Bootstrap Forms</h1>
        <p>Learn how to design responsive and user-friendly forms using Bootstrap 4 components and utilities.</p>
    </header>

    <div class="content">

        <h2>What are Bootstrap Forms?</h2>
        <p>
            Bootstrap forms are pre-styled and responsive HTML form components designed to make form creation faster and
            easier. They use the <code>.form-control</code>, <code>.form-group</code>, and grid classes to improve layout
            and consistency.
        </p>

        <h2>Basic Form Structure</h2>
        <p>Bootstrap forms use <code>.form-group</code> to group labels and controls, and <code>.form-control</code> for input styling.</p>

        <h3>Example:</h3>
        <pre><code>&lt;form&gt;
  &lt;div class="form-group"&gt;
    &lt;label for="email"&gt;Email address&lt;/label&gt;
    &lt;input type="email" class="form-control" id="email" placeholder="Enter email"&gt;
  &lt;/div&gt;
  &lt;div class="form-group"&gt;
    &lt;label for="password"&gt;Password&lt;/label&gt;
    &lt;input type="password" class="form-control" id="password" placeholder="Password"&gt;
  &lt;/div&gt;
  &lt;button type="submit" class="btn btn-primary"&gt;Submit&lt;/button&gt;
&lt;/form&gt;
</code></pre>

        <p>Output:</p>
        <form class="mb-3">
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" class="form-control" id="email" placeholder="Enter email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" placeholder="Password">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <h2>Horizontal Forms</h2>
        <p>Horizontal forms align labels and input fields side-by-side using the Bootstrap grid system.</p>
        <pre><code>&lt;form&gt;
  &lt;div class="form-group row"&gt;
    &lt;label for="name" class="col-sm-2 col-form-label"&gt;Name&lt;/label&gt;
    &lt;div class="col-sm-10"&gt;
      &lt;input type="text" class="form-control" id="name" placeholder="Full Name"&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/form&gt;
</code></pre>

        <div class="container mb-3">
            <form>
                <div class="form-group row">
                    <label for="name" class="col-sm-2 col-form-label">Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="name" placeholder="Full Name">
                    </div>
                </div>
            </form>
        </div>

        <h2>Inline Forms</h2>
        <p>Use <code>.form-inline</code> for compact forms such as search bars or login sections.</p>
        <pre><code>&lt;form class="form-inline"&gt;
  &lt;input class="form-control mr-sm-2" type="search" placeholder="Search"&gt;
  &lt;button class="btn btn-outline-success my-2 my-sm-0" type="submit"&gt;Search&lt;/button&gt;
&lt;/form&gt;
</code></pre>

        <form class="form-inline mb-3">
            <input class="form-control mr-sm-2" type="search" placeholder="Search">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
        </form>

        <h2>Form Validation</h2>
        <p>Bootstrap provides validation styles using <code>.was-validated</code> and HTML5 validation attributes.</p>

        <pre><code>&lt;form class="needs-validation" novalidate&gt;
  &lt;div class="form-group"&gt;
    &lt;label for="username"&gt;Username&lt;/label&gt;
    &lt;input type="text" class="form-control" id="username" required&gt;
    &lt;div class="invalid-feedback"&gt;Please enter your username.&lt;/div&gt;
  &lt;/div&gt;
  &lt;button type="submit" class="btn btn-primary"&gt;Submit&lt;/button&gt;
&lt;/form&gt;
</code></pre>

        <form class="needs-validation mb-3" novalidate>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" required>
                <div class="invalid-feedback">Please enter your username.</div>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <script>
            (function() {
                'use strict';
                window.addEventListener('load', function() {
                    var forms = document.getElementsByClassName('needs-validation');
                    Array.prototype.filter.call(forms, function(form) {
                        form.addEventListener('submit', function(event) {
                            if (form.checkValidity() === false) {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                            form.classList.add('was-validated');
                        }, false);
                    });
                }, false);
            })();
        </script>

        <h2>Custom Form Controls</h2>
        <p>Bootstrap also includes custom styles for checkboxes, radio buttons, and file inputs.</p>
        <pre><code>&lt;div class="custom-control custom-checkbox"&gt;
  &lt;input type="checkbox" class="custom-control-input" id="customCheck1"&gt;
  &lt;label class="custom-control-label" for="customCheck1"&gt;Remember me&lt;/label&gt;
&lt;/div&gt;
</code></pre>

        <div class="custom-control custom-checkbox mb-3">
            <input type="checkbox" class="custom-control-input" id="customCheck1">
            <label class="custom-control-label" for="customCheck1">Remember me</label>
        </div>

        <h2>Summary</h2>
        <ul>
            <li>Use <code>.form-group</code> and <code>.form-control</code> for consistent spacing and input styling.</li>
            <li>Leverage the grid system to build horizontal forms.</li>
            <li>Use <code>.form-inline</code> for compact forms.</li>
            <li>Enhance usability with Bootstrap’s validation and custom controls.</li>
        </ul>

        <div class="references">
            <h2>References</h2>
            <ul>
                <li><a href="https://www.w3schools.com/bootstrap4/bootstrap_forms.asp" target="_blank">W3Schools: Bootstrap Forms</a></li>
                <li><a href="https://getbootstrap.com/docs/4.5/components/forms/" target="_blank">Bootstrap 4 Documentation: Forms</a></li>
                <li><a href="https://developer.mozilla.org/en-US/docs/Learn/Forms" target="_blank">MDN: HTML Forms</a></li>
            </ul>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>