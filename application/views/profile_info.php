<div class="row profile-section">
	<!-- <div class="col"> -->
	<!-- <img src="..\assets\user.png" alt="Profile Picture" /> -->
	<!-- </div> -->
	<div class="col">
		<h6 class="m-0"><strong><?= $this->session->lastname, ', ',  $this->session->firstname ?></strong></h6>
		<p><?= $this->session->course, ' - ', $this->session->current_year ?></p>
	</div>
	<div class="col text-center">
		<a href="logout" class="btn btn-outline-danger">Logout</a>
	</div>
</div>

<hr>