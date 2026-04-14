<?php 

use core\App;
use core\Validator;

$db = App::resolve('core\Database'); 

$current_user_id = 1;

//find coresponding note
$note = $db -> query('Select * from notes where id = :id',
[
'id' => $_POST['id']
])->findOrfail();

// authorize that current user can edit the note
authorize($note['user_id'] ===$current_user_id);

// validate the form 
 
$errors = [];

if (!Validator:: string($_POST['body'] , 1 , 1000)){
        $errors ['body'] = 'A body of no more than 1,000 characters is required';
    }

// if no validation errors, update the record in the notes database tables 

if(count($errors)){

     view('notes/edit.view.php', [
        'heading' => 'Edit Note',
        'errors' => $errors,
        'note' => $note
    ]);

}

$db-> query('update notes set body = :body where id = :id ', [
    'id' => $_POST['id'],
    'body' => $_POST['body']
]);

//redirect the user 
header('location: /notes');
die();

