<?php
header('Content-Type: application/json; charset=UTF-8');
header('Allow: GET, POST, PUT, DELETE, OPTIONS');
function responder(int $status, array $dados): never {
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
set_exception_handler(function(Throwable $e): void {
    $codigo=$e instanceof mysqli_sql_exception?$e->getCode():0;
    $status=in_array($codigo,[1062,1451,1452],true)?409:500;
    $mensagem=match($codigo){
        1062=>'Já existe um extintor com esta identificação.',
        1452=>'Tipo, fabricante ou local informado não existe.',
        1451=>'Este registro é utilizado por outros dados.',
        default=>'Erro interno ao processar a requisição.'
    };
    responder($status,['erro'=>$mensagem]);
});
require_once 'autenticacao.php';
iniciarSessao();
if (!isset($_SESSION['usuario_id'])) responder(401,['erro'=>'Faça login para utilizar a API.']);
if (($_SERVER['REQUEST_METHOD']??'')==='OPTIONS') responder(200,['mensagem'=>'OK']);

function exigirPermissaoDeAlteracao(): void {
    if (!in_array($_SESSION['usuario_perfil']??'',['administrador','manutencao'],true)) responder(403,['erro'=>'Perfil sem permissão para alterar extintores.']);
}
function idDaRequisicao(): int {
    $id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
    if ($id===false || $id===null || $id<1) responder(400,['erro'=>'Informe um id válido na URL.']);
    return $id;
}
function dadosDoCorpo(): array {
    $dados=json_decode(file_get_contents('php://input'),true);
    if (!is_array($dados)) responder(400,['erro'=>'Envie um JSON válido no corpo da requisição.']);
    $local=filter_var($dados['id_local']??null,FILTER_VALIDATE_INT);
    if ($local===false || $local===null || $local<1) responder(422,['erro'=>'id_local é obrigatório.']);
    $converterInteiro=function(string $chave) use($dados): ?int {
        $v=$dados[$chave]??null;
        if ($v===null || $v==='')return null;
        $n=filter_var($v,FILTER_VALIDATE_INT);
        if ($n===false || $n<1)responder(422,['erro'=>"Valor inválido para $chave."]);
        return $n;
    };
    $capacidade=$dados['capacidade']??null;
    if ($capacidade==='')$capacidade=null;
    if ($capacidade!==null){
        $capacidade=filter_var($capacidade,FILTER_VALIDATE_FLOAT);
        if ($capacidade===false || $capacidade<=0)responder(422,['erro'=>'Capacidade inválida.']);
    }
    $data=$dados['data_validade_carga']??null;
    if ($data==='')$data=null;
    if ($data!==null){
        $obj=DateTime::createFromFormat('!Y-m-d',(string)$data);
        if (!$obj || $obj->format('Y-m-d')!==$data)responder(422,['erro'=>'A data deve usar YYYY-MM-DD.']);
    }
    return [
        'identificacao'=>$converterInteiro('identificacao'),
        'capacidade'=>$capacidade,
        'data_validade_carga'=>$data,
        'id_tipo'=>$converterInteiro('id_tipo'),
        'id_fabricante'=>$converterInteiro('id_fabricante'),
        'id_local'=>$local
    ];
}
function buscarExtintor(mysqli $conexao, ?int $id=null): array|null {
    $sql="SELECT e.id_extintor,e.identificacao,e.capacidade,e.data_validade_carga,e.id_tipo,e.id_fabricante,e.id_local,
    t.descricao AS tipo,t.classe_fogo,f.nome AS fabricante,l.andar,l.descricao AS localizacao
    FROM extintor e LEFT JOIN tipo_extintor t ON t.id_tipo=e.id_tipo
    LEFT JOIN fabricante f ON f.id_fabricante=e.id_fabricante
    JOIN local l ON l.id_local=e.id_local";
    if ($id!==null){
        $stmt=$conexao->prepare($sql.' WHERE e.id_extintor=?');$stmt->bind_param('i',$id);$stmt->execute();
        $dado=$stmt->get_result()->fetch_assoc()?:null;$stmt->close();return $dado;
    }
    return $conexao->query($sql.' ORDER BY l.andar,l.descricao,e.id_extintor')->fetch_all(MYSQLI_ASSOC);
}
require_once 'conexao.php';
$metodo=$_SERVER['REQUEST_METHOD'];
if ($metodo==='GET'){
    if (isset($_GET['id'])){
        $resultado=buscarExtintor($conexao,idDaRequisicao());
        if ($resultado===null)responder(404,['erro'=>'Extintor não encontrado.']);
        responder(200,$resultado);
    }
    responder(200,buscarExtintor($conexao));
}
if ($metodo==='POST'){
    exigirPermissaoDeAlteracao();$d=dadosDoCorpo();
    $stmt=$conexao->prepare('INSERT INTO extintor (identificacao,capacidade,data_validade_carga,id_tipo,id_fabricante,id_local) VALUES (?,?,?,?,?,?)');
    $stmt->bind_param('idsiii',$d['identificacao'],$d['capacidade'],$d['data_validade_carga'],$d['id_tipo'],$d['id_fabricante'],$d['id_local']);
    $stmt->execute();$criado=$conexao->insert_id;$stmt->close();
    responder(201,['mensagem'=>'Extintor cadastrado.','id_extintor'=>$criado]);
}
if ($metodo==='PUT'){
    exigirPermissaoDeAlteracao();$id=idDaRequisicao();
    if (buscarExtintor($conexao,$id)===null)responder(404,['erro'=>'Extintor não encontrado.']);
    $d=dadosDoCorpo();
    $stmt=$conexao->prepare('UPDATE extintor SET identificacao=?,capacidade=?,data_validade_carga=?,id_tipo=?,id_fabricante=?,id_local=? WHERE id_extintor=?');
    $stmt->bind_param('idsiiii',$d['identificacao'],$d['capacidade'],$d['data_validade_carga'],$d['id_tipo'],$d['id_fabricante'],$d['id_local'],$id);
    $stmt->execute();$stmt->close();responder(200,['mensagem'=>'Extintor atualizado.']);
}
if ($metodo==='DELETE'){
    exigirPermissaoDeAlteracao();$id=idDaRequisicao();
    $stmt=$conexao->prepare('DELETE FROM extintor WHERE id_extintor=?');$stmt->bind_param('i',$id);$stmt->execute();$n=$stmt->affected_rows;$stmt->close();
    if (!$n) responder(404,['erro'=>'Extintor não encontrado.']);
    responder(200,['mensagem'=>'Extintor excluído.']);
}
responder(405,['erro'=>'Método HTTP não permitido.']);
