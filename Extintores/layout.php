<?php

require_once "autenticacao.php";

function inicioPagina(string $titulo): void
{
    $perfil = $_SESSION["usuario_perfil"] ?? "";
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo escapar($titulo); ?> — Controle de Extintores</title>
        <link rel="stylesheet" href="styles.css?v=6">
    </head>
    <body>
        <main class="app">
            <header class="topo-app">
                <p class="marca">CONTROLE DE EXTINTORES — BLOCO B</p>
                <nav class="menu" aria-label="Menu principal">
                    <a href="painel.php">Início</a>
                    <a href="lista_extintores.php">Extintores</a>
                    <a href="lista_levantamentos.php">Levantamentos</a>
                    <a href="lista_sinalizacoes.php">Sinalizações</a>
                    <?php if ($perfil === "administrador") { ?>
                        <a href="lista_tipos.php">Tipos</a>
                        <a href="lista_fabricantes.php">Fabricantes</a>
                        <a href="lista_locais.php">Locais</a>
                        <a href="lista_usuarios.php">Usuários</a>
                    <?php } ?>
                    <a href="consome_api.php">API</a>
                    <a class="sair" href="logout.php">Sair</a>
                </nav>
            </header>
            <section class="conteudo-app">
    <?php
}

function fimPagina(): void
{
    ?>
            </section>
        </main>
    </body>
    </html>
    <?php
}

// Formulário de exclusão (POST + CSRF + confirmação).
function botaoExcluir(string $arquivo, int $id, string $texto = "Excluir"): string
{
    return '<form class="form-inline" action="' . escapar($arquivo) . '" method="POST" '
        . 'onsubmit="return confirm(\'Confirma a exclusão deste registro?\');">'
        . campoCsrf()
        . '<input type="hidden" name="id" value="' . $id . '">'
        . '<button type="submit" class="perigo">' . escapar($texto) . '</button></form>';
}

// Quando nao existe numero na etiqueta, usamos a localizacao como referencia.
function rotuloExtintor(mixed $numero, mixed $andar, mixed $local): string
{
    if ($numero !== null && $numero !== '') {
        return 'Extintor nº ' . $numero . ' — ' . $andar . 'º andar, ' . $local;
    }
    return $andar . 'º andar — ' . $local;
}
