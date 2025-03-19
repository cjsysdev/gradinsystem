<?php $this->load->view('header') ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>
        <a class="btn alert-primary btn-block mb-3" href="./uploads/105_reviewer.pdf" download="105_reviewer.pdf" src="./uploads/105_reviewer.pdf"><i class="fa fa-download" aria-hidden="true" style="margin-right: 10px"> </i>Download CC105 - Midterm Reviewer</a>
        <?php if ($class): ?>
            <div class="card-body p-1 text-center">
                <h5><span class="badge badge-secondary mb-1"><?= $class["class_code"], ' ', $class["type"] ?? NULL ?></span></h5>
                <h6 class="card-subtitle text-body-secondary"><?= $class["class_name"], ' ', $class["type"] ?></h6>
                <p class="card-text m-0"><?= $class["section"], ' ', $class["day"], ' : ', convert_time($class["time_start"]), '-', convert_time($class["time_end"]) ?></p>
                <p class="card-text m-0 mt-3" id="txt"></p>
                <div id="txt"></div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <?= $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>
        <hr>
        <div class="card-body p-1 text-center">
            <h5 class="card-subtitle text-body-secondary"><span class="badge badge-secondary">Attendance Record </span></h5>
            <?php
            ?>
            <table class="table table-striped table-bordered table-hover table-sm mt-3">
                <tbody>
                    <?php foreach ($record as $row):
                        $formatted = convert_datetime($row["date"]);
                    ?>
                        <tr>
                            <td><?= $row["type"] ?></td>
                            <td><?= $formatted['date'] ?></td>
                            <td><?= $formatted['time'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

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

<?php $this->load->view('footer') ?>