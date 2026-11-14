<?php

require_once "autenticacao.php";
exigirPerfis(["administrador"]);
exigirPost("lista_tipos.php");
require_once "conexao.php";

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if ($id) {
    $stmt = $conexao->prepare("DELETE FROM tipo_extintor WHERE id_tipo = ?");
    $stmt->bind_param("i", $id);
    $erro = executarSeguro($stmt, "Este tipo está sendo usado por extintores e não pode ser excluído.");
    $stmt->close();
    $erro ? avisar("aviso", $erro) : avisar("sucesso", "Tipo excluído com sucesso.");
}

$conexao->close();
header("Location: lista_tipos.php");
exit;
