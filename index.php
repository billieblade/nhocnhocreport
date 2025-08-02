<?php
session_start();
require_once 'includes/db.php';

$erro = '';
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica se é troca de senha
    if (isset($_POST['nova_senha']) && isset($_POST['email_alterar'])) {
        $email = trim($_POST['email_alterar']);
        $nova_senha = preg_replace('/\s+/', '', $_POST['nova_senha']);

        if (strlen($nova_senha) < 6) {
            $erro = "A nova senha deve ter pelo menos 6 caracteres.";
        } else {
            $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET senha_hash = ? WHERE email = ?");
            $stmt->bind_param("ss", $hash, $email);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $mensagem = "Senha alterada com sucesso!";
            } else {
                $erro = "Não foi possível alterar a senha. Verifique o e-mail informado.";
            }
        }
    } else {
        // Login
        $email = trim($_POST['email'] ?? '');
        $senha = preg_replace('/\s+/', '', $_POST['senha'] ?? '');

        $stmt = $conn->prepare("SELECT id, nome, senha_hash FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            if (password_verify($senha, $user['senha_hash'])) {
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['nome'] = $user['nome'];
                header("Location: dashboard.php");
                exit();
            } else {
                $erro = "Senha incorreta.";
            }
        } else {
            $erro = "Usuário não encontrado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Login - Nhoc Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: url('nhocbg.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #f0f8ff;
            padding: 15px;
        }
        .login-container {
            background: rgba(44, 120, 196, 0.9);
            padding: 30px 35px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            color: #e0eefe;
        }
        h2 {
            font-weight: 700;
            color: #e0eefe;
            margin-bottom: 25px;
            text-align: center;
            letter-spacing: 1px;
            text-shadow: 0 0 5px rgba(255,255,255,0.7);
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            font-weight: 600;
            color: #fff;
        }
        .btn-primary:hover {
            background-color: #0a58ca;
        }
        .form-label {
            font-weight: 600;
            color: #d1e8ff;
        }
        .form-control {
            background: #4a90e2;
            border: 1px solid #2a5ea8;
            color: #e0eefe;
            font-weight: 600;
            border-radius: 6px;
        }
        .form-control:focus {
            border-color: #77c0ff;
            box-shadow: 0 0 8px #77c0ff;
            color: #fff;
            background: #3a76d1;
        }
        .toggle {
            text-align: center;
            margin-top: 15px;
            color: #cfe8ff;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: underline;
        }
        .alert {
            font-size: 0.9rem;
        }
        footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.85rem;
            color: #d9e9ff;
        }
    </style>
</head>
<body>
<div class="login-container shadow-sm">

    <h2>Entrar no Nhoc Report</h2>

    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <?php if ($mensagem): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form method="POST" id="form-login">
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email" required autofocus />
        </div>
        <div class="mb-4">
            <label for="senha" class="form-label">Senha</label>
            <input type="password" class="form-control" id="senha" name="senha" required />
        </div>
        <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>

    <form method="POST" id="form-senha" style="display: none;">
        <div class="mb-3">
            <label for="email_alterar" class="form-label">Seu e-mail</label>
            <input type="email" class="form-control" id="email_alterar" name="email_alterar" required />
        </div>
        <div class="mb-4">
            <label for="nova_senha" class="form-label">Nova senha</label>
            <input type="password" class="form-control" id="nova_senha" name="nova_senha" required />
        </div>
        <button type="submit" class="btn btn-primary w-100">Alterar senha</button>
    </form>

    <div class="toggle" onclick="toggleForm()">Alterar minha senha</div>

    <div class="text-center mt-2">
        <button type="button" class="btn btn-link text-white-50" onclick="contatoDev()">Não sei minha senha</button>
    </div>

    <footer>
        Desenvolvido por Suporte Ogro
    </footer>
</div>

<script>
    function toggleForm() {
        const loginForm = document.getElementById("form-login");
        const senhaForm = document.getElementById("form-senha");
        loginForm.style.display = loginForm.style.display === "none" ? "block" : "none";
        senhaForm.style.display = senhaForm.style.display === "none" ? "block" : "none";
    }

    function contatoDev() {
        alert("Entre em contato com o desenvolvedor pelo e-mail: felopes@gmail.com");
    }
</script>
</body>
</html>
