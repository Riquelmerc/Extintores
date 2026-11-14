<?php

require_once "autenticacao.php";
exigirPerfis(["administrador"]);
exigirPost("lista_fabricantes.php");
require_once "conexao.php";

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$nome = trim($_POST["nome"] ?? "");
$destino = "cadastro_fabricante.php" . ($id ? "?id=$id" : "");

if ($nome === "") {
    avisar("aviso", "Informe o nome do fabricante.");
    header("Location: $destino");
    exit;
}

if ($id) {
    $stmt = $conexao->prepare("UPDATE fabricante SET nome = ? WHERE id_fabricante = ?");
    $stmt->bind_param("si", $nome, $id);
} else {
    $stmt = $conexao->prepare("INSERT INTO fabricante (nome) VALUES (?)");
    $stmt->bind_param("s", $nome);
}

$erro = executarSeguro($stmt);
$stmt->close();
$conexao->close();

if ($erro) {
    avisar("aviso", $erro);
    header("Location: $destino");
    exit;
}

avisar("sucesso", $id ? "Fabricante atualizado com sucesso." : "Fabricante cadastrado com sucesso.");
header("Location: lista_fabricantes.php");
exit;
