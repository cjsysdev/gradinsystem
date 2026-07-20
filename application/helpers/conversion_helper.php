<?php

function convert_datetime_string($datetime)
{
    $date = new DateTime($datetime);
    return  $date->format('D. M j');
}

function convert_datetime($datetime)
{
    $date = new DateTime($datetime);
    return [
        'date' => $date->format('D M j'),
        'time' => $date->format('h:i A')
    ];
}

function convert_time($time)
{
    $newtime = new DateTime($time);
    return $newtime->format('h:i A');
}

function get_date_today()
{
    date_default_timezone_set('Asia/Manila');
    $date = date('Y-m-d');
    return $date;
}

function generate_random_numbers($count = 10, $min = 1, $max = 9)
{
    $random_numbers = '';
    for ($i = 0; $i < $count; $i++) {
        $random_numbers .= rand($min, $max);
    }
    return $random_numbers;
}

function check_flashdata($alert_type)
{
    return get_instance()->session->flashdata($alert_type) != NULL;
}

function headToast($color)
{
    $template = <<<HTML
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id='liveToast' class="toast show align-items-center text-bg-{$color} border-0" data-autohide="true" data-animation="true" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
    HTML;

    return $template;
}


function footerToast()
{
    $template = <<<HTML
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    HTML;

    return $template;
}

function randomizeNumber($min, $max)
{
    $min = (float)$min;
    $max = (float)$max;

    $randomNumber = $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
    $randomNumber = round($randomNumber, 1);

    return $randomNumber;
}

/**
 * @deprecated Use Grade_calculator::transmute($percentage, $passing_rate).
 *
 * Kept as a shim so any straggling call site keeps working. Two behaviours
 * changed, both deliberately:
 *
 *  - The passing rate now comes from semester_master instead of a hardcoded 60,
 *    so a semester configured for 50 is finally honoured.
 *  - It returns a float or NULL, never a formatted string. The old version
 *    returned number_format() output, so callers comparing it with `>` were
 *    comparing strings, and passing 'INC' in cast it to 0 and got back "5.00".
 *
 * Prefer calling Grade_calculator directly — grades should not be computed in
 * a view.
 */
function convertPercentageToGradePoint($percentage, $passing_rate = null)
{
    $CI = &get_instance();
    $CI->load->model('Grade_calculator');

    if ($passing_rate === null) {
        $row = $CI->db->query(
            "SELECT passing_rate FROM semester_master WHERE is_active = 1 LIMIT 1"
        )->row_array();
        $passing_rate = $row['passing_rate'] ?? 60;
    }

    return $CI->Grade_calculator->transmute($percentage, $passing_rate);
}
