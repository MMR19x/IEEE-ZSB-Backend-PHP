<?php

function dd($value)
{

    echo "<pre>";
    var_dump($value);
    echo "</pre>";

    die();

}

function URLIs($value)
{
    return $_SERVER['REQUEST_URI'] === $value;
}