<?php $this->load->view('header') ?>


<div class="container">
    <div class="dashboard">
        <div class="profile-section">
            <img src="..\assets\user.png" alt="Profile Picture">
            <h5><strong>John Colson</strong></h5>
            <p>BSIS - 1</p>
        </div>
        <div class="nav-btns">
            <button class="btn btn-outline-secondary btn-custom">Dashboard</button>
            <button class="btn btn-secondary btn-custom">Overview</button>
        </div>
        <div class="category-btns row">
            <div class="col-6">
                <button class="btn btn-outline-secondary btn-custom">Activities<br>10%</button>
            </div>
            <div class="col-6">
                <button class="btn btn-outline-secondary btn-custom">PT<br>40%</button>
            </div>
            <div class="col-6">
                <button class="btn btn-outline-secondary btn-custom">Major Exam<br>30%</button>
            </div>
            <div class="col-6">
                <button class="btn btn-outline-secondary btn-custom">Quizzes<br>20%</button>
            </div>
        </div>
        <div class="total-section">
            <button class="btn btn-secondary btn-total">Total</button>
        </div>
        <div class="text-center">
            <button class="btn btn-outline-danger">Logout</button>
        </div>
    </div>
</div>

<?php $this->load->view('footer') ?>