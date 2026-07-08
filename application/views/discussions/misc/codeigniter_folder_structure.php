<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeIgniter's Folder Structure: A School Campus Analogy</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">

    <!-- Discussion Style -->
    <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css') ?>">

    <!-- Highlight.js -->
    <link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
    <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
    <script>
        hljs.highlightAll();
    </script>
</head>

<body>
    <header>
        <div class="container">
            <h1>CodeIgniter's Folder Structure: A School Campus Analogy</h1>
            <p class="subtitle">Understanding how a CodeIgniter 3 project is organized by comparing it to the buildings and offices of a school campus.</p>
        </div>
    </header>

    <div class="content">

        <!-- Learning Objectives -->
        <div class="section">
            <span class="badge-section">Learning Objectives</span>
            <h2>What you should be able to do after this lesson</h2>
            <ul class="objectives-list">
                <li>Identify the main folders in a CodeIgniter 3 project and explain what each one is responsible for.</li>
                <li>Use a school-campus analogy to explain <em>why</em> CodeIgniter separates code into controllers, models, and views.</li>
                <li>Decide where new code should go for a given task (a new page, a new database query, a new stylesheet).</li>
            </ul>
        </div>

        <!-- Concept Explanation -->
        <div class="section">
            <span class="badge-section">Concept Explanation</span>
            <h2>A CodeIgniter project is a campus, not a single building</h2>
            <p>
                Just like this college has a Gate, an Administration Building, a Registrar's Office, and classrooms — each
                with its own job — a CodeIgniter project splits its code into folders that each do <strong>one job only</strong>.
                A visitor (a browser request) always enters through the same gate and gets routed to the right office
                before anything happens.
            </p>

            <div class="flow-diagram">
                <div class="flow-step">Visitor (Browser)</div>
                <div class="flow-arrow">&rarr;</div>
                <div class="flow-step">Main Gate (index.php)</div>
                <div class="flow-arrow">&rarr;</div>
                <div class="flow-step">Guidance Office (Controller)</div>
                <div class="flow-arrow">&rarr;</div>
                <div class="flow-step">Registrar (Model)</div>
                <div class="flow-arrow">&rarr;</div>
                <div class="flow-step">Classroom (View)</div>
                <div class="flow-arrow">&rarr;</div>
                <div class="flow-step">Visitor sees the page</div>
            </div>

            <div class="alert-info">
                <div class="alert-title">Rule of Thumb</div>
                <code>application/</code> is the Administration Building — everything specific to <em>this</em> campus lives
                there. <code>system/</code> is the Division Office — it governs how every CodeIgniter campus operates, so
                you never rearrange its furniture.
            </div>
        </div>

        <!-- Guidelines -->
        <div class="section">
            <span class="badge-section">Guidelines</span>
            <h2>Walking the campus: what each folder is for</h2>

            <ol>
                <li class="mb-3">
                    <strong><code>application/</code> — The Administration Building</strong>
                    <div class="alert-info mt-2">
                        The main building where this specific school's day-to-day work happens. Almost everything you write
                        as a developer lives somewhere inside here.
                    </div>
                </li>

                <li class="mb-3">
                    <strong><code>application/controllers/</code> — Guidance Office / Front Desk</strong>
                    <div class="alert-info mt-2">
                        The first office every visitor reaches. Its only job is to understand <em>what</em> the visitor
                        wants and send them to the right office next — it doesn't open filing cabinets and it doesn't
                        decorate the classroom itself.
                    </div>
                </li>

                <li class="mb-3">
                    <strong><code>application/models/</code> — Registrar's Office / Records Room</strong>
                    <div class="alert-warning mt-2">
                        <strong>The only office allowed to open the filing cabinets (the database).</strong> If the Guidance
                        Office needs a student's grade, it doesn't rummage through the records room itself — it asks the
                        Registrar, who fetches (or files) the record and hands it back.
                    </div>
                </li>

                <li class="mb-3">
                    <strong><code>application/views/</code> — Classrooms &amp; Bulletin Boards</strong>
                    <div class="alert-info mt-2">
                        What the visitor actually sees and reads. A classroom doesn't decide school policy or store
                        permanent records — it just displays whatever it was handed.
                    </div>
                </li>

                <li class="mb-3">
                    <strong><code>application/config/</code> — Principal's Office / Policy Manual</strong>
                    <div class="alert-info mt-2">
                        Campus-wide settings that apply no matter which office you visit: the database credentials, the
                        base URL, the routing rules. One manual, followed by every office.
                    </div>
                </li>

                <li class="mb-3">
                    <strong><code>application/core/</code> — The Blueprint Office</strong>
                    <div class="alert-info mt-2">
                        Base classes (like <code>MY_Model</code> or <code>MY_Controller</code>) that other offices are
                        built on top of — the architectural blueprint every new office in this campus follows.
                    </div>
                </li>

                <li class="mb-3">
                    <strong><code>application/helpers/</code> and <code>application/libraries/</code> — Utility Staff and Specialist Teachers</strong>
                    <div class="alert-info mt-2">
                        <strong>Helpers</strong> are the shared toolbox any office can borrow from for a small task
                        (formatting a date, building a URL). <strong>Libraries</strong> are specialists you call in for a
                        whole service — the session library is the ID-and-attendance specialist; the upload library runs
                        the receiving dock.
                    </div>
                </li>

                <li class="mb-3">
                    <strong><code>system/</code> — The Division Office (framework core)</strong>
                    <div class="alert-warning mt-2">
                        Governs how <em>every</em> CodeIgniter campus operates, not just this one. You don't rewrite
                        division policy to fix one school's schedule — so this folder is never edited directly.
                    </div>
                </li>

                <li class="mb-3">
                    <strong><code>assets/</code> — The Supplies Room / Bookstore</strong>
                    <div class="alert-info mt-2">
                        CSS, JavaScript, images — the physical materials that make the campus look and function well.
                        No student records and no decision-making happen here, just supplies.
                    </div>
                </li>

                <li class="mb-0">
                    <strong><code>index.php</code> and <code>.htaccess</code> — The Main Gate and its Security Rules</strong>
                    <div class="alert-warning mt-2">
                        <code>index.php</code> is the single gate every visitor must pass through before reaching any
                        office. <code>.htaccess</code> is the guard at that gate — it quietly rewrites a casual address
                        like <code>campus/attendance</code> into the full route, and blocks anyone trying to sneak in
                        through a back entrance.
                    </div>
                </li>
            </ol>
        </div>

        <!-- Examples -->
        <div class="section">
            <span class="badge-section">Examples</span>
            <h2>Folder-to-campus quick reference</h2>

            <div class="table-responsive">
                <table class="mb-0">
                    <thead>
                        <tr>
                            <th style="width: 22%;">Folder</th>
                            <th style="width: 28%;">Campus Analogy</th>
                            <th>What Happens There</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>controllers/</code></td>
                            <td>Guidance Office</td>
                            <td>Reads the request, decides what's needed, calls the right model/view.</td>
                        </tr>
                        <tr>
                            <td><code>models/</code></td>
                            <td>Registrar's Office</td>
                            <td>The only place that talks to the database — fetches or saves records.</td>
                        </tr>
                        <tr>
                            <td><code>views/</code></td>
                            <td>Classroom / Bulletin Board</td>
                            <td>Displays HTML to the visitor. No queries, no business decisions.</td>
                        </tr>
                        <tr>
                            <td><code>config/</code></td>
                            <td>Principal's Office</td>
                            <td>Campus-wide settings: DB credentials, base URL, routes, autoloads.</td>
                        </tr>
                        <tr>
                            <td><code>helpers/</code></td>
                            <td>Utility Staff</td>
                            <td>Small, borrowable functions any office can call (e.g. <code>url_helper</code>).</td>
                        </tr>
                        <tr>
                            <td><code>libraries/</code></td>
                            <td>Specialist Teachers</td>
                            <td>Bigger reusable services: sessions, file uploads, form validation.</td>
                        </tr>
                        <tr>
                            <td><code>system/</code></td>
                            <td>Division Office (DepEd)</td>
                            <td>The framework itself. Governs every campus — never edited per-project.</td>
                        </tr>
                        <tr>
                            <td><code>assets/</code></td>
                            <td>Supplies Room</td>
                            <td>CSS, JS, images, fonts — no logic, just materials.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Checklist -->
        <div class="section">
            <span class="badge-section">Checklist</span>
            <h2>Where do I put my code? (use this before writing anything)</h2>

            <div class="alert-info">
                Ask yourself what the task actually needs, then find its office:
                <ul class="objectives-list mb-0">
                    <li><strong>Fetch or save database data?</strong> &rarr; <code>models/</code></li>
                    <li><strong>Decide what happens when a URL is visited?</strong> &rarr; <code>controllers/</code></li>
                    <li><strong>Display something to the user?</strong> &rarr; <code>views/</code></li>
                    <li><strong>A campus-wide constant or setting?</strong> &rarr; <code>config/</code></li>
                    <li><strong>A small reusable function (e.g. format a date)?</strong> &rarr; <code>helpers/</code></li>
                    <li><strong>A bigger reusable service (sessions, uploads)?</strong> &rarr; <code>libraries/</code></li>
                    <li><strong>A stylesheet, image, or script?</strong> &rarr; <code>assets/</code></li>
                </ul>
            </div>
        </div>

        <!-- Hands-on Activity -->
        <div class="section">
            <span class="badge-section">Hands-on Activity</span>
            <h2>Activity: Route the request</h2>

            <p class="mb-2">
                A teacher wants a new page that shows a class's average grade, computed from the
                <code>classworks</code> table.
            </p>
            <p class="mb-2"><strong>Task:</strong> Using the campus analogy, describe which "offices" (folders) the
                request visits, and in what order, from the moment the teacher opens the page to the moment they see
                the average on screen.</p>

            <div class="alert-tip">
                <div class="alert-title">One possible answer</div>
                <ol class="mb-0">
                    <li>The browser sends the request through the <strong>Main Gate</strong> (<code>index.php</code>).</li>
                    <li>The <strong>Guidance Office</strong> (a controller, e.g. <code>GradesController</code>) receives
                        it and realizes it needs grade data.</li>
                    <li>The Guidance Office asks the <strong>Registrar</strong> (a model, e.g. <code>classworks</code>)
                        to compute the average from the database.</li>
                    <li>The Registrar hands the computed average back to the Guidance Office.</li>
                    <li>The Guidance Office hands that number to a <strong>Classroom</strong> (a view) to display,
                        styled using materials from the <strong>Supplies Room</strong> (<code>assets/</code>).</li>
                    <li>The visitor sees the finished page.</li>
                </ol>
            </div>
        </div>

        <!-- Reflection -->
        <div class="section">
            <span class="badge-section">Reflection Questions</span>
            <h2>Check your understanding</h2>
            <div class="reflection-box">
                <ol class="mb-0">
                    <li>Why shouldn't a <code>view</code> file query the database directly?</li>
                    <li>If two different controllers need to format a date the same way, where should that logic live?</li>
                    <li>Why is the <code>system/</code> folder never edited directly by developers working on a specific project?</li>
                    <li>Using the campus analogy, what would go wrong if visitors could skip the Main Gate and walk
                        straight into the Registrar's Office?</li>
                    <li>Where would you place the HTML for a brand-new "Attendance Kiosk" page?</li>
                </ol>
            </div>
        </div>

        <!-- Quick Summary -->
        <div class="section">
            <span class="badge-section">Summary</span>
            <h2>Key takeaways</h2>
            <ul class="objectives-list mb-0">
                <li><strong>Controllers</strong> are the Guidance Office — they decide and direct, nothing else.</li>
                <li><strong>Models</strong> are the Registrar — the only door into the database.</li>
                <li><strong>Views</strong> are the Classroom — display only, no logic.</li>
                <li><strong>Config</strong> is the Principal's Office — campus-wide settings.</li>
                <li><strong>System</strong> is the Division Office — the framework core, never edited per-project.</li>
                <li><strong>Assets</strong> is the Supplies Room — CSS, JS, and images only.</li>
                <li><strong>index.php</strong> is the Main Gate — the one entrance every request passes through.</li>
            </ul>
        </div>

    </div>
</body>

</html>
