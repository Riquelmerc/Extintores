<?php
require_once 'autenticacao.php';require_once 'layout.php';require_once 'conexao.php';exigirLogin();
$busca=trim($_GET['busca']??'');$like='%'.$busca.'%';
$stmt=$conexao->prepare("SELECT s.id_sinalizacao,s.tipo,s.estado,s.observacao,e.identificacao,l.andar,l.descricao AS localizacao
 FROM sinalizacao s JOIN extintor e ON e.id_extintor=s.id_extintor JOIN local l ON l.id_local=e.id_local
 WHERE COALESCE(CAST(e.identificacao AS CHAR),'') LIKE ? OR l.descricao LIKE ? OR s.tipo LIKE ? OR s.estado LIKE ?
 ORDER BY l.andar,l.descricao,s.id_sinalizacao DESC");
$stmt->bind_param('ssss',$like,$like,$like,$like);$stmt->execute();$resultado=$stmt->get_result();
inicioPagina('Sinalizações'); ?>
<div class="titulo-com-acao"><div><h1>Sinalizações</h1><p class="subtitulo">Placas identificadas e suas observações, independentemente dos levantamentos.</p></div><?php if(podeAlterar()){?><a class="botao-link" href="cadastro_sinalizacao.php">Nova sinalização</a><?php } ?></div>
<?php mostrarAviso(); ?>
<form class="filtros" method="GET"><input type="search" name="busca" placeholder="Sala, equipamento, tipo ou estado" value="<?php echo escapar($busca); ?>"><button type="submit">Buscar</button></form>
<div class="tabela-responsiva"><table><tr><th>Extintor / local</th><th>Tipo</th><th>Estado</th><th>Observações</th><?php if(podeAlterar())echo '<th>Ações</th>'; ?></tr>
<?php while($s=$resultado->fetch_assoc()){?><tr>
<td><?php echo escapar(rotuloExtintor($s['identificacao'],$s['andar'],$s['localizacao'])); ?></td><td><?php echo escapar($s['tipo']); ?></td><td><?php echo escapar($s['estado']); ?></td><td><?php echo escapar($s['observacao']?:'—'); ?></td>
<?php if(podeAlterar()){?><td class="acoes"><a class="botao-link pequeno" href="cadastro_sinalizacao.php?id=<?php echo (int)$s['id_sinalizacao']; ?>">Editar</a><?php echo botaoExcluir('exclui_sinalizacao.php',(int)$s['id_sinalizacao']); ?></td><?php } ?></tr><?php } ?>
<?php if(!$resultado->num_rows){?><tr><td colspan="5">Nenhuma sinalização encontrada.</td></tr><?php } ?>
</table></div>
<?php $stmt->close();$conexao->close();fimPagina(); ?>
