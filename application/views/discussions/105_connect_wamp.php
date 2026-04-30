<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Connecting to a Database Using WAMP</title>

  <!-- Bootstrap 4.5.2 -->
  <link rel="stylesheet" href="<?php echo base_url('assets/bootstrap.min.css'); ?>">
  <!-- Custom Style -->
  <link rel="stylesheet" href="<?php echo base_url('assets/discussion-style.css'); ?>">
  <!-- Highlight.js -->
  <link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
  <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
  <script>
    hljs.highlightAll();
  </script>
  <style>
    /* ── Page-specific styles only ── */

    .step {
      display: flex;
      align-items: flex-start;
      margin-bottom: 1rem;
    }

    .step-num {
      background: #2b6cb0;
      color: #fff;
      border-radius: 50%;
      min-width: 26px;
      height: 26px;
      line-height: 26px;
      text-align: center;
      font-weight: 700;
      font-size: .82rem;
      margin-right: 10px;
      margin-top: 2px;
      flex-shrink: 0;
    }

    .step p {
      margin: 0;
    }

    .path {
      display: inline-block;
      background: #263238;
      color: #80cbc4;
      font-family: 'Courier New', monospace;
      font-size: .85rem;
      padding: .2rem .75rem;
      border-radius: 20px;
      margin: .3rem 0;
    }

    .output {
      background: #263238;
      color: #a5d6a7;
      border-radius: 6px;
      padding: .6rem 1rem;
      font-family: 'Courier New', monospace;
      font-size: .87rem;
      margin: .6rem 0;
    }

    .topic-badge {
      display: inline-block;
      background: #ebf8ff;
      color: #2b6cb0;
      border: 1px solid #bee3f8;
      border-radius: 20px;
      padding: .25rem .85rem;
      font-size: .82rem;
      font-weight: 600;
      margin: .25rem .2rem;
    }
  </style>
</head>

<body>

  <header>
    <div class="container">
      <h1>🗄️ Connecting to a Database Using WAMP</h1>
      <p>PHP &amp; MySQL — Local Development Basics</p>
    </div>
  </header>

  <div class="container content mt-4 mb-5">

    <!-- OBJECTIVES -->
    <div class="section">
      <h2>🎯 Objectives</h2>
      <ul class="mb-0">
        <li>Navigate to the WAMP <code class="ic">www</code> folder and create a project.</li>
        <li>Write a PHP file that connects to a MySQL database.</li>
        <li>Test the connection through the browser.</li>
      </ul>
    </div>

    <!-- QUICK CONCEPT -->
    <div class="section">
      <h2>💡 Quick Concept</h2>
      <p class="mb-2">
        WAMP runs a local web server on your computer. For PHP files to work, they must be placed inside the <code class="ic">www</code> folder — this is what Apache serves to your browser via <code class="ic">localhost</code>.
      </p>
      <div class="note">💡 Think of <code class="ic">localhost</code> as your computer acting as its own website.</div>
    </div>

    <!-- STEPS -->
    <div class="section">
      <h2>🪜 Steps</h2>

      <!-- 1 -->
      <div class="step">
        <div class="step-num">1</div>
        <div>
          <p><strong>Go to the WAMP www folder</strong></p>
          <p class="mt-1">Open File Explorer and navigate to:</p>
          <span class="path">C:\wamp64\www\</span>
          <div class="note mt-2">💡 You can also click the WAMP tray icon → <em>www directory</em>.</div>
        </div>
      </div>

      <!-- 2 -->
      <div class="step">
        <div class="step-num">2</div>
        <div>
          <p><strong>Create your project folder</strong></p>
          <p class="mt-1">Right-click inside <code class="ic">www</code> → New Folder. Name it (e.g., <code class="ic">myproject</code>).</p>
          <span class="path">C:\wamp64\www\myproject\</span>
          <div class="warn mt-2">⚠️ Use lowercase, no spaces.</div>
        </div>
      </div>

      <!-- 3 -->
      <div class="step">
        <div class="step-num">3</div>
        <div>
          <p><strong>Create a PHP file inside your folder</strong></p>
          <p class="mt-1">Open your code editor, create <code class="ic">index.php</code>, and save it here:</p>
          <span class="path">C:\wamp64\www\myproject\index.php</span>
        </div>
      </div>

      <!-- 4 -->
      <div class="step">
        <div class="step-num">4</div>
        <div>
          <p><strong>Test it in the browser</strong></p>
          <p class="mt-1">Make sure WAMP is running (tray icon is <span style="color:green;font-weight:700;">green</span>), then open:</p>
          <span class="path">http://localhost/myproject/</span>
          <p class="mt-2 mb-1">Put this in your file first to confirm PHP works:</p>
          <pre><code class="language-php">&lt;?php
  echo "PHP is working!";
?&gt;</code></pre>
          <div class="output">PHP is working!</div>
        </div>
      </div>

      <!-- 5 -->
      <div class="step">
        <div class="step-num">5</div>
        <div>
          <p><strong>Locate or create your database in phpMyAdmin</strong></p>
          <p class="mt-1">Open phpMyAdmin in your browser:</p>
          <span class="path">http://localhost/phpmyadmin</span>
          <p class="mt-2 mb-1">
            <strong>Database already exists?</strong> — Find it in the left sidebar and take note of the exact name.
          </p>
          <p class="mb-1">
            <strong>Need to create one?</strong> — Click <em>New</em>, enter a name (e.g., <code class="ic">school_db</code>), then click <em>Create</em>.
          </p>
          <div class="note">💡 Default WAMP login: Username <code class="ic">root</code> · Password <em>(leave blank)</em></div>
        </div>
      </div>

      <!-- 6 -->
      <div class="step">
        <div class="step-num">6</div>
        <div>
          <p><strong>Write the database connection script</strong></p>
          <p class="mt-1">Update your <code class="ic">index.php</code> with this:</p>
          <pre><code class="language-php">&lt;?php
  $host     = "localhost";
  $username = "root";
  $password = "";            // blank by default in WAMP
  $database = "school_db";   // your database name

  $conn = mysqli_connect($host, $username, $password, $database);

  if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
  }

  echo "Connected successfully!";
?&gt;</code></pre>
          <div class="output">Connected successfully!</div>
        </div>
      </div>
    </div>

    <!-- QUICK ERRORS -->
    <div class="section">
      <h2>🔧 Common Errors</h2>
      <table class="table table-sm table-bordered mb-0" style="font-size:.88rem;">
        <thead class="thead-dark">
          <tr>
            <th>Error</th>
            <th>Fix</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><code>Unknown database</code></td>
            <td>Check the database name in phpMyAdmin</td>
          </tr>
          <tr>
            <td><code>Access denied</code></td>
            <td>Use <code>root</code> and leave password blank</td>
          </tr>
          <tr>
            <td>Blank page</td>
            <td>Ensure WAMP is running and file is <code>.php</code></td>
          </tr>
          <tr>
            <td>Raw PHP code shows</td>
            <td>Use <code>http://localhost/…</code>, not the file path</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- REFLECTION -->
    <div class="section">
      <h2>🪞 Reflection</h2>
      <ol class="mb-0">
        <li>Why must PHP files be inside the <code class="ic">www</code> folder?</li>
        <li>What does <code class="ic">mysqli_connect()</code> do, and what happens if it fails?</li>
        <li>Why should you avoid writing connection code in every PHP file?</li>
      </ol>
    </div>

    <!-- RELATED TOPICS -->
    <div class="section">
      <h2>🔗 Related Topics</h2>
      <span class="topic-badge">PHP include &amp; require</span>
      <span class="topic-badge">Creating MySQL Tables</span>
      <span class="topic-badge">INSERT INTO</span>
      <span class="topic-badge">SELECT with WHERE</span>
      <span class="topic-badge">PHP Forms &amp; POST</span>
      <span class="topic-badge">Fetching Records</span>
      <span class="topic-badge">phpMyAdmin Basics</span>
    </div>

  </div>

  <footer>
    Carmen Municipal College — Web Development | PHP &amp; MySQL
  </footer>

  <!-- Bootstrap JS -->
  <script src="<?= base_url('assets/jquery.slim.min.js') ?>"></script>
  <script src="<?= base_url('assets/bootstrap.bundle.min.js') ?>"></script>

  <!-- Highlight.js init -->
  <script>
    hljs.highlightAll();
  </script>
</body>

</html>