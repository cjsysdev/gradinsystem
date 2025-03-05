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
</script>


<script>
    function animateNumber(finalNumber, duration = 2000) {
        // Create overlay elements
        const wrapper = document.createElement('div');
        const numberDisplay = document.createElement('div');

        wrapper.className = 'overlay-wrapper';
        numberDisplay.className = 'number-overlay';
        numberDisplay.textContent = '0.00';

        wrapper.appendChild(numberDisplay);
        document.body.appendChild(wrapper);

        const startNumber = 0;
        const startTime = performance.now();

        function updateNumber(currentTime) {
            const elapsedTime = currentTime - startTime;
            const progress = Math.min(elapsedTime / duration, 1);
            const easedProgress = 1 - Math.pow(1 - progress, 4);
            const currentNumber = startNumber + (finalNumber - startNumber) * easedProgress;

            numberDisplay.textContent = currentNumber.toFixed(2);

            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            } else {
                numberDisplay.textContent = finalNumber.toFixed(2);
            }
        }

        // Start animation
        requestAnimationFrame(updateNumber);

        // Remove overlay when clicking outside
        wrapper.addEventListener('click', function(e) {
            // Only remove if click is on the wrapper itself, not the number
            if (e.target === wrapper) {
                document.body.removeChild(wrapper);
            }
        });
    }
</script>


</body>

</html>