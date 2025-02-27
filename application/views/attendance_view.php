<?php $this->load->view('header') ?>

<style>
    body {
        font-family: Arial, sans-serif;
    }

    table {
        width: 75%;
        border-collapse: collapse;
        margin: 15px auto;
        font-size: small;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 7.5px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #f1f1f1;
    }
</style>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>
        <?php if ($class): ?>
            <div class="card-body p-1 text-center">
                <h5><span class="badge badge-primary"><?= $class["class_code"], ' ', $class["type"] ?? NULL ?></span></h5>
                <h6 class="card-subtitle text-body-secondary"><?= $class["class_name"], ' ', $class["type"] ?></h6>
                <p class="card-text m-0"><?= $class["section"], ' ', $class["day"], ' : ', convert_time($class["time_start"]), '-', convert_time($class["time_end"]) ?></p>
                <p class="card-text m-0 mt-3" id="txt"></p>
                <div id="txt"></div>
            </div>
            <hr>
            <div class="card-body p-1 text-center">
                <!-- <h5> <span class="badge badge-danger">LAB ACTIVITY</span></h5> -->
                <!-- <p>Create your own function, choose any operators or formulas based on your preference</p> -->
                <!-- <p>Understand the basic syntax and functionality of the MySQL SELECT statement.</p> -->
                <!-- <a class="btn alert-secondary" href="./uploads/Functions.pdf" download="Functions.pdf" src="./uploads/Functions.pdf"><i class="fa fa-download" aria-hidden="true" style="margin-right: 10px"> </i>Download</a> -->
                <!-- <img src="./uploads/INSERT_INTO.jpg" alt="Lab activity" style="width:300px;height:120px;"> -->
                <!-- <a href="./uploads/CP2_Riddle.pdf" download="CP2_Riddle.pdf" src="./uploads/CP2_Riddle.pdf">Download Here</a> -->
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <?= $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>
        <hr>
        <div class="card-body p-1 text-center">
            <h5 class="card-subtitle text-body-secondary"><span class="badge badge-primary">Attendance Record </span></h5>
            <?php
            function convert_datetime($datetime)
            {
                $date = new DateTime($datetime);
                return [
                    'date' => $date->format('D M j'),
                    'time' => $date->format('h:i A')
                ];
            }

            function convert_time($time)
            {
                $newtime = new DateTime($time);
                return $newtime->format('h:i A');
            }

            ?>
            <table>
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