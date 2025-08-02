<?php
session_start();
require_once 'includes/db.php';

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if (empty($email) || empty($nova_senha) || empty($confirmar)) {
        $erro = "Preencha todos os campos.";
    } elseif ($nova_senha !== $confirmar) {
        $erro = "As senhas não coincidem.";
    } else {
        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE usuarios SET senha_hash = ? WHERE email = ?");
        $stmt->bind_param("ss", $hash, $email);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $mensagem = "Senha alterada com sucesso!";
        } else {
            $erro = "Usuário não encontrado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Alterar Senha</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #2564a7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e0eefe;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            background: #2c78c4;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
            max-width: 420px;
            width: 100%;
        }
        h2 {
            margin-bottom: 20px;
            font-weight: 700;
            text-align: center;
        }
        .form-control {
            background: #4a90e2;
            border: 1px solid #2a5ea8;
            color: #e0eefe;
            font-weight: 600;
        }
        .form-control:focus {
            border-color: #77c0ff;
            box-shadow: 0 0 8px #77c0ff;
            background: #3a76d1;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Alterar Senha</h2>

        <?php if ($mensagem): ?>
            <div class="alert alert-success"><?= $mensagem ?></div>
        <?php elseif ($erro): ?>
            <div class="alert alert-danger"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nova Senha</label>
                <input type="password" name="nova_senha" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmar Nova Senha</label>
                <input type="password" name="confirmar" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Alterar Senha</button>
            <div class="mt-3 text-center">
                <a href="index.php" style="color: #d1e8ff;">Voltar ao login</a>
            </div>
        </form>
    </div>
</body>
</html>
