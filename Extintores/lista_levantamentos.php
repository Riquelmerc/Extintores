<?php
require_once 'autenticacao.php';require_once 'layout.php';require_once 'conexao.php';exigirLogin();
$busca=trim($_GET['busca']??'');$de=$_GET['de']??'';$ate=$_GET['ate']??'';$like='%'.$busca.'%';
$sql="SELECT v.id_levantamento,v.data_levantamento,v.acesso_desobstruido,v.estado_extintor,v.responsavel,v.observacao,
 e.identificacao, l.andar,l.descricao AS localizacao,
 (SELECT COUNT(*) FROM sinalizacao s WHERE s.id_extintor=e.id_extintor) AS qtd_sinalizacoes
 FROM levantamento v JOIN extintor e ON e.id_extintor=v.id_extintor JOIN local l ON l.id_local=e.id_local
 WHERE (COALESCE(CAST(e.identificacao AS CHAR),'') LIKE ? OR l.descricao LIKE ? OR v.estado_extintor LIKE ? OR COALESCE(v.observacao,'') LIKE ?)";
$tipos='ssss';$params=[$like,$like,$like,$like];
if ($de!==''){$sql.=' AND v.data_levantamento>=?';$tipos.='s';$params[]=$de;}
if ($ate!==''){$sql.=' AND v.data_levantamento<=?';$tipos.='s';$params[]=$ate;}
$sql.=' ORDER BY v.data_levantamento DESC,v.id_levantamento DESC';
$stmt=$conexao->prepare($sql);$stmt->bind_param($tipos,...$params);$stmt->execute();$resultado=$stmt->get_result();
inicioPagina('Levantamentos'); ?>
<div class="titulo-com-acao"><div><h1>Levantamentos</h1><p class="subtitulo">Histórico de observações do Bloco B.</p></div>
<?php if(podeAlterar()){?><div class="grupo-acoes"><a class="botao-link" href="cadastro_levantamento.php">Novo levantamento</a><a class="botao-link secundario" href="cadastro_sinalizacao.php">Nova sinalização</a></div><?php } ?></div>
<?php mostrarAviso(); ?>
<form class="filtros" method="GET"><input type="search" name="busca" placeholder="Sala, estado, observação ou nº" value="<?php echo escapar($busca); ?>"><input type="date" name="de" aria-label="Data inicial" value="<?php echo escapar($de); ?>"><input type="date" name="ate" aria-label="Data final" value="<?php echo escapar($ate); ?>"><button type="submit">Filtrar</button></form>
<div class="tabela-responsiva"><table>
<tr><th>Data</th><th>Extintor / local</th><th>Acesso</th><th>Estado</th><th>Responsável</th><th>Observação</th><th>Sinalização</th><?php if(podeAlterar())echo '<th>Ações</th>'; ?></tr>
<?php while($v=$resultado->fetch_assoc()){ ?>
<tr><td><?php echo escapar(date('d/m/Y',strtotime($v['data_levantamento']))); ?></td><td><?php echo escapar(rotuloExtintor($v['identificacao'],$v['andar'],$v['localizacao'])); ?></td>
<td><?php echo $v['acesso_desobstruido']===null?'Não verificado':($v['acesso_desobstruido']?'Desobstruído':'Obstruído'); ?></td>
<td><?php echo escapar($v['estado_extintor']); ?></td><td><?php echo escapar($v['responsavel']?:'A confirmar'); ?></td><td><?php echo escapar($v['observacao']?:'—'); ?></td>
<td><?php echo (int)$v['qtd_sinalizacoes']; ?> registro(s)</td>
<?php if(podeAlterar()){?><td class="acoes"><a class="botao-link pequeno" href="cadastro_levantamento.php?id=<?php echo (int)$v['id_levantamento']; ?>">Editar</a><?php echo botaoExcluir('exclui_levantamento.php',(int)$v['id_levantamento']); ?></td><?php } ?></tr>
<?php } ?>
<?php if(!$resultado->num_rows){?><tr><td colspan="8">Nenhum levantamento encontrado.</td></tr><?php } ?>
</table></div>
<?php $stmt->close();$conexao->close();fimPagina(); ?>
