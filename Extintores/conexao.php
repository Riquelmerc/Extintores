<?php
// Projeto de Controle de Extintores do Bloco B - configuracao para XAMPP.
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "controle_extintores_teste";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexao = new mysqli($servidor, $usuario, $senha);
    $conexao->set_charset("utf8mb4");

    // So inicializa automaticamente no acesso HTTP pelo proprio localhost.
    $hostAcesso = strtolower((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
    $hostAcesso = preg_replace('/:\d+$/', '', $hostAcesso);
    $ambienteLocal = in_array($hostAcesso, ['localhost', '127.0.0.1', '[::1]'], true);

    if ($ambienteLocal) {
        $conexao->query("CREATE DATABASE IF NOT EXISTS `$banco` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    $conexao->select_db($banco);
    if ($ambienteLocal) {
        require_once __DIR__ . '/banco/inicializacao.php';
        inicializarBancoExtintores($conexao);
    }
} catch (Throwable $erro) {
    http_response_code(500);
    error_log('Falha ao abrir/inicializar controle_extintores_teste: ' . $erro->getMessage());
    exit('Não foi possível conectar ou inicializar o banco. Verifique Apache, MySQL e os dados de conexao.php.');
}
