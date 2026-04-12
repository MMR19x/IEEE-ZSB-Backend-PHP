<?php 
use core\Database;
use core\App;
use core\Validator;

// log in if the credentials match.

$db = App::resolve(Database::class);

$email = $_POST['email'];
$password = $_POST['password'];

$errors = [];

if (!Validator::email($email)) {
    $errors['email'] = 'Please provide a valid email address.';
}

if (!Validator::string($password)) {
    $errors['password'] = 'Please provide a valid password.';
}

if (!empty($errors)) {
   return view('session/create.view.php', [
        'errors' => $errors,
    ]);
    
}
// match credentials

$user = $db -> query ('select * from users where email = :email' , [
    'email' => $email
]) -> find();

if($user){
    
    if (password_verify($password , $user['password'])){
    login([
        'email' => $email
    ]);
    header('location: /');
    exit();
    }
}

return view('session/create.view.php', [
        'errors' => [
            'email' => 'No matching account found for that email address and password!'
        ]
    ]);