<?php $this->load->view('header'); ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>
    </div>
</div>

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

    // Start animation when page loads
    window.onload = function() {
        animateNumber(<?= randomizeNumber(8.9, 10.0) ?>, 2500);
    }
</script>