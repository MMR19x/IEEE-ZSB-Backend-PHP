<?php

const BASE_PATH = __DIR__ . '\\..\\';
require BASE_PATH.'core/functions.php';

spl_autoload_register(function ($class) {
    
 $class = str_replace('\\' , DIRECTORY_SEPARATOR , $class);

require base_path("{$class}.php");
});

$router = new \core\Router();

$Routes = require base_path(('routes.php'));

$uri = parse_url($_SERVER['REQUEST_URI'])['path'];
$method =isset($_POST['_method']) ? $_POST['method']   : $_SERVER['REQUEST_METHOD'];

$router -> route($uri , $method);