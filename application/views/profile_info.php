<div class="container">
	<div class="row profile-section text-center mt-3">
		<div class="col">
			<a href="<?= base_url("update_account_form") ?>"><img src="<?= base_url("assets/user.png") ?>" alt="Profile Picture" style="length: 3rem; width: 3rem;" /></a>
		</div>
		<div class="col">
			<h6 class="m-0"><strong><?= $this->session->lastname, ', ',  $this->session->firstname ?></strong></h6>
			<p style="font-size: 15px;"> <span class="badge badge-info"> <?= $this->session->student_id ?></span> - <?= $this->session->student_no ?> - <?= $this->session->section ?></p>
		</div>
		<div class="col text-center">
			<!-- <a title="survey" href="" class=" btn btn-outline-dark"><i class="fa fa-list" aria-hidden="true"></i> Survey</a> -->
			<a href="<?= base_url("logout") ?>" class=" btn btn-outline-dark">Logout</a>
		</div>
	</div>
</div>

<hr>

<?php $this->load->view('nav_bar'); ?>