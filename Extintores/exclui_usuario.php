<?php

require_once "autenticacao.php";
exigirPerfis(["administrador"]);
exigirPost("lista_usuarios.php");
require_once "conexao.php";

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if ($id && (int) $_SESSION["usuario_id"] === $id) {
    avisar("aviso", "Você não pode excluir a sua própria conta.");
} elseif ($id) {
    $stmt = $conexao->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $erro = executarSeguro($stmt);
    $stmt->close();
    $erro ? avisar("aviso", $erro) : avisar("sucesso", "Usuário excluído com sucesso.");
}

$conexao->close();
header("Location: lista_usuarios.php");
exit;
