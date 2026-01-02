<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap Sandbox with CodeMirror</title>
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('./assets/codemirror.min.css') ?>" />

    <style>
        body {
            background: #f8f9fa;
        }

        .sandbox {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .CodeMirror {
            border: 1px solid #ccc;
            border-radius: 8px;
            height: 300px;
            font-size: 14px;
        }

        iframe {
            width: 100%;
            height: 300px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: white;
        }
    </style>
</head>

<body class="p-4">

    <h2 class="mb-3">Bootstrap Live Sandbox</h2>
    <p>Type HTML, CSS, Javascript code below and click <strong>Run Code</strong> to see the live output.</p>

    <div class="sandbox">
        <!-- editor textarea (give it an id so CodeMirror can attach) -->
        <textarea id="codeEditor">
            <h1 id="message">Click the button below</h1>
<button onclick="window.alert('hello')">
  Hello Alert
</button>
<button onclick="document.write('hello')">
  Write
</button>
<button onclick="document.getElementById('message').innerHTML = 'Button Clicked!'">
  Change Text
</button>
<button onclick="document.getElementById('message').style.color = 'red'">
  Change Color
</button>
        </textarea>

        <div class="d-flex">
            <button id="runBtn" class="btn btn-primary mr-2" type="button">Run Code</button>
            <button id="copyBtn" class="btn btn-outline-secondary" type="button" title="Copy code to clipboard">Copy</button>
        </div>

        <iframe id="outputFrame"></iframe>
    </div>

    <!-- CodeMirror JS -->
    <script src="<?= base_url('assets/codemirror.min.js') ?>"></script>
    <script src="<?= base_url('assets/codemirror-htmlmixed.min.js') ?>"></script>
    <script src="<?= base_url('assets/codemirror-xml.min.js') ?>"></script>
    <script src="<?= base_url('assets/codemirror-javascript.min.js') ?>"></script>
    <script src="<?= base_url('assets/codemirror-css.min.js') ?>"></script>

    <script>
        // Initialize CodeMirror
        const editor = CodeMirror.fromTextArea(document.getElementById('codeEditor'), {
            mode: 'text/html',
            lineNumbers: true,
            theme: 'default',
            tabSize: 2,
            lineWrapping: true,
        });

        // Run code into iframe
        function runCode() {
            const code = editor ? editor.getValue() : document.getElementById('codeEditor').value;
            const iframe = document.getElementById('outputFrame');
            const bootstrapLink = `<link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">`;
            iframe.srcdoc = bootstrapLink + code;
        }

        // Copy editor content to clipboard
        async function copyCode() {
            const btn = document.getElementById('copyBtn');
            const code = editor ? editor.getValue() : document.getElementById('codeEditor').value;
            // Try navigator.clipboard first
            try {
                await navigator.clipboard.writeText(code);
                const original = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(() => btn.textContent = original, 1500);
            } catch (err) {
                // Fallback for older browsers
                try {
                    const tmp = document.createElement('textarea');
                    tmp.value = code;
                    document.body.appendChild(tmp);
                    tmp.select();
                    document.execCommand('copy');
                    document.body.removeChild(tmp);
                    const original = btn.textContent;
                    btn.textContent = 'Copied!';
                    setTimeout(() => btn.textContent = original, 1500);
                } catch (e) {
                    alert('Copy failed. Please select the code and press Ctrl/Cmd+C.');
                }
            }
        }

        // Wire buttons
        document.getElementById('runBtn').addEventListener('click', runCode);
        document.getElementById('copyBtn').addEventListener('click', copyCode);
    </script>

</body>

</html>