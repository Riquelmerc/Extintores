<?php

require_once "autenticacao.php";
exigirPerfis(["administrador"]);
exigirPost("lista_tipos.php");
require_once "conexao.php";

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$descricao = trim($_POST["descricao"] ?? "");
$classe = trim($_POST["classe_fogo"] ?? "");
$destino = "cadastro_tipo.php" . ($id ? "?id=$id" : "");

if ($descricao === "" || $classe === "") {
    avisar("aviso", "Preencha a descrição e a classe de fogo.");
    header("Location: $destino");
    exit;
}

if ($id) {
    $stmt = $conexao->prepare("UPDATE tipo_extintor SET descricao = ?, classe_fogo = ? WHERE id_tipo = ?");
    $stmt->bind_param("ssi", $descricao, $classe, $id);
} else {
    $stmt = $conexao->prepare("INSERT INTO tipo_extintor (descricao, classe_fogo) VALUES (?, ?)");
    $stmt->bind_param("ss", $descricao, $classe);
}

$erro = executarSeguro($stmt);
$stmt->close();
$conexao->close();

if ($erro) {
    avisar("aviso", $erro);
    header("Location: $destino");
    exit;
}

avisar("sucesso", $id ? "Tipo atualizado com sucesso." : "Tipo cadastrado com sucesso.");
header("Location: lista_tipos.php");
exit;
