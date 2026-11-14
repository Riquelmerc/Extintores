<?php
require_once "autenticacao.php";
exigirPerfis(["administrador", "manutencao"]);
exigirPost("lista_sinalizacoes.php");
require_once "conexao.php";
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$idExtintor = filter_input(INPUT_POST, "id_extintor", FILTER_VALIDATE_INT);
$tipo = trim($_POST["tipo"] ?? "");
$estado = trim($_POST["estado"] ?? "");
$observacao = trim($_POST["observacao"] ?? "");
$destino = "cadastro_sinalizacao.php" . ($id ? "?id=$id" : "");
if (!$idExtintor || $tipo === "" || $estado === "" || strlen($tipo) > 240 || strlen($estado) > 150) {
    avisar("aviso", "Preencha o extintor, o tipo e o estado da sinalização corretamente.");
    header("Location: $destino");
    exit;
}
if ($id) {
    $stmt = $conexao->prepare("UPDATE sinalizacao SET id_extintor=?, tipo=?, estado=?, observacao=? WHERE id_sinalizacao=?");
    $stmt->bind_param("isssi", $idExtintor, $tipo, $estado, $observacao, $id);
} else {
    $stmt = $conexao->prepare("INSERT INTO sinalizacao (id_extintor, tipo, estado, observacao) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $idExtintor, $tipo, $estado, $observacao);
}
$erro = executarSeguro($stmt);
$stmt->close();
$conexao->close();
if ($erro) {
    avisar("aviso", $erro);
    header("Location: $destino");
    exit;
}
avisar("sucesso", $id ? "Sinalização atualizada com sucesso." : "Sinalização registrada com sucesso.");
header("Location: lista_sinalizacoes.php");
exit;
