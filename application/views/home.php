<?php $this->load->view('header') ?>


<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>

        <div class="category-btns">
            <button class="btn btn-outline-secondary btn-block">Activities<br>10%</button>
            <button class="btn btn-outline-secondary btn-block">Performance Task<br>40%</button>
            <button class="btn btn-outline-secondary btn-block">Major Exam<br>30%</button>
            <button class="btn btn-outline-secondary btn-block">Quizzes<br>20%</button>
            <div class="category-btns d-flex justify-content-between mt-2">
                <button class="btn btn-outline-secondary flex-fill mr-2">Midterm<br>50%</button>
                <button class="btn btn-outline-secondary flex-fill ">Tentative-Final<br>50%</button>
            </div>
        </div>


        <div class="total-section mt-3">
            <button class="btn btn-secondary btn-total btn-block">Final Grade <br> N/A </button>
        </div>
    </div>
</div>

<?php $this->load->view('footer') ?>