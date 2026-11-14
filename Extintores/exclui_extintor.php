<?php

require_once "autenticacao.php";
exigirPerfis(["administrador"]);
exigirPost("lista_extintores.php");
require_once "conexao.php";

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if ($id) {
    $stmt = $conexao->prepare("DELETE FROM extintor WHERE id_extintor = ?");
    $stmt->bind_param("i", $id);
    $erro = executarSeguro($stmt);
    $stmt->close();
    $erro ? avisar("aviso", $erro) : avisar("sucesso", "Extintor excluído (junto com seus levantamentos).");
}

$conexao->close();
header("Location: lista_extintores.php");
exit;
