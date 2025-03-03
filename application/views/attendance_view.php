<?php $this->load->view('header') ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>
        <?php if ($class): ?>
            <div class="card-body p-1 text-center">
                <h5> <span class="badge badge-danger">SQL INSERT</span></h5>
                <!-- <p>Create your own function, choose any operators or formulas based on your preference</p> -->
                <p>Understand the different types of MySQL insert statements. Learn the syntax and use cases for each insert method.</p>
                <a class="btn alert-secondary" href="./uploads/INSERT_INTO.pdf" download="INSERT_INTO.pdf" src="./uploads/INSERT_INTO.pdf"><i class="fa fa-download" aria-hidden="true" style="margin-right: 10px"> </i>Download</a>
                <!-- <img src="./uploads/INSERT_INTO.jpg" alt="Lab activity" style="width:300px;height:120px;"> -->
                <!-- <a href="./uploads/CP2_Riddle.pdf" download="CP2_Riddle.pdf" src="./uploads/CP2_Riddle.pdf">Download Here</a> -->
            </div>
            <hr>
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
    function startTime() {
        const today = new Date();
        let h = today.getHours();
        let m = today.getMinutes();
        let s = today.getSeconds();
        m = checkTime(m);
        s = checkTime(s);
        document.getElementById('txt').innerHTML = h + ":" + m + ":" + s;
        setTimeout(startTime, 1000);
    }

    function checkTime(i) {
        if (i < 10) {
            i = "0" + i
        }; // add zero in front of numbers < 10
        return i;
    }
</script>

<?php $this->load->view('footer') ?>