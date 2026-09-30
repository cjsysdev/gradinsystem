<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * CodeIgniter require_once's this file for every request (see
 * system/core/CodeIgniter.php, which loads core/{subclass_prefix}Controller.php),
 * so any base controller defined here is available to every controller without
 * an autoloader or a manual require.
 */

/**
 * Reserved CI3 base controller. Nothing extends it yet — it exists so the
 * filename CodeIgniter looks for actually defines the class it names.
 */
class MY_Controller extends CI_Controller
{
}

/**
 * Base for the admin screens, which used to be one 3,100-line AdminController.
 * It carries the two things every admin controller needs: the session gate,
 * and the seams to the shared libraries that back the assessment and
 * discussion screens.
 *
 * Admin controllers: AdminController (dashboard, attendance, project logs),
 * AdminSubmissionController, AdminAssessmentController, AdminStudentController,
 * AdminContentController. Old AdminController/* URLs still resolve — see the
 * legacy routes in application/config/routes.php.
 */
class Admin_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('role') !== 'admin') {
            redirect('login');
        }
    }

    // ---- Iq_topic_helper seams -----------------------------------------
    // Topic-file logic shared by the assessment screens (max_score derivation,
    // the topic dropdown) and the discussion manager (pasted topic files).

    protected function _glob_json_topics()
    {
        $this->load->library('iq_topic_helper');
        return $this->iq_topic_helper->glob_topics();
    }

    protected function _topic_class_code_from_path($file)
    {
        $this->load->library('iq_topic_helper');
        return $this->iq_topic_helper->class_code_from_path($file);
    }

    protected function _iq_topic_format(array $topic_meta)
    {
        $this->load->library('iq_topic_helper');
        return $this->iq_topic_helper->format($topic_meta);
    }

    protected function _count_iq_topic_questions(array $topic_meta)
    {
        $this->load->library('iq_topic_helper');
        return $this->iq_topic_helper->count_questions($topic_meta);
    }

    protected function _count_micro_topic_items(array $topic_meta)
    {
        $this->load->library('iq_topic_helper');
        return $this->iq_topic_helper->count_micro_items($topic_meta);
    }

    protected function _class_id_from_post($post)
    {
        $this->load->library('iq_topic_helper');
        return $this->iq_topic_helper->class_id_from_post($post);
    }

    protected function _resolve_iq_paste($post, $widget)
    {
        $this->load->library('iq_topic_helper');
        return $this->iq_topic_helper->resolve_paste($post, $widget);
    }

    protected function _save_pasted_topic_json($class_id, $slug, $json_text, $format = 'discussion', $allow_overwrite = true)
    {
        $this->load->library('iq_topic_helper');
        return $this->iq_topic_helper->save_pasted_topic_json($class_id, $slug, $json_text, $format, $allow_overwrite);
    }

    // ---- Widget_meta seams ---------------------------------------------
    // Pulls title/description/max_score out of a pasted config so the two
    // assessment save paths can fill in whatever the admin left blank.

    protected function _widget_config_meta(array $config)
    {
        $this->load->library('widget_meta');
        return $this->widget_meta->from_config($config);
    }

    protected function _fill_blank_fields(array &$fields, array $meta)
    {
        $this->load->library('widget_meta');
        $this->widget_meta->fill_blank_fields($fields, $meta);
    }
}
