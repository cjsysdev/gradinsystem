<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Bootstrap 4 config for CI's pagination library.
 *
 * The same 25-line array was being pasted into every paginated admin screen
 * (AdminStudentController::emergency_contacts(), ::advance_excuses(),
 * AdminAssessmentController::manage_assessments()); this is that array with the
 * three per-screen values filled in.
 *
 * `reuse_query_string` is the important one: page links keep whatever filters
 * are already in the query string (class_id, section, group_id, ...) instead of
 * dropping them, and `query_string_segment = 'per_page'` means the *offset*
 * arrives as ?per_page=N — CI's own naming, kept so all screens read alike.
 */
if (!function_exists('bs_pagination_config')) {
    function bs_pagination_config($base_url, $total, $per_page)
    {
        return [
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
    }
}
