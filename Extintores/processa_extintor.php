<?php
require_once 'autenticacao.php';
exigirPerfis(['administrador','manutencao']);
exigirPost('lista_extintores.php');
require_once 'conexao.php';
$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
$destino='cadastro_extintor.php'.($id?'?id='.$id:'');
function campoOpcionalInteiro(string $nome): int|false|null {
    $v=trim((string)($_POST[$nome]??''));
    if ($v==='')return null;
    $n=filter_var($v,FILTER_VALIDATE_INT);
    return ($n===false || $n<1) ? false : $n;
}
$identificacao=campoOpcionalInteiro('identificacao');
$idTipo=campoOpcionalInteiro('id_tipo');
$idFabricante=campoOpcionalInteiro('id_fabricante');
$idLocal=filter_input(INPUT_POST,'id_local',FILTER_VALIDATE_INT);
$capTexto=trim((string)($_POST['capacidade']??''));
$capacidade=$capTexto===''?null:filter_var($capTexto,FILTER_VALIDATE_FLOAT);
$dataValidade=trim((string)($_POST['data_validade_carga']??''));
if ($dataValidade==='')$dataValidade=null;
$dataValida=true;
if ($dataValidade!==null){$d=DateTime::createFromFormat('!Y-m-d',$dataValidade);$dataValida=$d && $d->format('Y-m-d')===$dataValidade;}
if ($identificacao===false || $idTipo===false || $idFabricante===false || !$idLocal || $idLocal<1 || $capacidade===false || ($capacidade!==null && $capacidade<=0) || !$dataValida) {
    avisar('aviso','Informe um local válido; preencha os demais campos apenas quando os dados forem confirmados.');
    header('Location: '.$destino);exit;
}
if ($id) {
    $stmt=$conexao->prepare('UPDATE extintor SET identificacao=?,capacidade=?,data_validade_carga=?,id_tipo=?,id_fabricante=?,id_local=? WHERE id_extintor=?');
    $stmt->bind_param('idsiiii',$identificacao,$capacidade,$dataValidade,$idTipo,$idFabricante,$idLocal,$id);
} else {
    $stmt=$conexao->prepare('INSERT INTO extintor (identificacao,capacidade,data_validade_carga,id_tipo,id_fabricante,id_local) VALUES (?,?,?,?,?,?)');
    $stmt->bind_param('idsiii',$identificacao,$capacidade,$dataValidade,$idTipo,$idFabricante,$idLocal);
}
$erro=executarSeguro($stmt);$stmt->close();$conexao->close();
if ($erro){avisar('aviso',$erro==='Já existe um registro com esses dados (valor duplicado).'?'Número de identificação já cadastrado.':$erro);header('Location: '.$destino);exit;}
avisar('sucesso',$id?'Extintor atualizado com sucesso.':'Extintor cadastrado com sucesso.');
header('Location: lista_extintores.php');exit;
