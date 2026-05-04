<div class="container">
	<div class="row profile-section text-center mt-3">
		<div class="col">
			<?php
				$pic = $this->session->profile_pic;
				$pic_src = ($pic && file_exists(FCPATH . 'uploads/profile_pics/' . $pic))
					? base_url('uploads/profile_pics/' . $pic)
					: base_url('assets/user.png');
			?>
			<a href="<?= base_url("update_account_form") ?>">
				<img src="<?= $pic_src ?>" alt="Profile Picture"
				     class="rounded-circle border"
				     style="width:40px; height:40px; object-fit:cover;" />
			</a>
		</div>
		<div class="col">
			<h6 class="m-0"><strong><?= $this->session->lastname, ', ',  $this->session->firstname ?></strong></h6>
			<p style="font-size: 15px;"> <span class="badge badge-info"> <?= $this->session->student_id ?></span> - <?= $this->session->section ?></p>
		</div>
		<div class="col text-center">
			<!-- <a title="survey" href="" class=" btn btn-outline-dark"><i class="fa fa-list" aria-hidden="true"></i> Survey</a> -->
			<a href="<?= base_url('emergency_contacts') ?>" title="Emergency Contacts" class="btn btn-outline-danger"><i class="fa fa-phone" aria-hidden="true"></i></a>
			<a href="<?= base_url("logout") ?>" class=" btn btn-outline-dark"><i class="fa fa-sign-out" aria-hidden="true"></i></a>
		</div>
	</div>
</div>

<hr>

<?php $this->load->view('nav_bar'); ?>