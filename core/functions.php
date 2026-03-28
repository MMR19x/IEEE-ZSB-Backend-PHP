<?php

use core\Response;

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

function abort($code = 404){

 http_response_code($code);
     
 require base_path("views/{$code}.php");
     
 die();
     
 }

function authorize ($condition , $status = Response::FORBIDDEN){

if (! $condition){
    abort($status);
}
}

function base_path ($path){
    return BASE_PATH .$path;
}

function view ($path, $attributes = [])
{
extract($attributes);
return require base_path('views/'. $path);
}