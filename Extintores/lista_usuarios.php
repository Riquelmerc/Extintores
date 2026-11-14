<?php

require_once "autenticacao.php";
require_once "layout.php";
require_once "conexao.php";

exigirPerfis(["administrador"]);

$busca = trim($_GET["busca"] ?? "");
$like = "%" . $busca . "%";
$stmt = $conexao->prepare("SELECT id, nome, email, perfil FROM usuarios WHERE nome LIKE ? OR email LIKE ? ORDER BY nome");
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$resultado = $stmt->get_result();

inicioPagina("Usuários e perfis");
?>

<div class="titulo-com-acao">
    <div>
        <h1>Usuários e perfis</h1>
        <p class="subtitulo">O administrador define o nível de acesso de cada conta do sistema.</p>
    </div>
    <a class="botao-link" href="cadastro_usuario.php">Novo usuário</a>
</div>

<?php mostrarAviso(); ?>

<form class="filtros" method="GET">
    <input type="search" name="busca" placeholder="Buscar por nome ou e-mail" value="<?php echo escapar($busca); ?>">
    <button type="submit">Buscar</button>
</form>

<div class="tabela-responsiva">
    <table>
        <tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Ações</th></tr>
        <?php while ($u = $resultado->fetch_assoc()) { ?>
            <tr>
                <td><?php echo escapar($u["nome"]); ?></td>
                <td><?php echo escapar($u["email"]); ?></td>
                <td><?php echo escapar(ucfirst($u["perfil"])); ?></td>
                <td class="acoes">
                    <a class="botao-link pequeno" href="cadastro_usuario.php?id=<?php echo (int) $u["id"]; ?>">Editar</a>
                    <?php if ((int) $u["id"] !== (int) $_SESSION["usuario_id"]) echo botaoExcluir("exclui_usuario.php", (int) $u["id"]); ?>
                </td>
            </tr>
        <?php } ?>
        <?php if ($resultado->num_rows === 0) echo '<tr><td colspan="4">Nenhum usuário encontrado.</td></tr>'; ?>
    </table>
</div>

<?php
$stmt->close();
$conexao->close();
fimPagina();
