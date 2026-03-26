<?php 

use core\Database;

$config = require base_path('config.php');
$db = new Database($config['database']);

$current_user_id = 1;

$note = $db -> query('Select * from notes where id = :id',
[
    'id' => $_GET['id']])->findOrfail();

authorize($note['user_id'] === $current_user_id);

view("notes/show.view.php", [
    'heading' => 'Note',
    'note' => $note
] );  