<?php

require_once "autenticacao.php";
exigirPerfis(["administrador", "manutencao"]);
exigirPost("lista_levantamentos.php");
require_once "conexao.php";

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$idExtintor = filter_input(INPUT_POST, "id_extintor", FILTER_VALIDATE_INT);
$data = $_POST["data_levantamento"] ?? "";
$textoAcesso = trim((string)($_POST["acesso_desobstruido"] ?? ""));
$acesso = $textoAcesso === "" ? null : filter_var($textoAcesso, FILTER_VALIDATE_INT);
$estado = trim($_POST["estado_extintor"] ?? "");
$observacao = trim($_POST["observacao"] ?? "");
$responsavel = trim($_POST["responsavel"] ?? "");
$destino = "cadastro_levantamento.php" . ($id ? "?id=$id" : "");

$d = DateTime::createFromFormat("Y-m-d", $data);
$dataValida = $d && $d->format("Y-m-d") === $data;

if (!$idExtintor || !$dataValida || ($acesso !== null && $acesso !== 0 && $acesso !== 1) || $estado === "") {
    avisar("aviso", "Preencha todos os campos obrigatórios corretamente.");
    header("Location: $destino");
    exit;
}

if ($id) {
    $stmt = $conexao->prepare("UPDATE levantamento SET id_extintor = ?, data_levantamento = ?,
                               acesso_desobstruido = ?, estado_extintor = ?, responsavel = ?, observacao = ?
                               WHERE id_levantamento = ?");
    $stmt->bind_param("isisssi", $idExtintor, $data, $acesso, $estado, $responsavel, $observacao, $id);
} else {
    $stmt = $conexao->prepare("INSERT INTO levantamento
        (id_extintor, data_levantamento, acesso_desobstruido, estado_extintor, responsavel, observacao)
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isisss", $idExtintor, $data, $acesso, $estado, $responsavel, $observacao);
}

$erro = executarSeguro($stmt);
$stmt->close();
$conexao->close();

if ($erro) {
    avisar("aviso", $erro);
    header("Location: $destino");
    exit;
}

avisar("sucesso", $id ? "Levantamento atualizado com sucesso." : "Levantamento cadastrado com sucesso.");
header("Location: lista_levantamentos.php");
exit;
