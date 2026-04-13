<?php 

use core\Authenticator;
use HTTP\Forms\Loginform;



$email = $_POST['email'];
$password = $_POST['password'];

// match credentials
$form = new Loginform();

if($form-> validate($email , $password)){
    $auth = new Authenticator();
    
    if($auth->attempt($email,$password)){
        redirect('/');
    }
    $form-> error($email , 'No matching account found for that email address and password!');
} 


return view('session/create.view.php', [
        'errors' => $form-> errors()
    ]);
