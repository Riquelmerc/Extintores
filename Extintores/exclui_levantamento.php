<?php

require_once "autenticacao.php";
exigirPerfis(["administrador", "manutencao"]);
exigirPost("lista_levantamentos.php");
require_once "conexao.php";

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if ($id) {
    $stmt = $conexao->prepare("DELETE FROM levantamento WHERE id_levantamento = ?");
    $stmt->bind_param("i", $id);
    $erro = executarSeguro($stmt);
    $stmt->close();
    $erro ? avisar("aviso", $erro) : avisar("sucesso", "Levantamento excluído com sucesso.");
}

$conexao->close();
header("Location: lista_levantamentos.php");
exit;
