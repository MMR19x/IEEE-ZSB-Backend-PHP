<?php

$Routes = require base_path(('routes.php'));


function routeToController($uri , $Routes){
    
    if(array_key_exists($uri, $Routes)){
        require base_path($Routes[$uri]);
        } else {
            abort(); 
            }
            }
            function abort($code = 404){
                http_response_code($code);
                
                require base_path("views/{$code}.php");
                
                die();
                
                }
  
                
  $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
  
  routeToController($uri, $Routes);