<?php

$uri = parse_url($_SERVER['REQUEST_URI'])['path'];

$Routes = [
    '/' => 'controllers/index.php',
    '/about' => 'controllers/about.php',
    '/notes' => 'controllers/notes.php',
    '/note' => 'controllers/note.php',
    '/contact' => 'controllers/contact.php',
];


function routeToController($uri , $Routes){
    
    if(array_key_exists($uri, $Routes)){
        require $Routes[$uri];
        } else {
            abort(); 
            }
            }
function abort($code = 404){
   http_response_code($code);
            
  require "views/{$code}.php";
            
  die();
            
  }

  routeToController($uri, $Routes);