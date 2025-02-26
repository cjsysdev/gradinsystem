<?php $this->load->view('header') ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>
        <?php if ($class): ?>
            <div class="card-body p-1 text-center">
                <h5><span class="badge badge-primary"><?= $class["class_code"], ' ', $class["type"] ?? NULL ?></span></h5>
                <h6 class="card-subtitle text-body-secondary"><?= $class["class_name"], ' ', $class["type"] ?></h6>
                <p class="card-text m-0"><?= $class["section"], ' ', $class["day"], ' : ', $class["time_start"], '-', $class["time_end"] ?></p>
                <p class="card-text m-0 mt-3" id="txt"></p>
                <div id="txt"></div>
            </div>
            <hr>
            <div class="card-body p-1 text-center">
                <h5> <span class="badge badge-danger">Lab Activity</span></h5>
                <img src="./uploads/FUNCTION_TEMPERATURES.jpg" alt="Lab activity" style="width:300px;height:120px;">
                <!-- <a href="./uploads/Storage_engine.pdf" download="Storage_engine.pdf" src="./uploads/Storage_engine.pdf">Download Here</a> -->
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <?= $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>
        <hr>
        <div class="card-body p-1 text-center">
            <h5 class="card-subtitle text-body-secondary"><span class="badge badge-primary">Attendance Record </span></h5>
            <?php foreach ($record as $row): ?>
                <p class="card-text m-0"><?= $row["class_code"], $row["type"], " ", $row["date"] ?></p>
            <?php endforeach; ?>
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