<?php

use core\Router;
use core\Session;
use core\ValidationException;

session_start();

const BASE_PATH = __DIR__ . '\\..\\';
require BASE_PATH.'core/functions.php';

spl_autoload_register(function ($class) {
    
 $class = str_replace('\\' , DIRECTORY_SEPARATOR , $class);

require base_path("{$class}.php");
});

require base_path('bootstrap.php');

$router = new \core\Router();

$Routes = require base_path(('routes.php'));

$uri = parse_url($_SERVER['REQUEST_URI'])['path'];
$method =isset($_POST['_method']) ? $_POST['_method'] : $_SERVER['REQUEST_METHOD'];
try {
$router -> route($uri , $method);
}
catch (ValidationException $exception){
    Session::flash('errors' , $exception-> errors);
    Session::flash('old', $exception->old);

    return redirect($router->previousURL());
}

Session::unflash();