<?php

require_once "autenticacao.php";
require_once "layout.php";
require_once "conexao.php";

exigirPerfis(["administrador"]);

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$local = ["andar" => "", "descricao" => ""];

if ($id) {
    $stmt = $conexao->prepare("SELECT andar, descricao FROM local WHERE id_local = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $local = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$local) {
        avisar("aviso", "Local não encontrado.");
        header("Location: lista_locais.php");
        exit;
    }
}

inicioPagina($id ? "Editar local" : "Novo local");
?>

<h1><?php echo $id ? "Editar local" : "Novo local"; ?></h1>
<p class="subtitulo">Onde o extintor poderá ser instalado no Bloco B.</p>

<?php mostrarAviso(); ?>

<form action="processa_local.php" method="POST">
    <?php echo campoCsrf(); ?>
    <input type="hidden" name="id" value="<?php echo (int) $id; ?>">

    <label for="andar">Andar</label>
    <input type="number" id="andar" name="andar" min="0" value="<?php echo escapar($local["andar"]); ?>" required>

    <label for="descricao">Descrição do local</label>
    <input type="text" id="descricao" name="descricao" maxlength="255" placeholder="Ex.: Próximo à escada" value="<?php echo escapar($local["descricao"]); ?>" required>

    <button type="submit">Salvar local</button>
    <a class="botao-link secundario" href="lista_locais.php">Cancelar</a>
</form>

<?php
$conexao->close();
fimPagina();
