<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ProjectLogController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['online'])) {
            redirect('login');
        }
        $this->load->model(['Project_log_model', 'Grouping_model', 'Group_member_model']);
    }

    // Student view: running project log for one of the student's courses.
    public function index($class_id = null)
    {
        $student_id = $this->session->student_id;

        // Only courses whose project log has actually been set up (a grouping
        // set designated in project_log_groupings) — same list the nav bar
        // uses to decide whether to show the Project Log button at all.
        $courses = $this->Project_log_model->get_designated_courses_for_student($student_id);

        // Only allow one of those courses; anything else falls back to the
        // first one rather than rendering a half-empty page.
        $selected = null;
        foreach ($courses as $c) {
            if ((int) $c['class_id'] === (int) $class_id) {
                $selected = $c;
                break;
            }
        }
        if ($selected === null && !empty($courses)) {
            $selected = $courses[0];
        }

        $group_ctx = $selected ? $this->_resolve_group($student_id, $selected['class_id']) : ['mode' => 'individual'];

        // The board is not paged: a kanban whose columns only hold page 1 shows
        // a false picture of the project (a "Blocked" column can read empty
        // just because its cards fell on page 2). The whole log is loaded and
        // each column scrolls on its own instead.
        $entries = [];
        if ($group_ctx['mode'] === 'group') {
            $entries = $this->Project_log_model->get_by_group($group_ctx['group']['group_id']);
        } elseif ($selected) {
            $entries = $this->Project_log_model->get_by_student_class($student_id, $selected['class_id']);
        }

        // Bucket once here rather than re-filtering the list per column in the
        // view. clean_status() folds any legacy/blank status into the default
        // column so a row can never vanish off the board.
        $statuses = Project_log_model::statuses();
        $board    = array_fill_keys(array_keys($statuses), []);
        foreach ($entries as $e) {
            $board[Project_log_model::clean_status($e['status'])][] = $e;
        }

        $data = [
            'courses'     => $courses,
            'selected'    => $selected,
            'selected_id' => $selected ? (int) $selected['class_id'] : null,
            'entries'     => $entries,
            'board'       => $board,
            'statuses'    => $statuses,
            'mode'        => $group_ctx['mode'],
            'group'       => $group_ctx['group'] ?? null,
            'members'     => $group_ctx['members'] ?? [],
            'total'       => count($entries),
        ];

        $this->load->view('project_log', $data);
    }

    // AJAX — move a card to another column on the kanban board. Answers JSON;
    // the board reverts the card to where it came from on anything but
    // success, so a rejected move never lies about what was saved.
    public function set_status($log_id)
    {
        header('Content-Type: application/json');

        $student_id = $this->session->student_id;
        $status     = (string) $this->input->post('status');

        // Not _clean_status(): that coerces an unknown value to 'planned', and
        // a dropped card silently resetting to Planned would be worse than a
        // refused move.
        if (!in_array($status, Project_log_model::status_keys(), true)) {
            echo json_encode(['success' => false, 'error' => 'Unknown status.']);
            return;
        }

        // get_one() is ownership-scoped, so this also rejects moving a
        // teammate's card on a shared team board.
        if (!$this->Project_log_model->get_one($log_id, $student_id)) {
            echo json_encode(['success' => false, 'error' => 'Entry not found.']);
            return;
        }

        $this->Project_log_model->set_status($log_id, $student_id, $status);
        echo json_encode(['success' => true, 'status' => $status]);
    }

    public function save()
    {
        $student_id = $this->session->student_id;
        $class_id   = (int) $this->input->post('class_id');
        $title      = trim((string) $this->input->post('title'));

        if (empty($title) || empty($class_id)) {
            $this->session->set_flashdata('error', 'Title and course are required.');
            redirect('project_log/' . $class_id);
            return;
        }

        if (!$this->_owns_course($student_id, $class_id)) {
            $this->session->set_flashdata('error', 'You are not enrolled in that course.');
            redirect('project_log');
            return;
        }

        $group_ctx = $this->_resolve_group($student_id, $class_id);
        if ($group_ctx['mode'] === 'ungrouped') {
            $this->session->set_flashdata('error', 'Your instructor set up teams for this course, but you are not on a team yet.');
            redirect('project_log/' . $class_id);
            return;
        }

        $data = [
            'student_id'  => $student_id,
            'class_id'    => $class_id,
            'group_id'    => $group_ctx['mode'] === 'group' ? $group_ctx['group']['group_id'] : null,
            'title'       => $title,
            'description' => $this->input->post('description') ?: null,
            'status'      => $this->_clean_status($this->input->post('status')),
            'link'        => $this->input->post('link') ?: null,
            'code'        => $this->input->post('code') ?: null,
            'file_upload' => $this->_handle_upload($class_id),
        ];

        $this->Project_log_model->create($data);
        $this->session->set_flashdata('success', 'Progress entry added.');
        redirect('project_log/' . $class_id);
    }

    public function update($log_id)
    {
        $student_id = $this->session->student_id;
        $existing   = $this->Project_log_model->get_one($log_id, $student_id);

        if (!$existing) {
            $this->session->set_flashdata('error', 'Entry not found.');
            redirect('project_log');
            return;
        }

        $title = trim((string) $this->input->post('title'));
        if (empty($title)) {
            $this->session->set_flashdata('error', 'Title is required.');
            redirect('project_log/' . $existing['class_id']);
            return;
        }

        $data = [
            'title'       => $title,
            'description' => $this->input->post('description') ?: null,
            'status'      => $this->_clean_status($this->input->post('status')),
            'link'        => $this->input->post('link') ?: null,
            'code'        => $this->input->post('code') ?: null,
            'updated_at'  => date('Y-m-d H:i:s'),
        ];

        // Only overwrite the file when a new one is uploaded.
        $new_file = $this->_handle_upload($existing['class_id']);
        if ($new_file !== null) {
            $data['file_upload'] = $new_file;
        }

        $this->Project_log_model->update_entry($log_id, $student_id, $data);
        $this->session->set_flashdata('success', 'Progress entry updated.');
        redirect('project_log/' . $existing['class_id']);
    }

    public function delete($log_id)
    {
        $student_id = $this->session->student_id;
        $existing   = $this->Project_log_model->get_one($log_id, $student_id);

        if (!$existing) {
            $this->session->set_flashdata('error', 'Entry not found.');
            redirect('project_log');
            return;
        }

        $this->Project_log_model->delete_entry($log_id, $student_id);
        $this->session->set_flashdata('success', 'Progress entry removed.');
        redirect('project_log/' . $existing['class_id']);
    }

    // One-time (idempotent) schema setup — run once as admin.
    // Confirmation + pre-flight backup: see Schema_guard.
    public function install()
    {
        if ($this->session->userdata('role') !== 'admin') {
            redirect('login');
            return;
        }

        $this->load->library('schema_guard');
        $tables = ['project_logs', 'project_log_groupings'];

        if (!$this->schema_guard->confirmed('Project log tables setup', 'ProjectLogController/install', $tables)) {
            return;
        }

        $backup = $this->schema_guard->backup($tables, 'project_log');
        $this->Project_log_model->install();

        $this->session->set_flashdata('success',
            'Project log table ready.' . ($backup ? ' Backup written to ' . basename($backup) . '.' : ''));
        redirect('project_log');
    }

    // ── helpers ─────────────────────────────────────────────────────────────

    // Resolves whether this course's project log is group-scoped for this
    // student. Mirrors GroupWorkController::_resolve()'s chain, but keyed off
    // project_log_groupings (a class can map to several sets, one per
    // section) instead of the assessment_groupings 1:1 lookup.
    //   - 'individual': no set designated for this course — current behavior.
    //   - 'group':      student belongs to a group in a designated set.
    //   - 'ungrouped':  a set is designated but the student isn't placed yet.
    private function _resolve_group($student_id, $class_id)
    {
        $set_ids = $this->Project_log_model->get_set_ids_for_class($class_id);
        if (empty($set_ids)) {
            return ['mode' => 'individual'];
        }

        foreach ($set_ids as $set_id) {
            $group = $this->Grouping_model->get_student_group($student_id, $set_id);
            if ($group) {
                return [
                    'mode'    => 'group',
                    'group'   => $group,
                    'members' => $this->Group_member_model->get_members_by_group($group['group_id']),
                ];
            }
        }

        return ['mode' => 'ungrouped'];
    }

    private function _owns_course($student_id, $class_id)
    {
        foreach ($this->Project_log_model->get_courses_for_student($student_id) as $c) {
            if ((int) $c['class_id'] === (int) $class_id) {
                return true;
            }
        }
        return false;
    }

    // The status list lives on the model — see Project_log_model::STATUSES.
    private function _clean_status($status)
    {
        return Project_log_model::clean_status($status);
    }

    // Returns the stored filename on a successful upload, or null when no file
    // was submitted. Flashes + redirects on a genuine upload error.
    private function _handle_upload($class_id)
    {
        if (empty($_FILES['file_upload']['name'])) {
            return null;
        }

        $config['upload_path']   = './uploads/project_logs';
        $config['allowed_types'] = '*';
        $config['max_size']      = 51200; // 50MB
        $config['file_name']     = $this->session->student_id . '-' . time();

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file_upload')) {
            $this->session->set_flashdata('error', $this->upload->display_errors());
            redirect('project_log/' . $class_id);
            return null;
        }

        return $this->upload->data('file_name');
    }
}
