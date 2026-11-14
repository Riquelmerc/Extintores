<?php

require_once "autenticacao.php";
require_once "layout.php";
require_once "conexao.php";

exigirPerfis(["administrador"]);

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$fabricante = ["nome" => ""];

if ($id) {
    $stmt = $conexao->prepare("SELECT nome FROM fabricante WHERE id_fabricante = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $fabricante = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$fabricante) {
        avisar("aviso", "Fabricante não encontrado.");
        header("Location: lista_fabricantes.php");
        exit;
    }
}

inicioPagina($id ? "Editar fabricante" : "Novo fabricante");
?>

<h1><?php echo $id ? "Editar fabricante" : "Novo fabricante"; ?></h1>
<p class="subtitulo">Fabricantes que poderão ser associados aos extintores.</p>

<?php mostrarAviso(); ?>

<form action="processa_fabricante.php" method="POST">
    <?php echo campoCsrf(); ?>
    <input type="hidden" name="id" value="<?php echo (int) $id; ?>">

    <label for="nome">Nome do fabricante</label>
    <input type="text" id="nome" name="nome" maxlength="100" value="<?php echo escapar($fabricante["nome"]); ?>" required>

    <button type="submit">Salvar fabricante</button>
    <a class="botao-link secundario" href="lista_fabricantes.php">Cancelar</a>
</form>

<?php
$conexao->close();
fimPagina();
