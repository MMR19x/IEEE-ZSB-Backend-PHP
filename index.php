<?php

require 'functions.php';
require 'Database.php';

$config = require 'config.php';

$db = new Database($config['database']);

$id = $_GET['id'];
$query = "select * from posts where id = ?";

$posts = $db -> query($query,[$id]) -> fetchAll(PDO::FETCH_ASSOC);

foreach($posts as $post){
    echo "<li>" . $post['title'] . "</li>";
}