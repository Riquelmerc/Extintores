<?php

require_once "autenticacao.php";
exigirPerfis(["administrador", "manutencao"]);
exigirPost("lista_sinalizacoes.php");
require_once "conexao.php";

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if ($id) {
    $stmt = $conexao->prepare("DELETE FROM sinalizacao WHERE id_sinalizacao = ?");
    $stmt->bind_param("i", $id);
    $erro = executarSeguro($stmt);
    $stmt->close();
    $erro ? avisar("aviso", $erro) : avisar("sucesso", "Sinalização excluída com sucesso.");
}

$conexao->close();
header("Location: lista_sinalizacoes.php");
exit;
