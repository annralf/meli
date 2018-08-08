<?php

function get_string_between($string, $start, $end)
{

    $string = ' ' . $string;

    $ini = strpos($string, $start);

    if ($ini == 0) return '';

    $ini += strlen($start);

    $len = strpos($string, $end, $ini) - $ini;

    return substr($string, $ini, $len);

}

function remove_string_between($string, $start, $end)
{
    $string = ' ' . $string;
    $ini = strpos($string, $start);

    $len = strpos($string, $end, $ini) + strlen($end);
    $re = substr($string, 0, $ini);
    $sult = substr($string, $len, strlen($string));
    return ($re . $sult);
}

?>