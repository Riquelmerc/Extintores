<?php

require_once "autenticacao.php";
iniciarSessao();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de usuário — Controle de Extintores</title>
    <link rel="stylesheet" href="styles.css?v=6">
</head>
<body class="pagina-autenticacao">
    <main class="cartao-autenticacao">
        <div class="marca-autenticacao">CONTROLE DE EXTINTORES — BLOCO B</div>

        <section class="conteudo-autenticacao">
            <h1>Cadastro de usuário</h1>
            <p class="subtitulo">Crie uma conta para consultar o sistema. O administrador pode alterar o seu perfil depois.</p>

            <?php mostrarAviso(); ?>

            <form action="processa_cadastro.php" method="POST">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" maxlength="100" required>

                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" maxlength="150" required>

                <label for="senha">Senha (mínimo 8 caracteres)</label>
                <input type="password" id="senha" name="senha" minlength="8" required>

                <button type="submit">Cadastrar</button>
            </form>
        </section>

        <div class="rodape-autenticacao">
            Já possui conta? <a href="login.php">Entrar</a>
        </div>
    </main>
</body>
</html>
