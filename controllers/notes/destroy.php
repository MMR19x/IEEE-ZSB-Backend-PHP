<?php 

use core\App;

$db = App::resolve('core\Database'); 
$current_user_id = 1;



$note = $db -> query('Select * from notes where id = :id',
[
    'id' => $_POST['id']])->findOrfail();

authorize($note['user_id'] === $current_user_id);

$db -> query('delete from notes where id = :id' , 
[
    'id' => $_GET['id']
]);

header('location: /notes');
exit();

