<?php

require_once "autenticacao.php";
require_once "layout.php";
require_once "conexao.php";

exigirPerfis(["administrador"]);

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$tipo = ["descricao" => "", "classe_fogo" => ""];

if ($id) {
    $stmt = $conexao->prepare("SELECT descricao, classe_fogo FROM tipo_extintor WHERE id_tipo = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $tipo = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$tipo) {
        avisar("aviso", "Tipo não encontrado.");
        header("Location: lista_tipos.php");
        exit;
    }
}

inicioPagina($id ? "Editar tipo" : "Novo tipo");
?>

<h1><?php echo $id ? "Editar tipo de extintor" : "Novo tipo de extintor"; ?></h1>
<p class="subtitulo">Defina o tipo do equipamento e as classes de fogo atendidas.</p>

<?php mostrarAviso(); ?>

<form action="processa_tipo.php" method="POST">
    <?php echo campoCsrf(); ?>
    <input type="hidden" name="id" value="<?php echo (int) $id; ?>">

    <label for="descricao">Descrição</label>
    <input type="text" id="descricao" name="descricao" maxlength="100" value="<?php echo escapar($tipo["descricao"]); ?>" required>

    <label for="classe_fogo">Classe de fogo</label>
    <input type="text" id="classe_fogo" name="classe_fogo" maxlength="20" placeholder="Ex.: A, B, C" value="<?php echo escapar($tipo["classe_fogo"]); ?>" required>

    <button type="submit">Salvar tipo</button>
    <a class="botao-link secundario" href="lista_tipos.php">Cancelar</a>
</form>

<?php
$conexao->close();
fimPagina();
