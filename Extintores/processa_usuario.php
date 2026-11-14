<?php

require_once "autenticacao.php";
exigirPerfis(["administrador"]);
exigirPost("lista_usuarios.php");
require_once "conexao.php";

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");
$senha = $_POST["senha"] ?? "";
$perfil = $_POST["perfil"] ?? "";
$destino = "cadastro_usuario.php" . ($id ? "?id=$id" : "");

if ($nome === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($perfil, ["administrador", "usuario", "manutencao"], true)) {
    avisar("aviso", "Informe nome, e-mail válido e perfil.");
    header("Location: $destino");
    exit;
}

if (($id === false || $id === null || $id === 0 || $senha !== "") && strlen($senha) < 8) {
    avisar("aviso", "A senha deve conter pelo menos 8 caracteres.");
    header("Location: $destino");
    exit;
}

// Evita que o administrador logado retire o próprio acesso por engano.
if ($id && (int) $_SESSION["usuario_id"] === $id && $perfil !== "administrador") {
    avisar("aviso", "Você não pode remover o seu próprio perfil de administrador.");
    header("Location: $destino");
    exit;
}

if ($id) {
    if ($senha !== "") {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $conexao->prepare("UPDATE usuarios SET nome = ?, email = ?, perfil = ?, senha = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nome, $email, $perfil, $hash, $id);
    } else {
        $stmt = $conexao->prepare("UPDATE usuarios SET nome = ?, email = ?, perfil = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nome, $email, $perfil, $id);
    }
} else {
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $conexao->prepare("INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $email, $hash, $perfil);
}

$erro = executarSeguro($stmt);
$stmt->close();
$conexao->close();

if ($erro) {
    avisar("aviso", $erro === "Já existe um registro com esses dados (valor duplicado)." ? "Este e-mail já está cadastrado." : $erro);
    header("Location: $destino");
    exit;
}

if ($id && (int) $_SESSION["usuario_id"] === $id) {
    $_SESSION["usuario_nome"] = $nome;
}

avisar("sucesso", $id ? "Usuário atualizado com sucesso." : "Usuário cadastrado com sucesso.");
header("Location: lista_usuarios.php");
exit;
