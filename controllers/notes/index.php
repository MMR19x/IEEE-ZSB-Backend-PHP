<?php 

use core\Database;

$config = require base_path('config.php');
$db = new Database($config['database']);

$notes = $db->query('select * from notes where user_id = 1')->fetchAll();


view("notes/index.view.php", [
    'heading' => 'My Notes',
    'notes' => $notes
] );  