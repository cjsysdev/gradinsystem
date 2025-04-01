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

function convertPercentageToGradePoint($percentage)
{
    $passingGrade = 60;
    if ($percentage <= $passingGrade) {
        // Range 1: 0% to passing grade
        return 5.0 - (2.0 / $passingGrade) * $percentage;
    } elseif ($percentage > $passingGrade && $percentage <= 100) {
        // Range 2: Passing grade to 100%
        return 3.0 - (2.0 / (100 - $passingGrade)) * ($percentage - $passingGrade);
    } else {
        // Invalid percentage (e.g., greater than 100 or less than 0)
        return null;
    }
}
