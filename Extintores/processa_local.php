<?php

require_once "autenticacao.php";
exigirPerfis(["administrador"]);
exigirPost("lista_locais.php");
require_once "conexao.php";

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$andar = filter_input(INPUT_POST, "andar", FILTER_VALIDATE_INT);
$descricao = trim($_POST["descricao"] ?? "");
$destino = "cadastro_local.php" . ($id ? "?id=$id" : "");

if ($andar === false || $andar === null || $andar < 0 || $descricao === "") {
    avisar("aviso", "Informe um andar válido e a descrição do local.");
    header("Location: $destino");
    exit;
}

if ($id) {
    $stmt = $conexao->prepare("UPDATE local SET andar = ?, descricao = ? WHERE id_local = ?");
    $stmt->bind_param("isi", $andar, $descricao, $id);
} else {
    $stmt = $conexao->prepare("INSERT INTO local (andar, descricao) VALUES (?, ?)");
    $stmt->bind_param("is", $andar, $descricao);
}

$erro = executarSeguro($stmt);
$stmt->close();
$conexao->close();

if ($erro) {
    avisar("aviso", $erro);
    header("Location: $destino");
    exit;
}

avisar("sucesso", $id ? "Local atualizado com sucesso." : "Local cadastrado com sucesso.");
header("Location: lista_locais.php");
exit;
