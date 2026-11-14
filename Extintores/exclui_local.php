<?php

require_once "autenticacao.php";
exigirPerfis(["administrador"]);
exigirPost("lista_locais.php");
require_once "conexao.php";

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if ($id) {
    $stmt = $conexao->prepare("DELETE FROM local WHERE id_local = ?");
    $stmt->bind_param("i", $id);
    $erro = executarSeguro($stmt, "Este local possui extintores cadastrados e não pode ser excluído.");
    $stmt->close();
    $erro ? avisar("aviso", $erro) : avisar("sucesso", "Local excluído com sucesso.");
}

$conexao->close();
header("Location: lista_locais.php");
exit;
