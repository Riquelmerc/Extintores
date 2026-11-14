<?php

require_once "autenticacao.php";
iniciarSessao();
require_once "conexao.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cadastro.php");
    exit;
}

$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");
$senha = $_POST["senha"] ?? "";

if ($nome === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    avisar("aviso", "Informe um nome e um e-mail válido.");
    header("Location: cadastro.php");
    exit;
}

if (strlen($senha) < 8) {
    avisar("aviso", "A senha deve conter pelo menos 8 caracteres.");
    header("Location: cadastro.php");
    exit;
}

$hash = password_hash($senha, PASSWORD_DEFAULT);
$stmt = $conexao->prepare("INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, 'usuario')");
$stmt->bind_param("sss", $nome, $email, $hash);
$erro = executarSeguro($stmt);
$stmt->close();
$conexao->close();

if ($erro) {
    avisar("aviso", str_contains($erro, "duplicado") ? "Este e-mail já está cadastrado." : $erro);
    header("Location: cadastro.php");
    exit;
}

avisar("sucesso", "Usuário cadastrado com sucesso! Faça login.");
header("Location: login.php");
exit;
