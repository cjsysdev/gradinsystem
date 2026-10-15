<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Exam mode switch (admin/settings → Global_setting::EXAM_TERM_KEY).
 *
 * Replaces the old session flag that was hardcoded to false at login: read
 * from global_settings once per request, so turning it on or off applies to
 * every student immediately, including ones already logged in.
 *
 * Autoloaded — see $autoload['helper'] in application/config/autoload.php.
 */
if (!function_exists('exam_term_on')) {
    function exam_term_on()
    {
        static $on = null;
        if ($on === null) {
            $CI =& get_instance();
            $CI->load->model('Global_setting');
            $on = $CI->Global_setting->exam_term();
        }
        return $on;
    }
}
