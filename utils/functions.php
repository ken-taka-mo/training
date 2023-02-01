<?php

function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES);
}

function insertHyphen($phoneNumber)
{
    $first = substr($phoneNumber, 0, 3);
    $middle = substr($phoneNumber, 3, 4);
    $last = substr($phoneNumber, 7, 4);

    return $first . '-' . $middle . '-' . $last;
}
