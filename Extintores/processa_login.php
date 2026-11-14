<?php

require_once "autenticacao.php";
iniciarSessao();
require_once "conexao.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$email = trim($_POST["email"] ?? "");
$senha = $_POST["senha"] ?? "";

$stmt = $conexao->prepare("SELECT id, nome, senha, perfil FROM usuarios WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conexao->close();

if ($usuario && password_verify($senha, $usuario["senha"])) {
    session_regenerate_id(true);
    $_SESSION["usuario_id"] = $usuario["id"];
    $_SESSION["usuario_nome"] = $usuario["nome"];
    $_SESSION["usuario_perfil"] = $usuario["perfil"];

    header("Location: painel.php");
    exit;
}

avisar("aviso", "E-mail ou senha incorretos.");
header("Location: login.php");
exit;
