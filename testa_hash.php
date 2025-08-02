<?php
// Insira aqui a senha que você quer testar e o hash do banco
$senha = 'tinatinatin'; // senha em texto
$hash = '$2y$10$FzWmjNdx1ekzyry8M8tkEOHeGxPhZHNTZwkaDsc91DsoEZdBbkxHC'; // hash copiado do banco

if (password_verify($senha, $hash)) {
    echo "Senha correta!";
} else {
    echo "Senha incorreta!";
}
