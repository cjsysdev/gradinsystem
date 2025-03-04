<?php if (check_flashdata("msg_success")) : ?>
    <?= headToast("success") ?>
    <?= $this->session->flashdata('msg_success') ?>
    <?= footerToast() ?>
<?php elseif (check_flashdata("msg_error")) : ?>
    <?= headToast("danger") ?>
    <?= $this->session->flashdata('msg_error') ?>
    <?= footerToast() ?>
<?php endif; ?>

<script>
    setTimeout(function() {
        $('#liveToast').fadeOut('fast');
    }, 7000);
</script>