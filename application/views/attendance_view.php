<?php $this->load->view('header'); ?>


<script src="<?= base_url('/assets/jquery-3.5.1.slim.min.js') ?> "></script>
<script src="<?= base_url('/assets/underscore-min.js') ?> "></script>
<script src="<?= base_url('/assets/moment.min.js') ?> "></script>
<script src="<?= base_url('/assets/clndr.min.js') ?> "></script>

<style>
    #calendar {
        max-width: 800px;
        margin: 0 auto;
    }

    .day.event {
        background-color: #ffe0b2;
    }

    .day.today {
        background-color: #e0f7fa;
    }

    .day.adjacent-month {
        color: #ccc;
    }

    .clndr-previous-button,
    .clndr-next-button {
        cursor: pointer;
    }

    /* Ensure consistent sizing and alignment */
    .days-of-the-week,
    .days {
        display: flex;
        /* Use flexbox for consistent column widths */
        flex-wrap: wrap;
    }

    .days-of-the-week .col,
    .days .col {
        flex: 1 0 calc(100% / 7 - 4px);
        /* Equal width for 7 columns, accounting for margin */
        max-width: calc(100% / 7 - 4px);
        /* Prevent overflow */
        padding: 5px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin: 2px;
        box-sizing: border-box;
        /* Include padding/border in width calculation */
        min-height: 40px;
        /* Ensure day cells have enough height */
    }

    .days-of-the-week .col {
        font-weight: bold;
        border: none;
        /* No border for day names */
        background-color: #f8f9fa;
        /* Light background for clarity */
    }

    #redOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 0, 0, 0.8);
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .overlay-text {
        font-size: 2rem;
        font-weight: bold;
        text-align: center;
    }
</style>

<div class="container">

    <div class="dashboard">
        <?php $this->load->view('profile_info'); ?>
        <?php
        $course =
            $this->class_student->get([
                'student_id' => $this->session->student_id,
            ])->class_id ?? '1';
        if ($course === '1') {
            $desc = '105';
        } else {
            $desc = '103';
        }
        ?>

        <?php if ($this->session->flashdata('warning') !== NULL): ?>
            <div class="alert alert-warning">
                <?= $this->session->flashdata('warning') ?>
            </div>
        <?php endif; ?>

        <!-- <a class="btn alert-primary btn-block mb-3" href="./uploads/discussion/DBMS_normalization.pdf" download="bootstrap.4.5.2.min.css" src="./uploads/discussion/DBMS_normalization.pdf"><i class="fa fa-download" aria-hidden="true" style="margin-right: 10px"> </i>DBMS_normalization</a> -->
        <!-- <a class="btn alert-primary btn-block mb-3" href="http://192.168.1.137/cmc/public/index.php">Survey</a> -->

        <?php if ($this->session->exam_review): ?>
            <a class="btn alert-primary btn-block mb-3" href="./uploads/<?= $desc ?>_reviewer.pdf" download="<?= $desc ?>_reviewer.pdf" src="./uploads/<?= $desc ?>_reviewer.pdf"><i class="fa fa-download" aria-hidden="true" style="margin-right: 10px"> </i>Download CC<?= $desc ?> - Midterm Reviewer</a>
        <?php endif; ?>
        <?php if ($class): ?>
            <div class="card-body p-1 text-center">
                <h5><span class="badge badge-secondary mb-1"><?= $class['class_code'],
                                                                ' ',
                                                                $class['type'] ?? null ?></span></h5>
                <h6 class="card-subtitle text-body-secondary"><?= $class['class_name'],
                                                                ' ',
                                                                $class['type'] ?></h6>
                <p class="card-text m-0"><?= $class['section'],
                                            ' ',
                                            $class['day'],
                                            ' : ',
                                            convert_time($class['time_start']),
                                            '-',
                                            convert_time($class['time_end']) ?></p>
                <p class="card-text m-0 mt-3" id="txt"></p>
                <div id="txt"></div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <?= $this->session->flashdata('error') ?>
            </div>
        <?php endif; ?>
        <hr>

        <div class="row m-1">
            <div class="col alert alert-success mr-2">
                <strong><?= $present ?> Present Sessions</strong>
            </div>
            <div class="col alert alert-secondary">
                <strong><?= $absences ?> Missed Sessions</strong>
            </div>
        </div>
        <?php if (!empty($absences_dates)): ?>
            <h4 class="text-center">Absences</h4>
        <?php endif; ?>
        <?php foreach ($absences_dates as $absence): ?>
            <div class="row m-1">
                <div class="col alert alert-secondary">
                    <strong>
                        <?= htmlspecialchars($absence['type']) ?>
                        <?= date('l, F j, Y', strtotime($absence['date'])) ?>
                    </strong>
                </div>
                <?php if (!empty($absence['reason'])): ?>
                    <div class="col-12 p-2" style="background:#f8f9fa; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.04); margin-bottom:8px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><b>Reason:</b> <?= htmlspecialchars($absence['reason']) ?></span>
                            <button class="btn btn-outline-primary btn-sm" onclick="printExcuseLetter('<?= htmlspecialchars($absence['date']) ?>', '<?= htmlspecialchars($absence['class_code'] . ' - ' . $absence['class_name'] . ' - ' . $absence['section']) ?>', '<?= htmlspecialchars($absence['reason']) ?>')">
                                <i class="fa fa-print mr-1"></i> Print Excuse Letter
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="post" action="<?= base_url('add_reason') ?>" class="row align-items-center p-2" style="background:#f8f9fa; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.04); margin-bottom:8px;">
                        <input type="hidden" name="attendance_id" value="<?= $absence['attendance_id'] ?>">
                        <div class="col-md-8 col-12 mb-2 mb-md-0">
                            <input type="text" name="reason" class="form-control" placeholder="Enter reason for absence..." required style="border-radius:6px;">
                        </div>
                        <div class="col-md-4 col-12 text-md-right text-center">
                            <button type="submit" class="btn btn-success btn-block px-4" style="border-radius:6px;">
                                <i class="fa fa-paper-plane mr-1"></i>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <!-- Excuse Letter Template for Printing -->
        <div id="excuseLetterTemplate" style="display:none;">
            <div id="excuseLetterContent" style="font-family: Arial, sans-serif; width: 8.5in; height: 11in; min-height: 11in; margin: 0 auto; padding: 1in; box-sizing: border-box; background: white;">
                <div style="text-align:center; margin-bottom: 30px;">
                    <img src="<?= base_url('assets/cmc_logo_no_bg.png') ?>" alt="School Logo" style="height:70px; margin-bottom:10px;">
                    <h3 style="margin:0;">Carmen Municipal College</h3>
                    <div style="font-size:15px;">Pob. Norte, Carmen, Bohol<br>info@cmcbohol.edu.ph</div>
                    <hr style="margin:20px 0 0 0;">
                </div>
                <h2 style="text-align:center; margin-top: 10px;">Excuse Letter</h2>
                <p style="text-align:right;">Date: <span id="excuseDate"></span></p>
                <p>To whom it may concern,</p>
                <p style="text-align:justify;">I am writing to formally excuse my absence on <b><span id="excuseAbsenceDate"></span></b> due to the following reason:</p>
                <blockquote style="background:#f1f1f1; padding:10px; border-radius:6px;"><span id="excuseReason"></span></blockquote>
                <p>Course: <span id="excuseCourse"></span></p>
                <br><br>
                <p>Sincerely,</p>
                <br><br>
                <p><b><?= $this->session->lastname . ' ' . $this->session->firstname ?></b></p>
                <p>Student</p> 
                <br><br><br>
                <p>Verified by:</p> 
                <br><br>
                ________________________
            </div>
        </div>

        <script>
            function printExcuseLetter(absenceDate, course, reason) {
                // Fill in the template
                document.getElementById('excuseDate').innerText = new Date().toLocaleDateString();
                document.getElementById('excuseAbsenceDate').innerText = formatLongDate(absenceDate);
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
                document.getElementById('excuseCourse').innerText = course;
                document.getElementById('excuseReason').innerText = reason;

                // Prepare print window for short bond paper (8.5x11in)
                var printContents = document.getElementById('excuseLetterContent').outerHTML;
                var win = window.open('', '', 'width=850,height=1100');
                win.document.write('<html><head><title>Excuse Letter</title>');
                win.document.write('<style>@media print { body { margin:0; } #excuseLetterContent { width:8.5in; height:11in; min-height:11in; margin:0; padding:1in; box-sizing:border-box; background:white; } }</style>');
                win.document.write('</head><body>' + printContents + '</body></html>');
                win.document.close();
                win.focus();
                setTimeout(function() {
                    win.print();
                    win.close();
                }, 500);
            }
        </script>
        <div class="container mt-1 mb-5 p-0">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<?php if (isset($show_red_overlay) && $show_red_overlay): ?>
    <div id="redOverlay">
        <div class="overlay-text">Subject to Re-admission</div>
    </div>
<?php endif; ?>

<script id="calendar-template" type="text/template">
    <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center p-3">
                <div class="clndr-previous-button btn btn-sm btn-outline-secondary"><</div>
                <div class="month h4 mb-0 text-center flex-grow-1"><%= month %> <%= year %></div>
                <div class="clndr-next-button btn btn-sm btn-outline-secondary">></div>
            </div>
            <div class="card-body p-3">
                <div class="days-of-the-week mb-3">
                    <% _.each(daysOfTheWeek, function(day) { %>
                        <div class="col"><%= day %></div>
                    <% }); %>
                </div>
                <div class="days">
                    <% _.each(days, function(day) { %>
                        <div class="col day <%= day.classes %>"><%= day.day %></div>
                    <% }); %>
                </div>
            </div>
        </div>
        
    </script>

<script type="text/javascript">
    $(document).ready(function() {
        // Set week to start on Sunday (0) or Monday (1) as desired
        moment.locale('en', {
            week: {
                dow: 0 // Sunday start
            }
        });

        var events = <?= $events ?>;

        var formattedEvents = events.map(function(event) {
            return {
                date: moment(event.date).format('YYYY-MM-DD'),
                title: event.type,
                datetime: event.date
            };
        });

        var daysOfTheWeek = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];

        $('#calendar').clndr({
            template: $('#calendar-template').html(),
            events: formattedEvents,
            daysOfTheWeek: daysOfTheWeek,
            clickEvents: {
                click: function(target) {
                    if (target.events.length) {
                        var eventsHtml = '';
                        target.events.forEach(function(event) {
                            eventsHtml += ' ' + event.title + ' ' + event.datetime;
                        });
                        alert(eventsHtml);
                    }
                }
            },
            adjacentDaysChangeMonth: true,
            forceSixRows: true,
            startWithMonth: moment()
        });
    });
</script>

<script>
    window.onload = function startTime() {
        const today = new Date();
        let h = today.getHours();
        let m = today.getMinutes();
        let s = today.getSeconds();
        const ampm = h >= 12 ? 'PM' : 'AM'; // Determine AM or PM

        // Convert to 12-hour format
        h = h % 12;
        h = h ? h : 12; // Handle midnight (0 hours)

        m = checkTime(m);
        s = checkTime(s);
        document.getElementById('txt').innerHTML = h + ":" + m + ":" + s + " " + ampm;
        setTimeout(startTime, 1000);
    }

    function checkTime(i) {
        if (i < 10) {
            i = "0" + i; // Add zero in front of numbers < 10
        }
        return i;
    }
</script>