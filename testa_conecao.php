<?php
require_once 'includes/db.php';

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
} else {
    echo "Conexão com banco OK!";
}
