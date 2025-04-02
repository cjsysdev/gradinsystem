<div class="container">
	<?php if (!$this->session->exam_term): ?>
		<div class="form-group row">
			<a href="<?= base_url('attendance') ?>" class="btn btn-outline-secondary col m-2">Stream</a>
			<a href="<?= base_url('classwork') ?>" class="btn btn-outline-success col m-2">Classwork</a>
			<a href="<?= base_url('output_upload') ?>" class="btn btn-outline-secondary col m-2">Project</a>
			<a href="<?= base_url('grades') ?>" class="btn btn-outline-secondary col m-2">Grades</a>
		</div>
	<?php else: ?>
		<div class="form-group row">
			<a href="<?= base_url('classwork') ?>" class="btn btn-outline-success col m-2">Midterm Exam</a>
		</div>
	<?php endif; ?>
</div>