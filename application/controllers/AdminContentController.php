<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Course content authoring: discussions, interactive topic files under
 * assets/json/, and the AI Worksheet Generator.
 *
 * The topic-file operations themselves live in the Iq_topic_helper library
 * (shared with the assessment screens); prompt building and response
 * validation for the generator live in Worksheet_generator.
 * Split out of AdminController; see Admin_Controller in
 * application/core/MY_Controller.php.
 */
class AdminContentController extends Admin_Controller
{
    public function manage_json_files()
    {
        $this->load->database();

        if ($this->input->post()) {
            $assessment_id = $this->input->post('assessment_id');
            $json_file_path = $this->input->post('json_file_path');

            $this->db->replace('assessment_files', [
                'assessment_id' => $assessment_id,
                'json_file_path' => $json_file_path
            ]);

            $this->session->set_flashdata('success', 'JSON file path updated successfully.');
            redirect('AdminController/manage_json_files');
        }

        $data['assessments'] = $this->db->get('assessment_full')->result_array();
        $data['json_files'] = $this->db->get('assessment_files')->result_array();

        $this->load->view('manage_json_files', $data);
    }

    public function manage_discussions()
    {
        $this->load->model('discussions');

        $filter_class_id = $this->input->get('class_id') ?: '';
        $filter_type      = $this->input->get('type') ?: '';
        $filter_q         = trim($this->input->get('q') ?? '');

        $this->db->select('*')->from('discussions');
        if ($filter_class_id !== '') {
            $this->db->where('class_id', (int) $filter_class_id);
        }
        if ($filter_type === 'static' || $filter_type === 'interactive') {
            $this->db->where('type', $filter_type);
        }
        if ($filter_q !== '') {
            $this->db->group_start()
                ->like('title', $filter_q)
                ->or_like('description', $filter_q)
                ->or_like('link', $filter_q)
                ->group_end();
        }
        $data['discussions'] = $this->db
            ->order_by('class_id', 'asc')
            ->order_by('type', 'asc')
            ->order_by('created_at', 'desc')
            ->get()
            ->result_array();

        $data['selected_class_id'] = $filter_class_id;
        $data['selected_type']     = $filter_type;
        $data['search_q']          = $filter_q;

        $data['classes'] = $this->db->order_by('class_id')->get('classes')->result_array();

        // Build topic list: slug, title, and section count from each JSON file
        $data['json_topics'] = [];
        foreach ($this->_glob_json_topics() as $f) {
            $slug = basename($f, '.json');
            $meta = json_decode(file_get_contents($f), true);
            $data['json_topics'][] = [
                'slug'     => $slug,
                'title'    => $meta['title'] ?? ucwords(str_replace('_', ' ', $slug)),
                'sections' => count($meta['sections'] ?? []),
            ];
        }
        usort($data['json_topics'], function($a, $b) { return strcmp($a['title'], $b['title']); });

        // Static topic files, grouped by subfolder (application/views/discussions/{folder}/*.php)
        $discussions_view_path = APPPATH . 'views/discussions/';
        $data['static_topics'] = [];
        foreach (glob($discussions_view_path . '*', GLOB_ONLYDIR) ?: [] as $dir) {
            $folder = basename($dir);
            $files = [];
            foreach (glob($dir . '/*.php') ?: [] as $f) {
                $base = basename($f, '.php');
                $files[] = [
                    'path'  => "DiscussionController/topic/{$folder}/{$base}",
                    'label' => $base,
                ];
            }
            usort($files, function($a, $b) { return strcmp($a['label'], $b['label']); });
            if ($files) {
                $data['static_topics'][$folder] = $files;
            }
        }
        ksort($data['static_topics']);

        $this->load->view('admin/manage_discussions', $data);
    }

    public function save_discussion()
    {
        $this->load->model('discussions');

        $id       = (int) $this->input->post('id');
        $type     = $this->input->post('type') === 'interactive' ? 'interactive' : 'static';
        $link     = trim($this->input->post('link') ?? '');
        $class_id = (int) $this->input->post('class_id');

        if ($type === 'interactive') {
            if ($this->input->post('json_source') === 'new') {
                $slug = $this->_save_pasted_topic_json(
                    $class_id,
                    trim($this->input->post('new_slug') ?? ''),
                    $this->input->post('json_text') ?? ''
                );
                if ($slug === false) {
                    redirect('AdminController/manage_discussions');
                    return;
                }
                $link = $slug;
            } else {
                // Existing topic — the link field holds the slug — strip any accidental path prefix
                $link = preg_replace('/[^a-z0-9_]/', '', strtolower($link));
            }
        }

        $row = [
            'class_id'     => $class_id,
            'type'         => $type,
            'title'        => trim($this->input->post('title')),
            'description'  => trim($this->input->post('description') ?? ''),
            'link'         => $link,
            'display_date' => $this->input->post('display_date') ?: null,
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            // MY_Model::update() takes (data, where) — NOT (where, data).
            $this->discussions->update($row, $id);
            $this->session->set_flashdata('success', 'Discussion updated.');
        } else {
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->discussions->insert($row);
            $this->session->set_flashdata('success', 'Discussion added.');
        }

        redirect('AdminController/manage_discussions');
    }

    public function delete_discussion($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('AdminController/manage_discussions');
            return;
        }

        $this->load->model('discussions');
        $this->discussions->delete((int) $id);
        $this->session->set_flashdata('success', 'Discussion deleted.');
        redirect('AdminController/manage_discussions');
    }

    public function worksheet_generator()
    {
        $data['schedules'] = $this->class_schedule->get_all_active();
        $this->load->view('admin/worksheet_generator', $data);
    }

    // Populates the assessment picker for a chosen course/section — used by
    // the "From existing assessment" source mode on quiz_from_worksheet, so
    // the instructor can ground a generated quiz in real classwork instead of
    // hand-pasting JSON.
    public function worksheet_assessments_for_schedule()
    {
        header('Content-Type: application/json');

        $schedule_id = (int) $this->input->post('schedule_id');
        if (!$schedule_id) {
            echo json_encode(['ok' => false, 'error' => 'Missing schedule_id.']);
            return;
        }

        $rows = $this->assessments->get_all_for_admin(['schedule_id' => $schedule_id]);
        $out  = [];
        foreach ($rows as $r) {
            $out[] = [
                'assessment_id'    => (int) $r['assessment_id'],
                'title'            => $r['title'],
                'iotype'           => $r['iotype'] ?? '',
                'term'             => $r['term'] ?? '',
                'submission_count' => (int) ($r['submission_count'] ?? 0),
            ];
        }

        echo json_encode(['ok' => true, 'assessments' => $out]);
    }

    // Compiles a text "source" block from an existing assessment (title,
    // description, widget config) and, optionally, an anonymized sample of
    // student classwork submissions — so a generated quiz can be grounded in
    // content students actually worked with. Never forwards student names or
    // trans_no to the model; classworks::get_all_submissions() rows are
    // stripped down to just submission content + score before use.
    public function worksheet_source_from_assessment()
    {
        header('Content-Type: application/json');

        $assessment_ids       = $this->input->post('assessment_ids');
        $assessment_ids       = is_array($assessment_ids) ? array_values(array_unique(array_filter(array_map('intval', $assessment_ids)))) : [];
        $include_submissions  = (bool) $this->input->post('include_submissions');

        if (empty($assessment_ids)) {
            echo json_encode(['ok' => false, 'error' => 'Select at least one assessment.']);
            return;
        }

        $blocks = [];
        $titles = [];

        foreach ($assessment_ids as $assessment_id) {
            $row = $this->db->select('a.assessment_id, a.title, a.description, a.given, a.term, cl.class_code, cl.class_name, cs.section, w.name AS widget_name')
                ->from('assessment_full a')
                ->join('class_schedule cs', 'cs.schedule_id = a.schedule_id')
                ->join('classes cl', 'cl.class_id = cs.class_id')
                ->join('widgets w', 'w.widget_id = a.widget_id', 'left')
                ->where('a.assessment_id', $assessment_id)
                ->get()->row_array();

            if (!$row) {
                continue;
            }

            $titles[] = $row['title'];

            $lines   = [];
            $lines[] = '=== Assessment: ' . $row['title'] . ' ===';
            $lines[] = 'Course: ' . $row['class_code'] . ' - ' . $row['class_name'] . ', Section ' . $row['section'] . ' (' . $row['term'] . ')';
            if (!empty($row['widget_name'])) {
                $lines[] = 'Activity type: ' . $row['widget_name'];
            }
            if (!empty($row['description'])) {
                $lines[] = "Description:\n" . $row['description'];
            }
            if (!empty($row['given'])) {
                $given_decoded = json_decode($row['given'], true);
                $given_pretty  = ($given_decoded !== null) ? json_encode($given_decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $row['given'];
                $lines[]       = "Assessment content/config:\n" . $given_pretty;
            }

            if ($include_submissions) {
                $subs   = $this->classworks->get_all_submissions($assessment_id);
                $sample = array_slice($subs, 0, 15);
                if (!empty($sample)) {
                    $lines[] = "Sample student submissions (anonymized, for grounding only):";
                    $n = 0;
                    foreach ($sample as $s) {
                        $content = trim((string) ($s['code'] ?? ''));
                        if ($content === '') continue;
                        $n++;
                        $decoded_content = json_decode($content, true);
                        $pretty_content  = ($decoded_content !== null) ? json_encode($decoded_content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $content;
                        $pretty_content  = mb_substr($pretty_content, 0, 2000);
                        $lines[]         = "--- Submission #{$n} ---\n" . $pretty_content;
                    }
                } else {
                    $lines[] = "(No student submissions yet for this assessment.)";
                }
            }

            $blocks[] = implode("\n\n", $lines);
        }

        if (empty($blocks)) {
            echo json_encode(['ok' => false, 'error' => 'None of the selected assessments could be found.']);
            return;
        }

        $combined_title = implode(' & ', $titles);
        if (mb_strlen($combined_title) > 120) {
            $combined_title = mb_substr($combined_title, 0, 117) . '…';
        }

        echo json_encode(['ok' => true, 'title' => $combined_title, 'source' => implode("\n\n\n", $blocks)]);
    }

    public function worksheet_generate()
    {
        header('Content-Type: application/json');

        // Larger requests (e.g. a 60-question quiz) take Claude noticeably
        // longer to generate than the default script/cURL timeouts allow for.
        set_time_limit(240);

        $this->load->library(['anthropic_client', 'worksheet_generator']);

        $type   = $this->input->post('type');
        $topic  = trim((string) $this->input->post('topic'));
        $params = [
            'course'      => trim((string) $this->input->post('course')),
            'count'       => max(1, min(60, (int) $this->input->post('count'))) ?: 5,
            'duration'    => trim((string) $this->input->post('duration')),
        ];
        $source       = trim((string) $this->input->post('source'));
        $requirements = trim((string) $this->input->post('requirements'));

        if ($topic === '' && $type !== 'quiz_from_worksheet') {
            echo json_encode(['ok' => false, 'error' => 'Topic is required.']);
            return;
        }

        if ($type === 'quiz_from_worksheet' && $source === '') {
            echo json_encode(['ok' => false, 'error' => 'Provide source content: pick an existing assessment or paste worksheet JSON to generate a quiz from.']);
            return;
        }

        $prompt = $this->worksheet_generator->prompt($type, $topic, $params, $source);
        if ($prompt === null) {
            echo json_encode(['ok' => false, 'error' => 'Unknown output type.']);
            return;
        }
        list($system, $user) = $prompt;

        if ($requirements !== '') {
            $user .= "\n\nAdditional requirements from the instructor (follow these carefully, but never deviate from the required JSON shape above):\n{$requirements}";
        }

        // Scale the output budget with how many items were requested — a flat
        // 8000 truncates well before 60 quiz questions or 60 discussion
        // sections finish generating. ~350 tokens/item covers even the more
        // verbose types (lab_worksheet HTML instructions, discussion lesson
        // HTML), floored at 6000 for small requests, capped at 32000 (Opus
        // 4.8 supports up to 128K but this is a synchronous, non-streaming
        // cURL call — keep it well under the 240s time limit above).
        $max_tokens = min(32000, max(6000, $params['count'] * 350 + 2000));

        $result = $this->anthropic_client->generate($system, $user, $max_tokens);

        if (!$result['ok']) {
            echo json_encode(['ok' => false, 'error' => $result['error']]);
            return;
        }

        $json_text = $this->worksheet_generator->strip_fences($result['text']);
        $data = json_decode($json_text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['ok' => false, 'error' => 'Model did not return valid JSON: ' . json_last_error_msg(), 'raw' => $json_text]);
            return;
        }

        $validation_error = $this->worksheet_generator->validate($type, $data);

        if ($validation_error) {
            echo json_encode(['ok' => false, 'error' => 'Generated JSON has an invalid shape: ' . $validation_error, 'raw' => $json_text]);
            return;
        }

        $pretty = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $preview_html = '';
        if ($type === 'lab_worksheet') {
            $preview_html = $this->load->view('widgets/lab_worksheet', ['config' => $data, 'readonly' => true, 'existing' => null], true);
        } elseif ($type === 'worksheet_table') {
            $config = $data;
            $preview_html = $this->load->view('widgets/worksheet', ['config' => $config, 'readonly' => true, 'existing' => null], true);
        } elseif ($type === 'discussion') {
            $preview_html = $this->load->view('admin/_worksheet_preview', ['mode' => 'discussion', 'data' => $data], true);
        } elseif ($type === 'quiz_from_worksheet') {
            $preview_html = $this->load->view('admin/_worksheet_preview', ['mode' => 'quiz', 'data' => $data], true);
        }

        echo json_encode(['ok' => true, 'json' => $pretty, 'preview_html' => $preview_html]);
    }
}
