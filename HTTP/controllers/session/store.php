<?php 

use core\Authenticator;
use HTTP\Forms\Loginform;


// match credentials

$form = Loginform::validate($attributes = [
    'email' => $_POST['email'],
    'password' => $_POST['password']
]);

$SignedIn = (new Authenticator) -> attempt(
    $attributes['email'] , $attributes['password']);

if(!$SignedIn){
    $form-> error(
        'email' , 'No matching account found for that email address and password!'
        )-> throw();
    }
        
redirect('/');

