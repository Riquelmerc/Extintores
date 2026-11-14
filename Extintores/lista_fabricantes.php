<?php

require_once "autenticacao.php";
require_once "layout.php";
require_once "conexao.php";

exigirLogin();

$busca = trim($_GET["busca"] ?? "");
$like = "%" . $busca . "%";
$stmt = $conexao->prepare("SELECT id_fabricante, nome FROM fabricante WHERE nome LIKE ? ORDER BY nome");
$stmt->bind_param("s", $like);
$stmt->execute();
$resultado = $stmt->get_result();

inicioPagina("Fabricantes");
?>

<div class="titulo-com-acao">
    <div>
        <h1>Fabricantes</h1>
        <p class="subtitulo">Empresas fabricantes dos extintores.</p>
    </div>
    <?php if (ehAdmin()) { ?>
        <a class="botao-link" href="cadastro_fabricante.php">Novo fabricante</a>
    <?php } ?>
</div>

<?php mostrarAviso(); ?>

<form class="filtros" method="GET">
    <input type="search" name="busca" placeholder="Buscar por nome" value="<?php echo escapar($busca); ?>">
    <button type="submit">Buscar</button>
</form>

<div class="tabela-responsiva">
    <table>
        <tr><th>ID</th><th>Nome</th><?php if (ehAdmin()) echo "<th>Ações</th>"; ?></tr>
        <?php while ($f = $resultado->fetch_assoc()) { ?>
            <tr>
                <td><?php echo escapar($f["id_fabricante"]); ?></td>
                <td><?php echo escapar($f["nome"]); ?></td>
                <?php if (ehAdmin()) { ?>
                    <td class="acoes">
                        <a class="botao-link pequeno" href="cadastro_fabricante.php?id=<?php echo (int) $f["id_fabricante"]; ?>">Editar</a>
                        <?php echo botaoExcluir("exclui_fabricante.php", (int) $f["id_fabricante"]); ?>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
        <?php if ($resultado->num_rows === 0) echo '<tr><td colspan="3">Nenhum registro encontrado.</td></tr>'; ?>
    </table>
</div>

<?php
$stmt->close();
$conexao->close();
fimPagina();
