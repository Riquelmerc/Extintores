<?php

require_once "autenticacao.php";
require_once "layout.php";
require_once "conexao.php";

exigirPerfis(["administrador", "manutencao"]);

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$lev = ["id_extintor" => "", "data_levantamento" => date("Y-m-d"), "acesso_desobstruido" => null,
        "estado_extintor" => "Não avaliado", "responsavel" => "", "observacao" => ""];

if ($id) {
    $stmt = $conexao->prepare("SELECT id_extintor, data_levantamento, acesso_desobstruido, estado_extintor, responsavel, observacao
                               FROM levantamento WHERE id_levantamento = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $lev = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$lev) {
        avisar("aviso", "Levantamento não encontrado.");
        header("Location: lista_levantamentos.php");
        exit;
    }
}

$extintores = $conexao->query("SELECT e.id_extintor,e.identificacao,l.andar,l.descricao AS localizacao FROM extintor e JOIN local l ON l.id_local=e.id_local ORDER BY l.andar,l.descricao");
inicioPagina($id ? "Editar levantamento" : "Novo levantamento");
?>

<h1><?php echo $id ? "Editar levantamento" : "Novo levantamento"; ?></h1>
<p class="subtitulo">Registre a verificação realizada em um extintor.</p>

<?php mostrarAviso(); ?>

<?php if ($extintores->num_rows === 0) { ?>
    <p class="mensagem aviso">Cadastre um extintor antes de registrar um levantamento.</p>
<?php } else { ?>
    <form action="processa_levantamento.php" method="POST">
        <?php echo campoCsrf(); ?>
        <input type="hidden" name="id" value="<?php echo (int) $id; ?>">

        <label for="id_extintor">Extintor verificado</label>
        <select id="id_extintor" name="id_extintor" required>
            <option value="">Selecione</option>
            <?php while ($e = $extintores->fetch_assoc()) { ?>
                <option value="<?php echo (int) $e["id_extintor"]; ?>" <?php echo (int) $lev["id_extintor"] === (int) $e["id_extintor"] ? "selected" : ""; ?>>
                    <?php echo escapar(rotuloExtintor($e["identificacao"],$e["andar"],$e["localizacao"])); ?>
                </option>
            <?php } ?>
        </select>

        <label for="data_levantamento">Data da verificação</label>
        <input type="date" id="data_levantamento" name="data_levantamento" value="<?php echo escapar($lev["data_levantamento"]); ?>" required>

        <label for="acesso_desobstruido">Acesso estava desobstruído?</label>
        <select id="acesso_desobstruido" name="acesso_desobstruido">
            <option value="" <?php echo $lev["acesso_desobstruido"] === null ? "selected" : ""; ?>>Não verificado</option>
            <option value="1" <?php echo $lev["acesso_desobstruido"] !== null && (int) $lev["acesso_desobstruido"] === 1 ? "selected" : ""; ?>>Sim</option>
            <option value="0" <?php echo $lev["acesso_desobstruido"] !== null && (int) $lev["acesso_desobstruido"] === 0 ? "selected" : ""; ?>>Não</option>
        </select>

        <label for="estado_extintor">Estado do extintor</label>
        <input type="text" id="estado_extintor" name="estado_extintor" maxlength="100" placeholder="Ex.: Em boas condições" value="<?php echo escapar($lev["estado_extintor"]); ?>" required>

        <label for="responsavel">Responsável pela verificação (conforme o Notion)</label>
        <input type="text" id="responsavel" name="responsavel" maxlength="120" value="<?php echo escapar($lev["responsavel"] ?? ""); ?>" placeholder="Ex.: Andrey, Riquelme">

        <label for="observacao">Observação</label>
        <textarea id="observacao" name="observacao" rows="4" placeholder="Registre detalhes relevantes da verificação."><?php echo escapar($lev["observacao"]); ?></textarea>

        <button type="submit">Salvar levantamento</button>
        <a class="botao-link secundario" href="lista_levantamentos.php">Cancelar</a>
    </form>
<?php } ?>

<?php
$conexao->close();
fimPagina();
