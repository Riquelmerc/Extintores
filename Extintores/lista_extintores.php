<?php
require_once "autenticacao.php";
require_once "layout.php";
require_once "conexao.php";
exigirLogin();

$busca = trim($_GET['busca'] ?? '');
$idTipo = filter_input(INPUT_GET, 'tipo', FILTER_VALIDATE_INT) ?: 0;
$idLocal = filter_input(INPUT_GET, 'local', FILTER_VALIDATE_INT) ?: 0;
$situacao = $_GET['situacao'] ?? '';

$sql = "SELECT e.id_extintor, e.identificacao, e.capacidade, e.data_validade_carga,
               t.descricao AS tipo, t.classe_fogo, f.nome AS fabricante,
               l.andar, l.descricao AS localizacao
        FROM extintor e
        LEFT JOIN tipo_extintor t ON t.id_tipo=e.id_tipo
        LEFT JOIN fabricante f ON f.id_fabricante=e.id_fabricante
        INNER JOIN local l ON l.id_local=e.id_local
        WHERE (COALESCE(CAST(e.identificacao AS CHAR),'') LIKE ?
               OR COALESCE(f.nome,'') LIKE ? OR l.descricao LIKE ?)";
$like = "%" . $busca . "%";
$paramTipos = 'sss';
$params = [$like, $like, $like];
if ($idTipo) { $sql .= ' AND e.id_tipo=?'; $paramTipos .= 'i'; $params[] = $idTipo; }
if ($idLocal) { $sql .= ' AND e.id_local=?'; $paramTipos .= 'i'; $params[] = $idLocal; }
if ($situacao === 'vencido') { $sql .= ' AND e.data_validade_carga < CURDATE()'; }
if ($situacao === 'vencendo') { $sql .= ' AND e.data_validade_carga BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)'; }
if ($situacao === 'ok') { $sql .= ' AND e.data_validade_carga > DATE_ADD(CURDATE(), INTERVAL 30 DAY)'; }
if ($situacao === 'pendente') { $sql .= ' AND e.data_validade_carga IS NULL'; }
$sql .= ' ORDER BY l.andar, l.descricao, e.id_extintor';
$stmt = $conexao->prepare($sql);
$stmt->bind_param($paramTipos, ...$params);
$stmt->execute();
$resultado = $stmt->get_result();
$listaTipos = $conexao->query('SELECT id_tipo, descricao FROM tipo_extintor ORDER BY descricao');
$listaLocais = $conexao->query('SELECT id_local, andar, descricao FROM local ORDER BY andar, descricao');

function situacaoValidade(?string $data): array
{
    if (!$data) return ['Não informada', 'etiqueta amarela'];
    $hoje = new DateTime('today');
    $validade = new DateTime($data);
    if ($validade < $hoje) return ['Vencido', 'etiqueta vermelha'];
    if ($validade <= (clone $hoje)->modify('+30 days')) return ['Vence em breve', 'etiqueta amarela'];
    return ['Em dia', 'etiqueta verde'];
}

inicioPagina('Extintores');
?>
<div class="titulo-com-acao">
    <div><h1>Extintores</h1><p class="subtitulo">Equipamentos do Bloco B, identificados pela localização quando o número não foi confirmado.</p></div>
    <?php if (podeAlterar()) { ?><a class="botao-link" href="cadastro_extintor.php">Novo extintor</a><?php } ?>
</div>
<?php mostrarAviso(); ?>
<form class="filtros" method="GET">
    <input type="search" name="busca" placeholder="Sala, local, fabricante ou número" value="<?php echo escapar($busca); ?>">
    <select name="tipo" aria-label="Tipo">
        <option value="0">Todos os tipos</option>
        <?php while ($t = $listaTipos->fetch_assoc()) { ?>
            <option value="<?php echo (int)$t['id_tipo']; ?>" <?php echo $idTipo === (int)$t['id_tipo'] ? 'selected' : ''; ?>><?php echo escapar($t['descricao']); ?></option>
        <?php } ?>
    </select>
    <select name="local" aria-label="Local">
        <option value="0">Todos os locais</option>
        <?php while ($l = $listaLocais->fetch_assoc()) { ?>
            <option value="<?php echo (int)$l['id_local']; ?>" <?php echo $idLocal === (int)$l['id_local'] ? 'selected' : ''; ?>><?php echo escapar($l['andar'].'º — '.$l['descricao']); ?></option>
        <?php } ?>
    </select>
    <select name="situacao" aria-label="Situação da validade">
        <?php foreach ([''=>'Qualquer validade','pendente'=>'Sem validade informada','vencido'=>'Vencidos','vencendo'=>'Vencem em 30 dias','ok'=>'Em dia'] as $valor=>$rotulo) { ?>
            <option value="<?php echo escapar($valor); ?>" <?php echo $situacao === $valor ? 'selected' : ''; ?>><?php echo escapar($rotulo); ?></option>
        <?php } ?>
    </select>
    <button type="submit">Filtrar</button>
</form>
<div class="tabela-responsiva"><table>
    <tr><th>Referência / Ident.</th><th>Tipo</th><th>Capacidade</th><th>Fabricante</th><th>Localização</th><th>Validade</th><?php if (podeAlterar()) echo '<th>Ações</th>'; ?></tr>
    <?php while ($e = $resultado->fetch_assoc()) {
        [$texto, $classe] = situacaoValidade($e['data_validade_carga']);
        $nomeTipo = $e['tipo'] ?: 'A confirmar';
        $classeFogo = $e['classe_fogo'] ? ' ('.$e['classe_fogo'].')' : '';
        $capacidade = $e['capacidade'] !== null
            ? number_format((float)$e['capacidade'],2,',','.') . (preg_match('/água|agua/iu',$nomeTipo) ? ' L' : ' kg')
            : 'Não informada';
    ?>
    <tr>
        <td><?php echo $e['identificacao'] !== null ? 'Nº '.escapar($e['identificacao']) : '<strong>Por localização</strong>'; ?></td>
        <td><?php echo escapar($nomeTipo.$classeFogo); ?></td>
        <td><?php echo escapar($capacidade); ?></td>
        <td><?php echo escapar($e['fabricante'] ?: 'Não identificado'); ?></td>
        <td><?php echo escapar($e['andar'].'º andar — '.$e['localizacao']); ?></td>
        <td><?php echo $e['data_validade_carga'] ? escapar(date('d/m/Y', strtotime($e['data_validade_carga']))) : 'A confirmar'; ?><br><span class="<?php echo $classe; ?>"><?php echo escapar($texto); ?></span></td>
        <?php if (podeAlterar()) { ?><td class="acoes">
            <a class="botao-link pequeno" href="cadastro_extintor.php?id=<?php echo (int)$e['id_extintor']; ?>">Editar</a>
            <?php if (ehAdmin()) echo botaoExcluir('exclui_extintor.php',(int)$e['id_extintor']); ?>
        </td><?php } ?>
    </tr>
    <?php } ?>
    <?php if ($resultado->num_rows === 0) { ?><tr><td colspan="7">Nenhum extintor encontrado.</td></tr><?php } ?>
</table></div>
<?php $stmt->close(); $conexao->close(); fimPagina(); ?>
