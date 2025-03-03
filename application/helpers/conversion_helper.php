<?php

function convert_datetime_string($datetime)
{
    $date = new DateTime($datetime);
    return  $date->format('D - M j');
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
