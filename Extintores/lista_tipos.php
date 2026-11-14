<?php

require_once "autenticacao.php";
require_once "layout.php";
require_once "conexao.php";

exigirLogin();

$busca = trim($_GET["busca"] ?? "");
$like = "%" . $busca . "%";
$stmt = $conexao->prepare("SELECT id_tipo, descricao, classe_fogo FROM tipo_extintor
                           WHERE descricao LIKE ? OR classe_fogo LIKE ? ORDER BY descricao");
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$resultado = $stmt->get_result();

inicioPagina("Tipos de extintor");
?>

<div class="titulo-com-acao">
    <div>
        <h1>Tipos de extintor</h1>
        <p class="subtitulo">Classificações de equipamentos e classes de fogo atendidas.</p>
    </div>
    <?php if (ehAdmin()) { ?>
        <a class="botao-link" href="cadastro_tipo.php">Novo tipo</a>
    <?php } ?>
</div>

<?php mostrarAviso(); ?>

<form class="filtros" method="GET">
    <input type="search" name="busca" placeholder="Buscar por descrição ou classe" value="<?php echo escapar($busca); ?>">
    <button type="submit">Buscar</button>
</form>

<div class="tabela-responsiva">
    <table>
        <tr><th>ID</th><th>Descrição</th><th>Classe de fogo</th><?php if (ehAdmin()) echo "<th>Ações</th>"; ?></tr>
        <?php while ($tipo = $resultado->fetch_assoc()) { ?>
            <tr>
                <td><?php echo escapar($tipo["id_tipo"]); ?></td>
                <td><?php echo escapar($tipo["descricao"]); ?></td>
                <td><?php echo escapar($tipo["classe_fogo"]); ?></td>
                <?php if (ehAdmin()) { ?>
                    <td class="acoes">
                        <a class="botao-link pequeno" href="cadastro_tipo.php?id=<?php echo (int) $tipo["id_tipo"]; ?>">Editar</a>
                        <?php echo botaoExcluir("exclui_tipo.php", (int) $tipo["id_tipo"]); ?>
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
