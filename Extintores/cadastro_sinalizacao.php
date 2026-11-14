<?php
require_once "autenticacao.php";
require_once "layout.php";
require_once "conexao.php";

exigirPerfis(["administrador", "manutencao"]);
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$s = ["id_extintor" => "", "tipo" => "Placa de extintor", "estado" => "", "observacao" => ""];

if ($id) {
    $stmt = $conexao->prepare("SELECT id_extintor, tipo, estado, observacao FROM sinalizacao WHERE id_sinalizacao = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $s = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$s) {
        avisar("aviso", "Sinalização não encontrada.");
        header("Location: lista_sinalizacoes.php");
        exit;
    }
}
$extintores = $conexao->query("SELECT e.id_extintor,e.identificacao,l.andar,l.descricao AS localizacao FROM extintor e JOIN local l ON l.id_local=e.id_local ORDER BY l.andar,l.descricao");
inicioPagina($id ? "Editar sinalização" : "Nova sinalização");
?>
<h1><?php echo $id ? "Editar sinalização" : "Nova sinalização"; ?></h1>
<p class="subtitulo">Um extintor pode possuir vários registros de sinalização, independentemente dos levantamentos.</p>
<?php mostrarAviso(); ?>
<?php if ($extintores->num_rows === 0) { ?>
    <p class="mensagem aviso">Cadastre um extintor antes de registrar uma sinalização.</p>
<?php } else { ?>
<form action="processa_sinalizacao.php" method="POST">
    <?php echo campoCsrf(); ?>
    <input type="hidden" name="id" value="<?php echo (int) $id; ?>">
    <label for="id_extintor">Extintor</label>
    <select id="id_extintor" name="id_extintor" required>
        <option value="">Selecione</option>
        <?php while ($e = $extintores->fetch_assoc()) { ?>
            <option value="<?php echo (int)$e["id_extintor"]; ?>" <?php echo (int)$s["id_extintor"] === (int)$e["id_extintor"] ? "selected" : ""; ?>>
                <?php echo escapar(rotuloExtintor($e["identificacao"],$e["andar"],$e["localizacao"])); ?>
            </option>
        <?php } ?>
    </select>
    <label for="tipo">Tipo de sinalização</label>
    <input type="text" id="tipo" name="tipo" maxlength="80" placeholder="Ex.: Placa de localização" value="<?php echo escapar($s["tipo"]); ?>" required>
    <label for="estado">Estado da sinalização</label>
    <input type="text" id="estado" name="estado" maxlength="50" placeholder="Ex.: Íntegra e visível" value="<?php echo escapar($s["estado"]); ?>" required>
    <label for="observacao">Observações</label>
    <textarea id="observacao" name="observacao" rows="4" placeholder="Ex.: Placa próxima à escada"><?php echo escapar($s["observacao"] ?? ""); ?></textarea>
    <button type="submit">Salvar sinalização</button>
    <a class="botao-link secundario" href="lista_sinalizacoes.php">Cancelar</a>
</form>
<?php } ?>
<?php $conexao->close(); fimPagina();
