<style>
    .flame-container {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 200px;
        height: 270px;
        z-index: 1000;
        pointer-events: none;
    }

    .flame-image {
        width: 100%;
        height: 100%;
        opacity: 1;
        animation: fadeAndZoom 2s infinite ease-in-out;
    }

    @keyframes fadeAndZoom {

        0%,
        100% {
            /* opacity: 1; */
            transform: scale(1);
        }

        50% {
            /* opacity: 0.85; */
            transform: scale(1.1);
        }
    }

    /* body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        pointer-events: none;
    } */
</style>


<div class="flame-container">
    <img src="<?= base_url('assets/streak.png') ?>" alt="Flame" class="flame-image">
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const flameContainer = document.querySelector('.flame-container');

        document.addEventListener('click', (event) => {
            if (!flameContainer.contains(event.target)) {
                flameContainer.style.display = 'none'; // Hide the flame
            }
        });

        document.body.addEventListener('click', () => {
            flameContainer.style.display = 'block'; // Show the flame
        });
    });
</script>