<?php

require_once "autenticacao.php";
require_once "layout.php";
require_once "conexao.php";

exigirLogin();

$busca = trim($_GET["busca"] ?? "");
$like = "%" . $busca . "%";
$stmt = $conexao->prepare("SELECT id_local, andar, descricao FROM local
                           WHERE descricao LIKE ? OR CAST(andar AS CHAR) LIKE ? ORDER BY andar, descricao");
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$resultado = $stmt->get_result();

inicioPagina("Locais");
?>

<div class="titulo-com-acao">
    <div>
        <h1>Locais</h1>
        <p class="subtitulo">Pontos do Bloco B onde os extintores estão instalados.</p>
    </div>
    <?php if (ehAdmin()) { ?>
        <a class="botao-link" href="cadastro_local.php">Novo local</a>
    <?php } ?>
</div>

<?php mostrarAviso(); ?>

<form class="filtros" method="GET">
    <input type="search" name="busca" placeholder="Buscar por andar ou descrição" value="<?php echo escapar($busca); ?>">
    <button type="submit">Buscar</button>
</form>

<div class="tabela-responsiva">
    <table>
        <tr><th>ID</th><th>Andar</th><th>Descrição</th><?php if (ehAdmin()) echo "<th>Ações</th>"; ?></tr>
        <?php while ($l = $resultado->fetch_assoc()) { ?>
            <tr>
                <td><?php echo escapar($l["id_local"]); ?></td>
                <td><?php echo escapar($l["andar"]); ?></td>
                <td><?php echo escapar($l["descricao"]); ?></td>
                <?php if (ehAdmin()) { ?>
                    <td class="acoes">
                        <a class="botao-link pequeno" href="cadastro_local.php?id=<?php echo (int) $l["id_local"]; ?>">Editar</a>
                        <?php echo botaoExcluir("exclui_local.php", (int) $l["id_local"]); ?>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
        <?php if ($resultado->num_rows === 0) echo '<tr><td colspan="4">Nenhum registro encontrado.</td></tr>'; ?>
    </table>
</div>

<?php
$stmt->close();
$conexao->close();
fimPagina();
