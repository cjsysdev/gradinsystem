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

        <a class="btn alert-primary btn-block mb-3" href="./uploads/discussion/DBMS_normalization.pdf" download="bootstrap.4.5.2.min.css" src="./uploads/discussion/DBMS_normalization.pdf"><i class="fa fa-download" aria-hidden="true" style="margin-right: 10px"> </i>DBMS_normalization</a>
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
            </div>
        <?php endforeach; ?>
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