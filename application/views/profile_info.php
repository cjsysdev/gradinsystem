<?php
	// Forced temporary-password change: block the rest of the app until the
	// student sets a new password. Skipped on the update form itself so they can
	// actually complete the change. Client-side guard; the authoritative checks
	// live in AuthenticationController::login and StudentController::update_account.
	$force_pw_block = $this->session->must_change_password
		&& $this->router->fetch_method() !== 'update_account_form';
?>
<?php if ($force_pw_block): ?>
	<div style="position:fixed; inset:0; z-index:2000; background:rgba(0,0,0,0.75); display:flex; align-items:center; justify-content:center;">
		<div class="bg-white rounded shadow p-4 text-center" style="max-width:420px;">
			<i class="fa fa-key fa-2x text-warning mb-2"></i>
			<h5 class="fw-bold">Set a New Password</h5>
			<p class="text-muted small mb-3">
				You're signed in with a temporary password. For your security, you
				must set a new password before you can use the system.
			</p>
			<a href="<?= base_url('update_account_form') ?>" class="btn btn-warning">
				<i class="fa fa-lock mr-1"></i> Change Password Now
			</a>
			<div class="mt-2">
				<a href="<?= base_url('logout') ?>" class="small text-muted">Log out</a>
			</div>
		</div>
	</div>
<?php endif; ?>

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
			<a href="<?= base_url('grades') ?>" class="text-dark text-decoration-none">
				<h6 class="m-0"><strong><?= $this->session->lastname, ', ',  $this->session->firstname ?></strong></h6>
				<p style="font-size: 15px;"> <span class="badge badge-info"> <?= $this->session->student_id ?></span> - <?= $this->session->section ?></p>
			</a>
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