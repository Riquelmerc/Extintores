<?php

require_once "autenticacao.php";
exigirPerfis(["administrador"]);
exigirPost("lista_fabricantes.php");
require_once "conexao.php";

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if ($id) {
    $stmt = $conexao->prepare("DELETE FROM fabricante WHERE id_fabricante = ?");
    $stmt->bind_param("i", $id);
    $erro = executarSeguro($stmt, "Este fabricante está sendo usado por extintores e não pode ser excluído.");
    $stmt->close();
    $erro ? avisar("aviso", $erro) : avisar("sucesso", "Fabricante excluído com sucesso.");
}

$conexao->close();
header("Location: lista_fabricantes.php");
exit;
