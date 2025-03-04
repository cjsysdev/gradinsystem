<script src="<?= base_url("/assets/jquery-3.5.1.slim.min.js") ?> "></script>
<script src="<?= base_url("/assets/popper.min.js") ?>"></script>
<script src="<?= base_url("/assets/bootstrap.bundle.min.js") ?>"></script>

<script src="<?= base_url("assets/highlights/11.7.0-highlight.min.js") ?> "></script>
<script>
    hljs.highlightAll();
</script>

<!-- Bootstrap 4.5.2 JS (with Popper and jQuery) -->
<script src="<?= base_url("/assets/2-jquery-3.5.1.slim.min.js") ?>"></script>
<script src="<?= base_url("/assets/4.5.2.bootstrap.bundle.min") ?>"></script>

<!-- JavaScript to Handle Form Submission -->
<script>
    document.getElementById('confirmSave').addEventListener('click', function() {
        document.getElementById('code-form').submit();
    });
</script>

<!-- CodeMirror JavaScript -->
<script src="<?= base_url('./assets/codemirror.min.js ?>') ?> "></script>
<script src="<?= base_url('./assets/clike.min.js') ?>"></script>

<script>
    const editor = CodeMirror.fromTextArea(document.getElementById('code-editor'), {
        mode: 'text/x-csrc',
        lineNumbers: true,
        indentUnit: 4,
        matchBrackets: true,
        autoCloseBrackets: true,
    });

    window.onload = function() {
        const savedText = localStorage.getItem('textboxValue');

        if (savedText) {
            editor.setValue(savedText);
        }
    };

    function saveText() {
        const textboxValue = editor.getValue();
        localStorage.setItem('textboxValue', textboxValue);
        alert('Text saved!');
    }
</script>


</body>

</html>