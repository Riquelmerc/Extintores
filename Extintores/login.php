<?php

require_once "autenticacao.php";
iniciarSessao();

if (isset($_SESSION["usuario_id"])) {
    header("Location: painel.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Controle de Extintores</title>
    <link rel="stylesheet" href="styles.css?v=6">
</head>
<body class="pagina-autenticacao">
    <main class="cartao-autenticacao">
        <div class="marca-autenticacao">CONTROLE DE EXTINTORES — BLOCO B</div>

        <section class="conteudo-autenticacao">
            <h1>Entrar no sistema</h1>
            <p class="subtitulo">Acesse para consultar ou administrar o sistema.</p>

            <?php mostrarAviso(); ?>

            <form action="processa_login.php" method="POST">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required autofocus>

                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>

                <button type="submit">Entrar</button>
            </form>
        </section>

        <div class="rodape-autenticacao">
            Ainda não possui cadastro?
            <a href="cadastro.php">Cadastrar usuário</a>
        </div>
    </main>
</body>
</html>
