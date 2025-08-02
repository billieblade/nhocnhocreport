<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$data = $_POST['data'];
$horario = $_POST['horario'];
$tipo = $_POST['tipo'];
$refeicao = $_POST['refeicao'];
$bebida = $_POST['bebida'];

$stmt = $conn->prepare("INSERT INTO refeicoes (usuario_id, data, tipo, horario, refeicao, bebida) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $usuario_id, $data, $tipo, $horario, $refeicao, $bebida);
$stmt->execute();

header("Location: dashboard.php");
exit();
?>
