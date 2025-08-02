<?php
$host = 'localhost';
$db = 'nomedodb';
$user = 'userdodbc';
$pass = 'senhadb';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>
