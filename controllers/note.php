<?php 

$config = require 'config.php';

$db = new Database($config['database']);

$heading = 'Note';

$current_user_id = 1;

$note = $db -> query('Select * from notes where id = :id',
[
    'id' => $_GET['id']])->findOrfail();

authorize($note['user_id'] === $current_user_id);
require "views/note.view.php";
