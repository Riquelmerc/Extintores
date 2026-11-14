<?php

require_once "autenticacao.php";
require_once "layout.php";
require_once "conexao.php";

exigirPerfis(["administrador"]);

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$u = ["nome" => "", "email" => "", "perfil" => "usuario"];

if ($id) {
    $stmt = $conexao->prepare("SELECT nome, email, perfil FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$u) {
        avisar("aviso", "Usuário não encontrado.");
        header("Location: lista_usuarios.php");
        exit;
    }
}

inicioPagina($id ? "Editar usuário" : "Novo usuário");
?>

<h1><?php echo $id ? "Editar usuário" : "Novo usuário"; ?></h1>
<p class="subtitulo">Defina os dados de acesso e o perfil da conta.</p>

<?php mostrarAviso(); ?>

<form action="processa_usuario.php" method="POST">
    <?php echo campoCsrf(); ?>
    <input type="hidden" name="id" value="<?php echo (int) $id; ?>">

    <label for="nome">Nome</label>
    <input type="text" id="nome" name="nome" maxlength="100" value="<?php echo escapar($u["nome"]); ?>" required>

    <label for="email">E-mail</label>
    <input type="email" id="email" name="email" maxlength="150" value="<?php echo escapar($u["email"]); ?>" required>

    <label for="senha">Senha<?php echo $id ? " (deixe em branco para manter a atual)" : ""; ?></label>
    <input type="password" id="senha" name="senha" minlength="8" <?php echo $id ? "" : "required"; ?>>

    <label for="perfil">Perfil</label>
    <select id="perfil" name="perfil" required>
        <?php foreach (["administrador" => "Administrador", "manutencao" => "Manutenção", "usuario" => "Usuário (somente consulta)"] as $valor => $rotulo) { ?>
            <option value="<?php echo $valor; ?>" <?php echo $u["perfil"] === $valor ? "selected" : ""; ?>><?php echo $rotulo; ?></option>
        <?php } ?>
    </select>

    <button type="submit">Salvar usuário</button>
    <a class="botao-link secundario" href="lista_usuarios.php">Cancelar</a>
</form>

<?php
$conexao->close();
fimPagina();
