<?php
require_once 'autenticacao.php';
require_once 'layout.php';
require_once 'conexao.php';
exigirPerfis(['administrador','manutencao']);
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$e=['identificacao'=>null,'capacidade'=>null,'data_validade_carga'=>null,'id_tipo'=>null,'id_fabricante'=>null,'id_local'=>null];
if ($id) {
    $stmt=$conexao->prepare('SELECT identificacao,capacidade,data_validade_carga,id_tipo,id_fabricante,id_local FROM extintor WHERE id_extintor=?');
    $stmt->bind_param('i',$id); $stmt->execute(); $e=$stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$e) { avisar('aviso','Extintor não encontrado.');header('Location: lista_extintores.php');exit; }
}
$tipos=$conexao->query('SELECT id_tipo,descricao,classe_fogo FROM tipo_extintor ORDER BY descricao');
$fabricantes=$conexao->query('SELECT id_fabricante,nome FROM fabricante ORDER BY nome');
$locais=$conexao->query('SELECT id_local,andar,descricao FROM local ORDER BY andar,descricao');
inicioPagina($id?'Editar extintor':'Novo extintor');
?>
<h1><?php echo $id?'Editar extintor':'Novo extintor'; ?></h1>
<p class="subtitulo">Número, capacidade, validade, tipo e fabricante podem ficar em branco até serem confirmados. A localização é obrigatória.</p>
<?php mostrarAviso(); ?>
<?php if ($locais->num_rows===0) { ?>
<p class="mensagem aviso">Cadastre ao menos um local antes de incluir um extintor.</p>
<?php } else { ?>
<form action="processa_extintor.php" method="POST">
<?php echo campoCsrf(); ?><input type="hidden" name="id" value="<?php echo (int)$id; ?>">
<label for="identificacao">Número da etiqueta (opcional)</label>
<input type="number" id="identificacao" name="identificacao" min="1" placeholder="Deixe vazio se não foi possível confirmar" value="<?php echo escapar($e['identificacao']); ?>">
<label for="capacidade">Capacidade (<span id="unidade-capacidade">kg</span>) — opcional</label>
<input type="number" id="capacidade" name="capacidade" min="0.01" step="0.01" value="<?php echo escapar($e['capacidade']); ?>">
<label for="data_validade_carga">Validade da carga (opcional)</label>
<input type="date" id="data_validade_carga" name="data_validade_carga" value="<?php echo escapar($e['data_validade_carga']); ?>">
<label for="id_tipo">Tipo de extintor (opcional)</label>
<select id="id_tipo" name="id_tipo"><option value="">Não identificado</option>
<?php while($t=$tipos->fetch_assoc()){ ?>
<option value="<?php echo (int)$t['id_tipo']; ?>" data-unidade="<?php echo preg_match('/água|agua/iu',$t['descricao'])?'L':'kg'; ?>" <?php echo $e['id_tipo']!==null && (int)$e['id_tipo']===(int)$t['id_tipo']?'selected':''; ?>><?php echo escapar($t['descricao'].($t['classe_fogo']?' — classe '.$t['classe_fogo']:'')); ?></option>
<?php } ?></select>
<label for="id_fabricante">Fabricante (opcional; prestadora de manutenção não é fabricante)</label>
<select id="id_fabricante" name="id_fabricante"><option value="">Não identificado</option>
<?php while($f=$fabricantes->fetch_assoc()){ ?>
<option value="<?php echo (int)$f['id_fabricante']; ?>" <?php echo $e['id_fabricante']!==null && (int)$e['id_fabricante']===(int)$f['id_fabricante']?'selected':''; ?>><?php echo escapar($f['nome']); ?></option>
<?php } ?></select>
<label for="id_local">Localização no Bloco B</label>
<select id="id_local" name="id_local" required><option value="">Selecione</option>
<?php while($l=$locais->fetch_assoc()){ ?>
<option value="<?php echo (int)$l['id_local']; ?>" <?php echo (int)$e['id_local']===(int)$l['id_local']?'selected':''; ?>><?php echo escapar($l['andar'].'º andar — '.$l['descricao']); ?></option>
<?php } ?></select>
<script>(function(){const tipo=document.getElementById('id_tipo'), unidade=document.getElementById('unidade-capacidade');function atualizar(){unidade.textContent=tipo.selectedOptions[0]?.dataset.unidade||'kg';}tipo.addEventListener('change',atualizar);atualizar();})();</script>
<button type="submit">Salvar extintor</button><a class="botao-link secundario" href="lista_extintores.php">Cancelar</a>
</form>
<?php } ?>
<?php $conexao->close(); fimPagina(); ?>
