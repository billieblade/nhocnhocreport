<?php
require_once 'includes/db.php'; // seu arquivo de conexão

// Lista de usuários com emails para atualizar
$usuarios = [
    'tina@nhoc.com',
    'analaura@nhoc.com'
];

// Senha nova em texto para ambos
$senhaNova = 'tinatinatin';

// Gera o hash da senha nova
$hash = password_hash($senhaNova, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE usuarios SET senha_hash = ? WHERE email = ?");

foreach ($usuarios as $email) {
    $stmt->bind_param("ss", $hash, $email);
    if ($stmt->execute()) {
        echo "Senha atualizada com sucesso para o usuário $email.<br>";
    } else {
        echo "Erro ao atualizar senha do usuário $email: " . $stmt->error . "<br>";
    }
}

$stmt->close();
$conn->close();
