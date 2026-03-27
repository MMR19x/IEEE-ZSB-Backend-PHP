<?php 

use core\Database;

$config = require base_path('config.php');
$db = new Database($config['database']);

$current_user_id = 1;

if($_SERVER['REQUEST_METHOD'] === 'POST'){

$note = $db -> query('Select * from notes where id = :id',
[
    'id' => $_GET['id']])->findOrfail();

authorize($note['user_id'] === $current_user_id);

$db -> query('delete from notes where id = :id' , 
[
    'id' => $_GET['id']
]);

header('location: /notes');
exit();

}
else {

$note = $db -> query('Select * from notes where id = :id',
[
    'id' => $_GET['id']])->findOrfail();

authorize($note['user_id'] === $current_user_id);

view("notes/show.view.php", [
    'heading' => 'Note',
    'note' => $note
] );  
}