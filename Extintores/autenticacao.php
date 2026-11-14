<?php

function iniciarSessao(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function exigirLogin(): void
{
    iniciarSessao();

    if (!isset($_SESSION["usuario_id"])) {
        header("Location: login.php");
        exit;
    }
}

function exigirPerfis(array $perfisPermitidos): void
{
    exigirLogin();

    $perfil = $_SESSION["usuario_perfil"] ?? "";

    if (!in_array($perfil, $perfisPermitidos, true)) {
        http_response_code(403);
        exit("Você não possui permissão para acessar esta página.");
    }
}

function escapar(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

// ---------- Proteção contra CSRF ----------

function tokenCsrf(): string
{
    iniciarSessao();

    if (empty($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf"];
}

function campoCsrf(): string
{
    return '<input type="hidden" name="csrf" value="' . escapar(tokenCsrf()) . '">';
}

function exigirPost(string $voltarPara): void
{
    iniciarSessao();

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: $voltarPara");
        exit;
    }

    $enviado = $_POST["csrf"] ?? "";

    if (!hash_equals($_SESSION["csrf"] ?? "", $enviado)) {
        http_response_code(403);
        exit("Requisição inválida. Volte e tente novamente.");
    }
}

// ---------- Mensagens entre páginas ----------

function avisar(string $tipo, string $texto): void
{
    iniciarSessao();
    $_SESSION["aviso"] = ["tipo" => $tipo, "texto" => $texto];
}

function mostrarAviso(): void
{
    iniciarSessao();

    if (!empty($_SESSION["aviso"])) {
        $aviso = $_SESSION["aviso"];
        unset($_SESSION["aviso"]);
        echo '<p class="mensagem ' . escapar($aviso["tipo"]) . '">' . escapar($aviso["texto"]) . '</p>';
    }
}

function podeAlterar(): bool
{
    return in_array($_SESSION["usuario_perfil"] ?? "", ["administrador", "manutencao"], true);
}

function ehAdmin(): bool
{
    return ($_SESSION["usuario_perfil"] ?? "") === "administrador";
}

// Executa um INSERT/UPDATE/DELETE e devolve mensagem amigável em caso de erro.
function executarSeguro(mysqli_stmt $stmt, string $mensagemRestricao = "Registro em uso por outros dados."): ?string
{
    try {
        $stmt->execute();
        return null;
    } catch (mysqli_sql_exception $e) {
        return match ($e->getCode()) {
            1451 => $mensagemRestricao,
            1452 => "Um dos itens selecionados não existe mais.",
            1062 => "Já existe um registro com esses dados (valor duplicado).",
            default => "Não foi possível salvar. Verifique os dados informados.",
        };
    }
}
