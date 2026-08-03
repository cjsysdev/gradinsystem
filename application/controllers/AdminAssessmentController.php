<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Creating, editing, assigning and deleting assessments.
 *
 * The master/section split matters throughout: content (title, description,
 * max_score, term, widget, given) lives on the shared `assessments` master and
 * propagates to every section, while due/status/is_groupings belong to the one
 * `assessment_section` row being edited. Ids in URLs are always section ids.
 * Split out of AdminController; see Admin_Controller in
 * application/core/MY_Controller.php.
 */
class AdminAssessmentController extends Admin_Controller
{
    public function manage_assessments()
    {
        $this->load->library('pagination');

        $schedule_id = $this->input->get('schedule_id');
        // No schedule_id in the query string at all (first page load, not an
        // explicit "All Sections" pick) — default the filter to whichever
        // class is scheduled right now, same as all_submissions().
        if ($schedule_id === null) {
            $day = date('D');
            $current_class = $this->class_schedule->class_today($day);
            $schedule_id = $current_class['schedule_id'] ?? null;
        }

        // Search + filter set — applied identically to the list, the pager total,
        // and the bulk-action id set (see assessments::_admin_filters_sql()).
        $filters = [
            'schedule_id' => $schedule_id ?: null,
            'q'           => trim($this->input->get('q') ?? ''),
            'iotype_id'   => $this->input->get('iotype_id') ?: null,
            'term'        => $this->input->get('term') ?: null,
            'status'      => ($this->input->get('status') !== null && $this->input->get('status') !== '') ? $this->input->get('status') : '',
            'submission'  => $this->input->get('submission') ?: '',
        ];

        $per_page = 20;
        $offset   = (int) $this->input->get('per_page');
        $total    = $this->assessments->count_all_for_admin($filters);

        // Preserve every active filter across page links.
        $qs = [];
        foreach (['schedule_id', 'q', 'iotype_id', 'term', 'status', 'submission'] as $k) {
            if ($filters[$k] !== null && $filters[$k] !== '') $qs[] = $k . '=' . urlencode($filters[$k]);
        }
        $base_url = base_url('manage_assessments') . ($qs ? '?' . implode('&', $qs) : '');

        $config = [
            'base_url'             => $base_url,
            'total_rows'           => $total,
            'per_page'             => $per_page,
            'page_query_string'    => TRUE,
            'query_string_segment' => 'per_page',
            'reuse_query_string'   => TRUE,
            'use_page_numbers'     => FALSE,
            'full_tag_open'        => '<ul class="pagination pagination-sm mb-0">',
            'full_tag_close'       => '</ul>',
            'first_link'           => '&laquo;',
            'first_tag_open'       => '<li class="page-item">',
            'first_tag_close'      => '</li>',
            'last_link'            => '&raquo;',
            'last_tag_open'        => '<li class="page-item">',
            'last_tag_close'       => '</li>',
            'next_link'            => '&rsaquo;',
            'next_tag_open'        => '<li class="page-item">',
            'next_tag_close'       => '</li>',
            'prev_link'            => '&lsaquo;',
            'prev_tag_open'        => '<li class="page-item">',
            'prev_tag_close'       => '</li>',
            'num_tag_open'         => '<li class="page-item">',
            'num_tag_close'        => '</li>',
            'cur_tag_open'         => '<li class="page-item active"><a class="page-link" href="#">',
            'cur_tag_close'        => '</a></li>',
            'attributes'           => ['class' => 'page-link'],
            'num_links'            => 4,
        ];
        $this->pagination->initialize($config);

        $rows = $this->assessments->get_all_for_admin($filters, $per_page, $offset);
        // Rows are pre-sorted by master_id (see get_all_for_admin()) so every
        // section sharing an assessment lands on consecutive rows within this
        // page. Mark the first row of each run with _rowspan = run length —
        // the view uses it to merge the shared content cells (title/type/
        // widget/term/max score) across the group with a single <td rowspan>
        // instead of repeating them per section.
        $prev_master = null;
        $group_start = null;
        foreach ($rows as $i => $row) {
            if ($row['master_id'] !== $prev_master) {
                if ($group_start !== null) {
                    $rows[$group_start]['_rowspan'] = $i - $group_start;
                }
                $group_start = $i;
                $prev_master = $row['master_id'];
            }
        }
        if ($group_start !== null) {
            $rows[$group_start]['_rowspan'] = count($rows) - $group_start;
        }

        $data['assessments']         = $rows;
        $data['all_assessment_ids']  = $this->assessments->get_all_ids_for_admin($filters);
        $data['pagination']          = $this->pagination->create_links();
        $data['total']               = $total;
        $data['per_page']            = $per_page;
        $data['offset']              = $offset;
        $data['schedules'] = $this->class_schedule->get_all_active();
        $data['io_types'] = $this->db->get('io_type')->result_array();
        $data['selected_schedule']   = $schedule_id;
        $data['search_q']            = $filters['q'];
        $data['selected_iotype']     = $filters['iotype_id'];
        $data['selected_term']       = $filters['term'];
        $data['selected_status']     = $filters['status'];
        $data['selected_submission'] = $filters['submission'];

        // Distinct classes behind those active sections, for the "Entire Class" apply mode.
        $seen_classes = [];
        foreach ($data['schedules'] as $s) {
            $seen_classes[$s['class_id']] = ['class_id' => $s['class_id'], 'class_code' => $s['class_code'], 'class_name' => $s['class_name']];
        }
        $data['classes'] = array_values($seen_classes);

        $this->load->model('Grouping_model');
        $data['grouping_sets'] = $this->Grouping_model->get_all_sets();

        $this->load->model('Widgets_model');
        $data['widgets'] = $this->Widgets_model->get_all();

        $data['copyable_assessments']  = $this->assessments->get_copyable_for_active_semester();
        $data['assignable_masters']    = $this->assessments->get_assignable_masters();

        // Topics available to the topic-file widgets — the lesson+quiz format
        // InteractiveQuizController::discussion() renders (sections[].quiz) and
        // the microlearning format micro() renders (sections[].chunks), but not
        // the multi-question sections[].questions format used by the older
        // topics/analytics flow.
        $data['iq_topics'] = [];
        // Question count per topic — the modal JS auto-fills Max Score from
        // this when a topic is picked, and save_assessment() re-derives it
        // server-side as the source of truth.
        $data['iq_topic_question_counts'] = [];
        // 'discussion' | 'micro' per topic — the modal JS uses it to offer only
        // the topics the selected widget's renderer can actually handle (a
        // micro topic's arrange/type checkpoints would fail the discussion
        // template's validator, and vice versa).
        $data['iq_topic_formats'] = [];
        // Class code per topic (its assets/json/{CLASS_CODE}/ folder, '' for
        // legacy/unfiled root files) — the modal JS filters the Topic dropdown
        // to the section/class selected above so admins can't pick a topic
        // that belongs to a different course.
        $data['iq_topic_classes'] = [];
        // Title/description straight from the topic JSON, keyed by slug — the
        // modal JS auto-fills the assessment's Title/Description fields from
        // this when a topic is picked, same as it does for Max Score.
        $data['iq_topic_meta'] = [];
        foreach ($this->_glob_json_topics() as $file) {
            $meta = json_decode(file_get_contents($file), true);
            if (!$meta || empty($meta['sections'])) {
                continue;
            }
            $is_discussion_format = true;
            foreach ($meta['sections'] as $s) {
                if (isset($s['questions'])) {
                    $is_discussion_format = false;
                    break;
                }
            }
            if ($is_discussion_format) {
                $slug = basename($file, '.json');
                $title = $meta['title'] ?? ucwords(str_replace('_', ' ', $slug));
                $format = $this->_iq_topic_format($meta);
                $data['iq_topics'][$slug] = $title;
                $data['iq_topic_formats'][$slug] = $format;
                $data['iq_topic_question_counts'][$slug] = $format === 'micro'
                    ? $this->_count_micro_topic_items($meta)
                    : $this->_count_iq_topic_questions($meta);
                $data['iq_topic_classes'][$slug] = $this->_topic_class_code_from_path($file);
                $data['iq_topic_meta'][$slug] = [
                    'title'       => $title,
                    'description' => $meta['description'] ?? '',
                ];
            }
        }

        $this->load->view('admin/manage_assessments', $data);
    }

    // $assessment_id posted here is always a SECTION id (assessment_section_id)
    // — the id space consumers see everywhere (URLs, classworks.assessment_id,
    // etc.) never changed across the master/assessment_section split (see
    // Assessment_normalize_model). Content (title/description/max_score/term/
    // widget/given) lives on the shared master; editing it updates every
    // section sharing that master. Per-section fields (due/status/
    // is_groupings) only ever touch the one section being edited.
    public function save_assessment()
    {
        $post = $this->input->post();
        $section_id = !empty($post['assessment_id']) ? (int)$post['assessment_id'] : null;
        $apply_mode = $post['apply_mode'] ?? 'section';

        $status = isset($post['status']) ? $post['status'] : 0;
        if ($status === 'open' || $status === 'closed') {
            $status = $status === 'open' ? '1' : '0';
        }

        $master_fields = [
            'iotype_id'   => $post['iotype_id'],
            'title'       => $post['title'],
            'description' => $post['description'],
            'max_score'   => $post['max_score'],
            'term'        => $post['term'],
            'widget_id'   => !empty($post['widget_id']) ? (int) $post['widget_id'] : null,
            'given'       => !empty($post['widget_id']) ? ($post['given'] ?? null) : null,
        ];

        // Interactive Discussion/Quiz: max_score isn't hand-entered — it's the
        // number of questions in the chosen topic (one per section.quiz), so a
        // student's raw quiz score (1 point per correct answer) always lines
        // up with the assessment's own max. Derived server-side, not trusted
        // from the posted "Max Score" field, since the modal JS's auto-fill
        // could be stale (e.g. topic file edited after the form loaded).
        if ($master_fields['widget_id']) {
            $this->load->model('Widgets_model');
            $widget = $this->Widgets_model->get($master_fields['widget_id']);
            if ($widget && in_array($widget['widget_key'], ['iq_discussion', 'iq_micro'], true)) {
                $is_micro = $widget['widget_key'] === 'iq_micro';

                // "Paste new JSON": write the file first so the lookup below
                // finds it and derives max_score the same way it would for any
                // other topic. A validation/collision failure here has already
                // flashed its own error.
                $pasted = $this->_resolve_iq_paste($post, $widget);
                if ($pasted === false) {
                    redirect('manage_assessments' . (!empty($post['schedule_id']) ? '?schedule_id=' . $post['schedule_id'] : ''));
                    return;
                }
                if ($pasted) {
                    $master_fields['given'] = json_encode(['topic' => $pasted]);
                }

                $topic = json_decode($master_fields['given'] ?? '', true)['topic'] ?? '';
                $topic_found = false;
                $wrong_format = false;
                $topic_meta = [];
                if ($topic) {
                    foreach ($this->_glob_json_topics() as $file) {
                        if (basename($file, '.json') !== $topic) {
                            continue;
                        }
                        $meta = json_decode(file_get_contents($file), true) ?: [];
                        // A topic authored for the other renderer would 422 the
                        // moment a student opened it, so reject it at save time.
                        if (($this->_iq_topic_format($meta) === 'micro') !== $is_micro) {
                            $wrong_format = true;
                            break;
                        }
                        $master_fields['max_score'] = max(1, $is_micro
                            ? $this->_count_micro_topic_items($meta)
                            : $this->_count_iq_topic_questions($meta));
                        $topic_found = true;
                        $topic_meta = $meta;
                        break;
                    }
                }
                if ($wrong_format) {
                    $this->session->set_flashdata('error', $is_micro
                        ? 'Microlearning Quiz needs a topic in the microlearning format (sections with "chunks") — the selected topic is a plain lesson+quiz topic, so use the Interactive Discussion/Quiz widget for it.'
                        : 'Interactive Discussion/Quiz needs a plain lesson+quiz topic — the selected topic is in the microlearning format (sections with "chunks"), so use the Microlearning Quiz widget for it.');
                    redirect('manage_assessments' . (!empty($post['schedule_id']) ? '?schedule_id=' . $post['schedule_id'] : ''));
                    return;
                }
                if (!$topic_found) {
                    $this->session->set_flashdata('error', $widget['name'] . ' needs a topic — pick one from the Topic dropdown (the selected topic file could not be found).');
                    redirect('manage_assessments' . (!empty($post['schedule_id']) ? '?schedule_id=' . $post['schedule_id'] : ''));
                    return;
                }

                // Topic files carry their own title/description — fill them in
                // for an admin who left those fields blank. max_score is
                // dropped: it was just derived from the topic's own item count
                // above, which always wins over anything the file claims.
                $found_meta = $this->_widget_config_meta($topic_meta);
                unset($found_meta['max_score']);
                $this->_fill_blank_fields($master_fields, $found_meta);
            } elseif ($widget) {
                // All other widgets keep their config as a JSON string in
                // assessments.given (the standard — see CLAUDE.md). Reject
                // invalid/empty JSON here instead of storing it silently and
                // only breaking when a student opens the assessment.
                $given = trim((string) ($master_fields['given'] ?? ''));
                $config = $given !== '' ? json_decode($given, true) : null;
                if (!is_array($config) || empty($config)) {
                    if ($given === '') {
                        $reason = 'an empty config';
                    } elseif (json_last_error() !== JSON_ERROR_NONE) {
                        $reason = 'invalid JSON (' . json_last_error_msg() . ')';
                    } else {
                        $reason = 'JSON that is not an object';
                    }
                    $this->session->set_flashdata('error', 'Widget config not saved — "' . $widget['name'] . '" needs a JSON config, but the form contained ' . $reason . '.');
                    redirect('manage_assessments' . (!empty($post['schedule_id']) ? '?schedule_id=' . $post['schedule_id'] : ''));
                    return;
                }

                // Quiz widgets accept a bare list of questions [ {...}, {...} ] as
                // well as the canonical { "questions":[...] }; canonicalize on save
                // so the stored config is always the object shape every reader (and
                // the visual builder) expects. Already-correct configs are untouched.
                if (in_array($widget['widget_key'], ['quiz', 'secure_quiz'], true) && !isset($config['questions'])) {
                    $master_fields['given'] = json_encode(['questions' => $this->Widgets_model->quiz_questions($config)]);
                }

                // Same idea as the topic branch above: a config JSON that names
                // itself (title/description/max_score) fills whichever of those
                // form fields the admin left blank.
                $this->_fill_blank_fields($master_fields, $this->_widget_config_meta($config));
            }
        }

        $auto_create = !empty($post['auto_create_submissions']);
        $section_fields = [
            'due'          => $post['due'],
            'status'       => (int) $status,
            'is_groupings' => !empty($post['is_groupings']) ? 1 : 0,
        ];

        // "Entire class" mode is shared by manage_assessments.php and
        // class_assessments.php's Add modals — class_assessments.php posts
        // this hidden field so the redirect lands back on the class it came
        // from instead of the section-scoped manage_assessments page.
        $class_return_url = !empty($post['return_class_id'])
            ? 'class_assessments?class_id=' . (int) $post['return_class_id']
            : 'manage_assessments';

        // "Draft" anchors a master to a class with NO section assigned yet —
        // the class_assessments page's way of authoring an assessment before
        // deciding which section(s) get it. Unlike "class" mode below, this
        // never touches assessment_section; the master persists indefinitely
        // as an unassigned draft (see assessments::delete_section()'s
        // class_id guard) until assigned via assign_master() or explicitly
        // deleted.
        if (!$section_id && $apply_mode === 'draft' && !empty($post['class_id'])) {
            $master_fields['class_id'] = (int) $post['class_id'];
            $this->assessments->create_master($master_fields);

            $this->session->set_flashdata('success', 'Draft assessment created — not yet assigned to any section.');
            redirect('class_assessments?class_id=' . (int) $post['class_id']);
        }

        // "Entire class" creates ONE shared master, assigned to every section
        // of that class in the target semester, instead of a full duplicate
        // row per section. Only offered for brand-new assessments — an
        // existing assessment is already tied to a master. Group Submission
        // isn't offered in this mode since grouping sets are section-scoped
        // (see manage_assessments.php JS). class_assessments.php posts its
        // own selected semester_id so this fans out to whichever semester the
        // admin is viewing; manage_assessments.php doesn't post one, so this
        // falls back to the truly active semester (unchanged behavior there).
        if (!$section_id && $apply_mode === 'class' && !empty($post['class_id'])) {
            $schedules = $this->class_schedule->get_active_schedules_by_class(
                (int) $post['class_id'],
                !empty($post['semester_id']) ? (int) $post['semester_id'] : null
            );
            if (empty($schedules)) {
                $this->session->set_flashdata('error', 'That class has no sections in that semester.');
                redirect($class_return_url);
            }

            $master_fields['class_id'] = (int) $post['class_id'];
            $master_id = $this->assessments->create_master($master_fields);

            $created_count = 0;
            $submissions_created = 0;
            foreach ($schedules as $sched) {
                $new_section_id = $this->assessments->assign_to_schedule($master_id, $sched['schedule_id'], [
                    'due'          => $post['due'],
                    'status'       => (int) $status,
                    'is_groupings' => 0,
                ]);
                $created_count++;

                if ($auto_create) {
                    $submissions_created += $this->classworks->create_blank_for_schedule($new_section_id, $sched['schedule_id']);
                }
            }

            $flash = "Created 1 assessment, assigned to $created_count section(s).";
            if ($auto_create) {
                $flash .= " Created $submissions_created blank submission(s) across those sections.";
            }
            $this->session->set_flashdata('success', $flash);
            redirect($class_return_url);
        }

        if ($section_id) {
            $master_id = $this->assessments->master_id_for_section($section_id);
            if (!$master_id) {
                $this->session->set_flashdata('error', 'Assessment not found — please try again.');
                redirect('manage_assessments');
                return;
            }
            // Backfill class_id for masters created before save_assessment()
            // started populating it (or via the old flow) — never overwrite
            // an already-set class_id just because a section got re-pointed.
            $existing_class_id = $this->db->select('class_id')->where('assessment_id', $master_id)->get('assessments')->row('class_id');
            if (!$existing_class_id) {
                $master_fields['class_id'] = $this->db->select('class_id')->where('schedule_id', $post['schedule_id'])->get('class_schedule')->row('class_id');
            }
            // Content edits propagate to every section sharing this master —
            // that's the point of sharing (see CLAUDE.md widget config rule).
            // schedule_id is included here too: the modal's Section dropdown
            // stays editable on Edit (re-pointing a single section is a
            // supported correction), guarded by the same UNIQUE(assessment_id,
            // schedule_id) constraint that stops "class" mode from double-
            // assigning a section.
            $this->assessments->update_master($master_id, $master_fields);
            $this->assessments->update_section($section_id, $section_fields + ['schedule_id' => $post['schedule_id']]);
            $flash = 'Assessment updated successfully.';
        } else {
            $master_fields['class_id'] = $this->db->select('class_id')->where('schedule_id', $post['schedule_id'])->get('class_schedule')->row('class_id');
            $master_id = $this->assessments->create_master($master_fields);
            $section_id = $this->assessments->assign_to_schedule($master_id, $post['schedule_id'], $section_fields);
            $flash = 'Assessment added successfully.';
        }

        $grouping_set_id = !empty($post['grouping_set_id']) ? (int) $post['grouping_set_id'] : null;
        $this->db->where('assessment_id', $section_id)->delete('assessment_groupings');
        if ($section_fields['is_groupings'] && $grouping_set_id) {
            $this->db->insert('assessment_groupings', [
                'assessment_id' => $section_id,
                'set_id'        => $grouping_set_id,
            ]);
        }

        // Participation-style assessments: pre-create a blank (no score/code)
        // classworks row for every enrolled student in the section so the
        // admin can grade/randomize directly instead of students submitting.
        if ($auto_create) {
            $created = $this->classworks->create_blank_for_schedule($section_id, $post['schedule_id']);
            $flash .= $created > 0
                ? " Created $created blank submission(s) for the section."
                : ' All enrolled students already have a submission for this assessment.';
        }

        $this->session->set_flashdata('success', $flash);

        $qs = !empty($post['schedule_id']) ? '?schedule_id=' . $post['schedule_id'] : '';
        redirect('manage_assessments' . $qs);
    }

    // Attaches an EXISTING assessment (master) to an additional section,
    // instead of cloning its content into a new one — the true "shared
    // across sections" flow. Distinct from save_assessment()'s "Entire
    // Class" mode (which creates one master for every active section up
    // front) and from "Copy from existing assessment" (which pre-fills a
    // brand-new, independent master). Content fields aren't posted here at
    // all — only the target section and that section's own due/status/
    // grouping, since the master's content is fixed.
    public function assign_master()
    {
        $post = $this->input->post();
        $master_id = !empty($post['master_id']) ? (int) $post['master_id'] : null;
        $schedule_id = !empty($post['schedule_id']) ? $post['schedule_id'] : null;

        // class_assessments.php posts its own class_id so a save from there
        // lands back on that class's page instead of manage_assessments.
        $return_url = !empty($post['return_class_id'])
            ? 'class_assessments?class_id=' . (int) $post['return_class_id']
            : 'manage_assessments';

        if (!$master_id || !$schedule_id) {
            $this->session->set_flashdata('error', 'Pick both an assessment and a target section.');
            redirect($return_url);
            return;
        }

        // UNIQUE(assessment_id, schedule_id) would reject this anyway, but a
        // friendly flash message beats a silently-failed insert (db_debug is
        // off — see CLAUDE.md).
        $existing = $this->db->where(['assessment_id' => $master_id, 'schedule_id' => $schedule_id])
            ->get('assessment_section')->row_array();
        if ($existing) {
            $this->session->set_flashdata('error', 'That section is already assigned to this assessment.');
            redirect($return_url);
            return;
        }

        $status = isset($post['status']) ? (int) $post['status'] : 0;
        $section_fields = [
            'due'          => $post['due'],
            'status'       => $status,
            'is_groupings' => !empty($post['is_groupings']) ? 1 : 0,
        ];

        $section_id = $this->assessments->assign_to_schedule($master_id, $schedule_id, $section_fields);

        $grouping_set_id = !empty($post['grouping_set_id']) ? (int) $post['grouping_set_id'] : null;
        if ($section_fields['is_groupings'] && $grouping_set_id) {
            $this->db->insert('assessment_groupings', [
                'assessment_id' => $section_id,
                'set_id'        => $grouping_set_id,
            ]);
        }

        $flash = 'Section assigned to the shared assessment.';
        if (!empty($post['auto_create_submissions'])) {
            $created = $this->classworks->create_blank_for_schedule($section_id, $schedule_id);
            $flash .= $created > 0 ? " Created $created blank submission(s) for the section." : '';
        }

        $this->session->set_flashdata('success', $flash);
        redirect($return_url);
    }

    // Read-only per-class assessment monitoring/management — every master
    // belonging to a class, INCLUDING drafts with no section assigned yet
    // (see assessments::get_for_class()). Complements manage_assessments()
    // (which is per-section and only ever shows assigned assessments) with
    // a per-class view that also surfaces unassigned drafts.
    public function class_assessments()
    {
        $this->load->model('classes');

        $class_id = $this->input->get('class_id') ?: null;

        // Semester filter — defaults to whichever semester is currently
        // active, but the admin can switch to a past one to see (and, via
        // "One Section"/"Assign", still act on) that semester's assignments.
        // Drafts (class_id set, no section yet) aren't tied to any semester
        // and always show regardless of this filter — see get_for_class().
        $data['semesters'] = $this->db->order_by('trans_no', 'DESC')->get('semester_master')->result_array();
        $active_semester = $this->db->where('is_active', 1)->get('semester_master')->row_array();
        $semester_id = $this->input->get('semester_id') ?: ($active_semester['trans_no'] ?? null);
        $data['selected_semester_id'] = $semester_id;

        $data['all_classes'] = $this->classes->as_array()->order_by('class_code')->get_all();
        $data['class_id'] = $class_id;
        $data['selected_class'] = null;
        $data['assessments'] = [];
        $data['sections'] = [];
        $data['copyable_assessments'] = [];
        $class_code = null;

        if ($class_id && $semester_id) {
            $data['selected_class'] = $this->classes->as_array()->get($class_id);
            $data['assessments'] = $this->assessments->get_for_class($class_id, $semester_id);
            // Full section labels (not just schedule_id, unlike
            // get_active_schedules_by_class() — that method only feeds the
            // "assign to every section" fan-out in save_assessment(), which
            // doesn't need labels) for the Section/Assign dropdowns below,
            // scoped to the selected semester (not always the active one).
            $data['sections'] = $this->db->select('cs.schedule_id, cs.section, cs.type')
                ->from('class_schedule cs')
                ->where('cs.class_id', $class_id)
                ->where('cs.semester_id', $semester_id)
                ->order_by('cs.section')
                ->get()->result_array();

            // "Copy from" is scoped to this class only here (unlike
            // manage_assessments, which filters dynamically by JS as the
            // admin switches sections/classes in one shared modal) — this
            // page never changes class without a full reload, so the filter
            // can just happen once, server-side.
            $class_code = $data['selected_class']['class_code'] ?? null;
            $data['copyable_assessments'] = array_values(array_filter(
                $this->assessments->get_copyable_for_active_semester(),
                function ($ca) use ($class_code) {
                    return $ca['class_code'] === $class_code;
                }
            ));
        }

        $data['io_types'] = $this->db->get('io_type')->result_array();

        $this->load->model('Widgets_model');
        $data['widgets'] = $this->Widgets_model->get_all();

        $this->load->model('Grouping_model');
        $data['grouping_sets'] = $this->Grouping_model->get_all_sets();

        // Same topic-library scan as manage_assessments(), but pre-filtered
        // to topics belonging to this class's class_code (plus legacy/unfiled
        // topics, class_code '') — see the copyable_assessments filter above
        // for why this can happen server-side here instead of via JS.
        $data['iq_topics'] = [];
        $data['iq_topic_question_counts'] = [];
        $data['iq_topic_formats'] = [];
        $data['iq_topic_meta'] = [];
        foreach ($this->_glob_json_topics() as $file) {
            $meta = json_decode(file_get_contents($file), true);
            if (!$meta || empty($meta['sections'])) {
                continue;
            }
            $is_discussion_format = true;
            foreach ($meta['sections'] as $s) {
                if (isset($s['questions'])) {
                    $is_discussion_format = false;
                    break;
                }
            }
            if (!$is_discussion_format) {
                continue;
            }
            $topic_class = $this->_topic_class_code_from_path($file);
            if ($topic_class !== '' && $topic_class !== $class_code) {
                continue;
            }
            $slug = basename($file, '.json');
            $title = $meta['title'] ?? ucwords(str_replace('_', ' ', $slug));
            $format = $this->_iq_topic_format($meta);
            $data['iq_topics'][$slug] = $title;
            $data['iq_topic_formats'][$slug] = $format;
            $data['iq_topic_question_counts'][$slug] = $format === 'micro'
                ? $this->_count_micro_topic_items($meta)
                : $this->_count_iq_topic_questions($meta);
            $data['iq_topic_meta'][$slug] = [
                'title'       => $title,
                'description' => $meta['description'] ?? '',
            ];
        }

        $this->load->view('admin/class_assessments', $data);
    }

    // Edits a master's shared content ONLY (title/description/iotype/term/
    // max_score/widget/given) — no due/status/is_groupings, since those are
    // per-section and a master on class_assessments may have zero, one, or
    // several sections with different values. Distinct from save_assessment()'s
    // edit branch, which always requires and edits exactly one target
    // section. Same widget/given validation as save_assessment() (duplicated
    // rather than shared — the validation is short and the two callers'
    // surrounding flow/redirects differ enough that extracting a helper
    // would need its own parameter surface for little real reuse).
    public function update_class_assessment_master()
    {
        $post = $this->input->post();
        $master_id = !empty($post['master_id']) ? (int) $post['master_id'] : null;
        $class_id = !empty($post['class_id']) ? (int) $post['class_id'] : null;

        if (!$master_id) {
            $this->session->set_flashdata('error', 'Assessment not found — please try again.');
            redirect('class_assessments' . ($class_id ? '?class_id=' . $class_id : ''));
            return;
        }

        $master_fields = [
            'iotype_id'   => $post['iotype_id'],
            'title'       => $post['title'],
            'description' => $post['description'],
            'max_score'   => $post['max_score'],
            'term'        => $post['term'],
            'widget_id'   => !empty($post['widget_id']) ? (int) $post['widget_id'] : null,
            'given'       => !empty($post['widget_id']) ? ($post['given'] ?? null) : null,
        ];

        if ($master_fields['widget_id']) {
            $this->load->model('Widgets_model');
            $widget = $this->Widgets_model->get($master_fields['widget_id']);
            if ($widget && in_array($widget['widget_key'], ['iq_discussion', 'iq_micro'], true)) {
                $is_micro = $widget['widget_key'] === 'iq_micro';

                $pasted = $this->_resolve_iq_paste($post, $widget);
                if ($pasted === false) {
                    redirect('class_assessments' . ($class_id ? '?class_id=' . $class_id : ''));
                    return;
                }
                if ($pasted) {
                    $master_fields['given'] = json_encode(['topic' => $pasted]);
                }

                $topic = json_decode($master_fields['given'] ?? '', true)['topic'] ?? '';
                $topic_found = false;
                $wrong_format = false;
                $topic_meta = [];
                if ($topic) {
                    foreach ($this->_glob_json_topics() as $file) {
                        if (basename($file, '.json') !== $topic) {
                            continue;
                        }
                        $meta = json_decode(file_get_contents($file), true) ?: [];
                        if (($this->_iq_topic_format($meta) === 'micro') !== $is_micro) {
                            $wrong_format = true;
                            break;
                        }
                        $master_fields['max_score'] = max(1, $is_micro
                            ? $this->_count_micro_topic_items($meta)
                            : $this->_count_iq_topic_questions($meta));
                        $topic_found = true;
                        $topic_meta = $meta;
                        break;
                    }
                }
                if ($wrong_format) {
                    $this->session->set_flashdata('error', $is_micro
                        ? 'Microlearning Quiz needs a topic in the microlearning format (sections with "chunks") — the selected topic is a plain lesson+quiz topic, so use the Interactive Discussion/Quiz widget for it.'
                        : 'Interactive Discussion/Quiz needs a plain lesson+quiz topic — the selected topic is in the microlearning format (sections with "chunks"), so use the Microlearning Quiz widget for it.');
                    redirect('class_assessments' . ($class_id ? '?class_id=' . $class_id : ''));
                    return;
                }
                if (!$topic_found) {
                    $this->session->set_flashdata('error', $widget['name'] . ' needs a topic — pick one from the Topic dropdown (the selected topic file could not be found).');
                    redirect('class_assessments' . ($class_id ? '?class_id=' . $class_id : ''));
                    return;
                }

                $found_meta = $this->_widget_config_meta($topic_meta);
                unset($found_meta['max_score']);
                $this->_fill_blank_fields($master_fields, $found_meta);
            } elseif ($widget) {
                $given = trim((string) ($master_fields['given'] ?? ''));
                $config = $given !== '' ? json_decode($given, true) : null;
                if (!is_array($config) || empty($config)) {
                    if ($given === '') {
                        $reason = 'an empty config';
                    } elseif (json_last_error() !== JSON_ERROR_NONE) {
                        $reason = 'invalid JSON (' . json_last_error_msg() . ')';
                    } else {
                        $reason = 'JSON that is not an object';
                    }
                    $this->session->set_flashdata('error', 'Widget config not saved — "' . $widget['name'] . '" needs a JSON config, but the form contained ' . $reason . '.');
                    redirect('class_assessments' . ($class_id ? '?class_id=' . $class_id : ''));
                    return;
                }
                if (in_array($widget['widget_key'], ['quiz', 'secure_quiz'], true) && !isset($config['questions'])) {
                    $master_fields['given'] = json_encode(['questions' => $this->Widgets_model->quiz_questions($config)]);
                }

                $this->_fill_blank_fields($master_fields, $this->_widget_config_meta($config));
            }
        }

        $this->assessments->update_master($master_id, $master_fields);

        $this->session->set_flashdata('success', 'Assessment updated successfully.');
        redirect('class_assessments' . ($class_id ? '?class_id=' . $class_id : ''));
    }

    // One-time/idempotent maintenance action: backfills class_id on masters
    // created before save_assessment() started populating it on write (see
    // assessments::backfill_class_id()). Safe to run repeatedly — only
    // touches rows where class_id IS NULL.
    public function backfill_assessment_class_id()
    {
        $count = $this->assessments->backfill_class_id();
        $this->session->set_flashdata('success', "Backfilled class_id on $count assessment(s).");
        redirect('class_assessments' . ($this->input->post('class_id') ? '?class_id=' . (int) $this->input->post('class_id') : ''));
    }

    // Full delete of a draft or assigned master and everything under it
    // (sections, groupings, live state, and — if it has submissions —
    // classworks rows too). Distinct from delete_assessment(), which only
    // ever removes ONE section; this removes the whole assessment across
    // every section it's on, for the class_assessments page's "Delete"
    // action. Same two-step force-confirm pattern as delete_assessment().
    public function delete_class_assessment($master_id)
    {
        header('Content-Type: application/json');

        if ($this->input->method() !== 'post') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
            return;
        }

        $master_id = (int) $master_id;
        $master = $this->db->where('assessment_id', $master_id)->get('assessments')->row_array();
        if (!$master) {
            echo json_encode(['success' => false, 'error' => 'Assessment not found.']);
            return;
        }

        $section_ids = array_column($this->assessments->sections_of_master($master_id), 'assessment_section_id');
        $submission_count = $section_ids
            ? (int) $this->db->where_in('assessment_id', $section_ids)->count_all_results('classworks')
            : 0;

        $force = $this->input->post('force') === '1';

        if ($submission_count > 0 && !$force) {
            echo json_encode(['success' => false, 'blocked' => true, 'submission_count' => $submission_count]);
            return;
        }

        if ($submission_count > 0) {
            $this->db->where_in('assessment_id', $section_ids)->delete('classworks');
        }
        // assessment_section rows (and their assessment_groupings/
        // assessment_live_state) cascade via FK to the master.
        $this->assessments->delete_master($master_id);

        echo json_encode(['success' => true]);
    }

    // Renders a widget's own input_view against admin-authored "given" JSON so
    // the Add/Edit Assessment modal can show a live preview underneath the
    // config textarea — same view file the student sees, just with
    // readonly=false/existing=null (a blank, unsubmitted form).
    public function preview_widget()
    {
        $widget_id = $this->input->post('widget_id');
        $given = $this->input->post('given');

        if (empty($widget_id)) {
            echo '<p class="text-muted mb-0">Select a widget above to see a preview.</p>';
            return;
        }

        $this->load->model('Widgets_model');
        $widget = $this->Widgets_model->get((int) $widget_id);
        if (!$widget) {
            echo '<p class="text-danger mb-0">Unknown widget.</p>';
            return;
        }

        $config = [];
        if (trim((string) $given) !== '') {
            $config = json_decode($given, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo '<p class="text-danger mb-0"><i class="fas fa-exclamation-triangle"></i> Invalid JSON &mdash; fix the config above to see a preview.</p>';
                return;
            }
        }

        echo $this->load->view($widget['input_view'], [
            'config'   => $config ?: [],
            'readonly' => false,
            'existing' => null,
        ], true);
    }

    public function update_assessment_status()
    {
        $assessment_id = (int)$this->input->post('assessment_id');
        $status = $this->input->post('status');

        if ($status === 'open' || $status === 'closed') {
            $status = $status === 'open' ? '1' : '0';
        }

        if (!$assessment_id || !in_array($status, ['0', '1'], true)) {
            echo json_encode(['success' => false]);
            return;
        }

        $this->db->where('assessment_section_id', $assessment_id)->update('assessment_section', ['status' => (int)$status]);
        echo json_encode(['success' => true]);
    }

    // Bulk open/close — used by the "Open All" / "Close All" buttons on
    // manage_assessments, applied only to the assessment_ids currently shown
    // in the table (i.e. respecting the Section filter).
    public function bulk_update_assessment_status()
    {
        $status = $this->input->post('status');
        $assessment_ids = $this->input->post('assessment_ids');
        $assessment_ids = is_array($assessment_ids) ? array_filter(array_map('intval', $assessment_ids)) : [];

        if (!in_array($status, ['0', '1'], true) || empty($assessment_ids)) {
            echo json_encode(['success' => false]);
            return;
        }

        $this->db->where_in('assessment_section_id', $assessment_ids)->update('assessment_section', ['status' => (int)$status]);
        echo json_encode(['success' => true]);
    }

    // Delete button on manage_assessments. Two-step: without `force`, a
    // pending student submission blocks the delete and reports how many exist
    // (so the modal JS can re-confirm with the admin using a fresh count —
    // never trusting the row count already rendered in the table, which can
    // go stale between page load and click). Only with `force=1` does it
    // cascade-delete the classworks rows too; otherwise a zero-submission
    // assessment is removed outright.
    public function delete_assessment($id)
    {
        header('Content-Type: application/json');

        if ($this->input->method() !== 'post') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
            return;
        }

        $assessment_id = (int) $id;
        if (!$assessment_id || !$this->assessments->get($assessment_id)) {
            echo json_encode(['success' => false, 'error' => 'Assessment not found.']);
            return;
        }

        $submission_count = (int) $this->db
            ->where('assessment_id', $assessment_id)
            ->count_all_results('classworks');

        if ($submission_count > 0) {
            echo json_encode(['success' => false, 'blocked' => true, 'submission_count' => $submission_count]);
            return;
        }

        // assessment_groupings/assessment_live_state cascade-delete via their
        // FK to assessment_section — delete_section() handles both that and
        // deleting the now-orphaned master if this was its last section.
        $this->assessments->delete_section($assessment_id);

        echo json_encode(['success' => true]);
    }
}
