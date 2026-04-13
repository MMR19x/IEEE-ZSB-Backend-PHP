<?php 

use core\App;

$db = App::resolve('core\Database'); 

$notes = $db->query('select * from notes where user_id = 1')->fetchAll();


view("notes/index.view.php", [
    'heading' => 'My Notes',
    'notes' => $notes
] );  