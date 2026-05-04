<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_info') ?>

    <div class="row justify-content-center mt-4 mb-5">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa fa-user-circle mr-2"></i>Update Account</h5>
                </div>
                <div class="card-body">

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fa fa-check-circle mr-1"></i><?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fa fa-exclamation-circle mr-1"></i><?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('update_account') ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="student_id" value="<?= $this->session->student_id ?>">

                        <!-- Profile Picture -->
                        <div class="form-group text-center mb-4">
                            <label class="d-block mb-2 font-weight-semibold text-muted" style="font-size:0.85rem; letter-spacing:.05em; text-transform:uppercase;">Profile Picture</label>
                            <div class="d-inline-block position-relative">
                                <?php
                                    $pic = $this->session->profile_pic;
                                    $pic_src = ($pic && file_exists(FCPATH . 'uploads/profile_pics/' . $pic))
                                        ? base_url('uploads/profile_pics/' . $pic)
                                        : base_url('assets/user.png');
                                ?>
                                <img id="pic-preview" src="<?= $pic_src ?>" alt="Profile Picture"
                                     class="rounded-circle border"
                                     style="width:110px; height:110px; object-fit:cover; cursor:pointer;"
                                     onclick="document.getElementById('profile_pic').click()"
                                     title="Click to change photo">
                                <span class="position-absolute" style="bottom:4px;right:4px; background:#0069d9; border-radius:50%; width:28px; height:28px; display:flex; align-items:center; justify-content:center; cursor:pointer;"
                                      onclick="document.getElementById('profile_pic').click()">
                                    <i class="fa fa-camera text-white" style="font-size:13px;"></i>
                                </span>
                            </div>
                            <input type="file" name="profile_pic" id="profile_pic" accept="image/*" class="d-none">
                            <div class="text-muted mt-1" style="font-size:0.8rem;">Click the photo to change it</div>
                        </div>

                        <hr class="my-3">

                        <!-- Username -->
                        <div class="form-group">
                            <label for="username" class="font-weight-bold">
                                <i class="fa fa-user mr-1 text-secondary"></i>Username
                            </label>
                            <input type="text" name="username" id="username" class="form-control"
                                   placeholder="Enter new username"
                                   value="<?= htmlspecialchars($this->session->username ?? '') ?>"
                                   required>
                        </div>

                        <!-- Password section -->
                        <div class="form-group mb-1">
                            <label class="font-weight-bold">
                                <i class="fa fa-lock mr-1 text-secondary"></i>Password
                            </label>
                            <small class="text-muted ml-1">(leave blank to keep current)</small>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control"
                                       placeholder="New password">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                                       placeholder="Confirm new password">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="confirm_password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <small id="pw-mismatch" class="text-danger d-none">Passwords do not match.</small>
                        </div>

                        <button type="submit" id="submit-btn" class="btn btn-primary btn-block mt-3">
                            <i class="fa fa-save mr-1"></i>Save Changes
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Live profile picture preview
    document.getElementById('profile_pic').addEventListener('change', function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('pic-preview').src = e.target.result;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Toggle password visibility
    document.querySelectorAll('.toggle-pw').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(this.dataset.target);
            var icon = this.querySelector('i');
            if (target.type === 'password') {
                target.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                target.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // Confirm password match check
    document.getElementById('submit-btn').addEventListener('click', function (e) {
        var pw = document.getElementById('password').value;
        var cpw = document.getElementById('confirm_password').value;
        var msg = document.getElementById('pw-mismatch');
        if (pw && pw !== cpw) {
            e.preventDefault();
            msg.classList.remove('d-none');
        } else {
            msg.classList.add('d-none');
        }
    });
</script>

<?php $this->load->view('footer'); ?>
