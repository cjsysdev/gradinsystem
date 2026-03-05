<!DOCTYPE html>
<html>

<head>
    <title>Excuse Letter</title>
    <style>
        body {
            background: #f8f9fa;
        }

        #excuseLetterContent {
            font-family: Arial, sans-serif;
            width: 8.5in;
            height: 11in;
            min-height: 11in;
            margin: 30px auto;
            padding: 1in;
            box-sizing: border-box;
            background: white;
        }

        .placeholder {
            color: lightgray;
        }
    </style>
</head>

<body>
    <div id="excuseLetterContent">
        <div style="text-align:center; margin-bottom: 30px;">
            <img src="<?= base_url('assets/cmc_logo_no_bg.png') ?>" alt="School Logo" style="height:70px; margin-bottom:10px;">
            <h3 style="margin:0;">Carmen Municipal College</h3>
            <div style="font-size:15px;">Pob. Norte, Carmen, Bohol<br>info@cmcbohol.edu.ph</div>
            <hr style="margin:20px 0 0 0;">
        </div>
        <h2 style="text-align:center; margin-top: 10px;">Excuse Letter</h2>
        <p style="text-align:right;">Date: <span id="excuseDate"></span></p>
        <p>To whom it may concern,</p>
        <p style="text-align:justify;">
            I am writing to formally excuse my absence on <b><span id="excuseAbsenceDate"></span></b> due to the following reason:
        </p>
        <blockquote style="background:#f1f1f1; padding:10px; border-radius:6px;">
            <span id="excuseReason"></span>
        </blockquote>
        <p>Course: <span id="excuseCourse"></span></p>
        <br><br>
        <p>Sincerely,</p>
        <br><br>
        <p><b><?= $this->session->lastname . ' ' . $this->session->firstname ?></b></p>
        <p>Student</p>
        <br><br><br>
        <p>Verified by:</p>
        <br><br><br>
        <p class="placeholder">(Name & Signature)</p>
        <div style="text-align:center; margin-top:40px;">
            <button onclick="downloadLetterAsImage()" style="padding:10px 20px;">Download as Image</button>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script>
        // Get query params
        function getParam(name) {
            const url = new URL(window.location.href);
            return url.searchParams.get(name) || '';
        }
        // Format date as 'dddd, mmmm dd, yyyy'
        function formatLongDate(dateStr) {
            const date = new Date(dateStr);
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const dayName = days[date.getDay()];
            const monthName = months[date.getMonth()];
            const day = date.getDate();
            const year = date.getFullYear();
            return `${dayName}, ${monthName} ${day < 10 ? '0' : ''}${day}, ${year}`;
        }
        document.getElementById('excuseDate').innerText = formatLongDate(new Date());
        document.getElementById('excuseAbsenceDate').innerText = formatLongDate(getParam('date'));
        document.getElementById('excuseCourse').innerText = decodeURIComponent(getParam('course'));
        document.getElementById('excuseReason').innerText = decodeURIComponent(getParam('reason'));

        function downloadLetterAsImage() {
            html2canvas(document.getElementById('excuseLetterContent'), {
                width: 816, // 8.5in * 96dpi
                height: 1056 // 11in * 96dpi
            }).then(canvas => {
                var link = document.createElement('a');
                link.download = 'excuse_letter.png';
                link.href = canvas.toDataURL();
                link.click();
            });
        }
    </script>
</body>

</html>