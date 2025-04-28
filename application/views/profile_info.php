<div class="container">
	<div class="row profile-section text-center mt-3">
		<div class="col">
			<a href="<?= base_url("update_account_form") ?>"><img src="<?= base_url("assets/user.png") ?>" alt="Profile Picture" style="length: 3rem; width: 3rem;" /></a>
		</div>
		<div class="col">
			<h6 class="m-0"><strong><?= $this->session->lastname, ', ',  $this->session->firstname ?></strong></h6>
			<p><?= $this->session->student_id ?> - <?= $this->session->student_no ?></p>
		</div>
		<div class="col text-center">
			<!-- <a href="<?= base_url("update_account_form") ?>" class=" btn btn-outline-dark"><i class="fa fa-user" aria-hidden="true"></i></a> -->
			<a href="<?= base_url("logout") ?>" class=" btn btn-outline-dark">Logout</a>
		</div>
	</div>
</div>

<hr>

<?php $this->load->view('nav_bar'); ?>