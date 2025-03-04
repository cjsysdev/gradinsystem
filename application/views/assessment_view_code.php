<?php $this->load->view('header') ?>
<style>
  /* Default font size */
  .CodeMirror {
    font-size: 14px;
  }

  /* Increase font size on smaller screens */
  @media (max-width: 600px) {
    .CodeMirror {
      font-size: 16px;
    }
  }

  /* Disable touch actions */
  .CodeMirror {
    touch-action: manipulation;
  }
</style>

<div class="container">
  <div class="dashboard">
    <?php $this->load->view('profile_info') ?>
    <p><?= $classwork["description"] ?></p>

    <div class="form-group">
      <form id="code-form" action="<?= base_url('submit_classwork') ?>" method="POST">
        <textarea id="code-editor" name="code" style=" overflow: hidden;" oninput="saveText()"></textarea>
        <input type="text" name="assessment_id" value="<?= $classwork['assessment_id'] ?>" hidden>
        <input type="text" name="student_id" value="<?= $this->session->student_id ?>" hidden>
        <div class="row mt-3">
          <div class="col p-1">
            <button class="btn btn-outline-info btn-block" type="button" onclick="saveText()">Save</button>
          </div>
          <div class="col p-1">
            <button class="btn btn-info btn-block" data-toggle="modal" data-target="#confirmationModal" type="button">Submit</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmationModalLabel">Confirm Save</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            Are you sure you want to submit this code?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="button" id="confirmSave" class="btn btn-info">Confirm</button>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
</div>

<?php $this->load->view('footer') ?>