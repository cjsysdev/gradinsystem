<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Bootstrap 4 — Forms: Discussion & Activities</title>
    <!-- Bootstrap 4 CDN -->
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">
    <style>
        body {
            padding: 2rem;
        }

        .example {
            background: #f8f9fa;
            border-radius: .375rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        pre code {
            display: block;
            white-space: pre-wrap;
        }
    </style>
</head>

<body>
    <div class="container">
        <header class="mb-4">
            <h1>Bootstrap 4 — Forms: Classroom Discussion & Activities</h1>
            <p class="lead">A structured classroom discussion and hands‑on activity set to teach Bootstrap 4 forms (based on common Bootstrap 4 patterns).</p>
        </header>

        <section class="mb-4">
            <h2>Goals & Learning Outcomes</h2>
            <ul>
                <li>Understand how Bootstrap styles form elements using utility classes like <code>.form-control</code> and grouping with <code>.form-group</code>.</li>
                <li>Compare plain HTML forms vs. Bootstrap forms and appreciate benefits (consistency, responsiveness).</li>
                <li>Build responsive forms using Bootstrap grid (rows &amp; cols).</li>
                <li>Implement inline/horizontal forms and client‑side validation with Bootstrap's validation classes.</li>
                <li>Consider accessibility and semantic markup in form design.</li>
            </ul>
        </section>

        <section class="mb-4">
            <h2>Prerequisites</h2>
            <p>Students should know basic HTML form tags (<code>&lt;form&gt;</code>, <code>&lt;input&gt;</code>, <code>&lt;select&gt;</code>, <code>&lt;textarea&gt;</code>, <code>&lt;label&gt;</code>) and have a general idea of CSS classes.</p>
        </section>

        <section class="mb-4">
            <h2>Discussion Outline</h2>
            <ol>
                <li><strong>Warm up:</strong> Show a plain HTML form. Ask: what is missing in terms of layout and responsiveness?</li>
                <li><strong>Introduce Bootstrap:</strong> What does <code>.form-control</code> do? How does the grid help arrange fields?</li>
                <li><strong>Show examples:</strong> stacked, inline, two-column, and horizontal forms.</li>
                <li><strong>Validation &amp; accessibility:</strong> required fields, <code>aria</code> attributes, labels linked with <code>for</code> + <code>id</code>.</li>
                <li><strong>Hands-on:</strong> Convert the plain form into a Bootstrap form; then refactor it to be responsive.</li>
            </ol>
        </section>

        <section class="mb-4">
            <h2>Live Examples (editable)</h2>

            <div class="example">
                <h5>1) Simple stacked form</h5>
                <p>Use <code>.form-group</code> and <code>.form-control</code> to make each field full width.</p>
                <form>
                    <div class="form-group">
                        <label for="name">Full name</label>
                        <input type="text" class="form-control" id="name" placeholder="Enter your name">
                    </div>
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" class="form-control" id="email" placeholder="name@example.com">
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea class="form-control" id="message" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>

            <div class="example">
                <h5>2) Two-column responsive form (grid)</h5>
                <p>Combine Bootstrap grid with form classes to place two fields side-by-side on md+ screens and stacked on mobile.</p>
                <form>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="firstName">First name</label>
                            <input type="text" class="form-control" id="firstName">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="lastName">Last name</label>
                            <input type="text" class="form-control" id="lastName">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="city">City</label>
                            <input type="text" class="form-control" id="city">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="state">State</label>
                            <select id="state" class="form-control">
                                <option selected>Choose...</option>
                                <option>...</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="zip">Zip</label>
                            <input type="text" class="form-control" id="zip">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">Save</button>
                </form>
            </div>

            <div class="example">
                <h5>3) Inline form</h5>
                <p>Use <code>.form-inline</code> for compact forms (usually for small search bars or tight UIs).</p>
                <form class="form-inline">
                    <label class="sr-only" for="inlineEmail">Email</label>
                    <input type="email" class="form-control mb-2 mr-sm-2" id="inlineEmail" placeholder="email@example.com">

                    <label class="sr-only" for="inlinePassword">Password</label>
                    <input type="password" class="form-control mb-2 mr-sm-2" id="inlinePassword" placeholder="Password">

                    <button type="submit" class="btn btn-primary mb-2">Sign in</button>
                </form>
            </div>

            <div class="example">
                <h5>4) Form with custom controls &amp; checkboxes</h5>
                <form>
                    <div class="form-group">
                        <label for="customFile">Upload file</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="customFile">
                            <label class="custom-file-label" for="customFile">Choose file</label>
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="gridCheck">
                        <label class="form-check-label" for="gridCheck">Check me out</label>
                    </div>

                    <button type="submit" class="btn btn-outline-primary mt-2">Upload</button>
                </form>
            </div>

        </section>

        <section class="mb-4">
            <h2>Bootstrap Form Validation (client-side) — Example</h2>
            <p>Bootstrap 4 uses classes such as <code>.was-validated</code> and pseudo-classes to style validity. Below is a simple pattern using native HTML validation and Bootstrap styling.</p>

            <div class="example">
                <form class="needs-validation" novalidate>
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label for="validationName">Name</label>
                            <input type="text" class="form-control" id="validationName" required>
                            <div class="invalid-feedback">Please provide your name.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="validationEmail">Email</label>
                            <input type="email" class="form-control" id="validationEmail" required>
                            <div class="invalid-feedback">Please provide a valid email.</div>
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Submit form</button>
                </form>

                <script>
                    // Example starter JavaScript for disabling form submissions if there are invalid fields
                    (function() {
                        'use strict';
                        window.addEventListener('load', function() {
                            var forms = document.getElementsByClassName('needs-validation');
                            var validation = Array.prototype.filter.call(forms, function(form) {
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
            </div>
        </section>

        <section class="mb-4">
            <h2>Accessibility &amp; Best Practices</h2>
            <ul>
                <li>Always use <code>&lt;label for="id"&gt;</code> to link labels to inputs for screen readers.</li>
                <li>Use semantic form controls and native types (<code>email</code>, <code>tel</code>, <code>date</code>) for better mobile keyboards and validation.</li>
                <li>Provide clear placeholder text but don’t rely on placeholders as the only label.</li>
                <li>Ensure sufficient color contrast on form controls and validation messages.</li>
                <li>Use <code>aria-describedby</code> for additional hints when needed.</li>
            </ul>
        </section>

        <section class="mb-4">
            <h2>Class Activities &amp; Exercises</h2>
            <ol>
                <li>Convert this plain form (provided on a worksheet) to a Bootstrap stacked form using <code>.form-group</code> and <code>.form-control</code>.</li>
                <li>Refactor the form into a two-column layout using Bootstrap grid so that name/email are side-by-side on md+ screens.</li>
                <li>Create a compact inline search bar using <code>.form-inline</code> and discuss when inline forms are appropriate (usability tradeoffs).</li>
                <li>Add client-side validation to the form using the <code>.needs-validation</code> pattern shown above.</li>
                <li>Accessibility check: verify label associations, tab order, and ARIA where necessary. Swap with another pair and perform a checklist review.</li>
            </ol>
        </section>

        <section class="mb-4">
            <h2>Assessment (Short)</h2>
            <p>Ask students to submit a single HTML file implementing a responsive contact form that:</p>
            <ul>
                <li>Uses Bootstrap classes for layout and controls.</li>
                <li>Has at least one row with two inputs side-by-side on desktop.</li>
                <li>Includes client-side validation and a required email field.</li>
                <li>Includes comments describing choices and accessibility considerations.</li>
            </ul>
        </section>

        <footer class="mt-5">
            <h6>Further reading &amp; resources</h6>
            <ul>
                <li>Bootstrap 4 documentation (forms)</li>
                <li>General accessibility guidance for forms</li>
            </ul>
            <p class="text-muted">Instructor note: replace resource list with specific links you'd like your students to use (e.g., Bootstrap docs, W3Schools, MDN).</p>
        </footer>
    </div>

    <!-- Bootstrap 4 JS -->
    <script src="<?= base_url("/assets/2-jquery-3.5.1.slim.min.js") ?>"></script>
    <script src="<?= base_url("/assets/4.5.2.bootstrap.bundle.min") ?>"></script>
</body>

</html>