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
