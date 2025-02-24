<?php $this->load->view('header') ?>


<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>
        <div class="card-body p-1 text-center">
            <span class="badge badge-primary mb-3"><?= $class["class_code"], ' ', $class["type"] ?? NULL ?></span>
            <h6 class="card-subtitle text-body-secondary"><?= $class["class_name"], ' ', $class["type"] ?></h6>
            <p class="card-text m-0"><?= $class["section"], ' ', $class["day"], ' : ', $class["time_start"], '-', $class["time_end"] ?></p>
            <p class="card-text m-0 mt-3" id="txt"></p>
            <div id="txt"></div>
            <!-- <span class="badge badge-light mb-3"><?= $class["status"] ?></span> -->
        </div>
        <hr>
        <div class="card-body p-1 text-center">
            <h6 class="card-subtitle text-body-secondary mb-3">Attendance Record</h6>
            <?php foreach ($record as $row): ?>
                <p class="card-text m-0"><?= $row["class_code"], " " , $row["date"]?></p>
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