<?php

require_once "autenticacao.php";
require_once "layout.php";
require_once "conexao.php";

exigirLogin();
$perfil = $_SESSION["usuario_perfil"] ?? "usuario";

function contar(mysqli $c, string $sql): int
{
    return (int) $c->query($sql)->fetch_row()[0];
}

$totais = [
    "Extintores" => contar($conexao, "SELECT COUNT(*) FROM extintor"),
    "Locais" => contar($conexao, "SELECT COUNT(*) FROM local"),
    "Dados a confirmar" => contar($conexao, "SELECT COUNT(*) FROM extintor WHERE identificacao IS NULL OR capacidade IS NULL OR data_validade_carga IS NULL OR id_fabricante IS NULL"),
    "Levantamentos" => contar($conexao, "SELECT COUNT(*) FROM levantamento"),
    "Sinalizações" => contar($conexao, "SELECT COUNT(*) FROM sinalizacao"),
];
$vencidos = contar($conexao, "SELECT COUNT(*) FROM extintor WHERE data_validade_carga < CURDATE()");
$vencendo = contar($conexao, "SELECT COUNT(*) FROM extintor WHERE data_validade_carga BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
$obstruidos = contar($conexao, "SELECT COUNT(*) FROM levantamento l
    WHERE l.acesso_desobstruido = 0 AND l.data_levantamento = (SELECT MAX(l2.data_levantamento) FROM levantamento l2 WHERE l2.id_extintor = l.id_extintor)");

$alertas = $conexao->query("SELECT e.identificacao, e.data_validade_carga, l.andar, l.descricao
    FROM extintor e INNER JOIN local l ON l.id_local = e.id_local
    WHERE e.data_validade_carga <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY e.data_validade_carga LIMIT 8");

inicioPagina("Painel");
?>

<section class="boas-vindas">
    <span class="etiqueta-perfil"><?php echo escapar(ucfirst($perfil)); ?></span>
    <h1>Olá, <?php echo escapar($_SESSION["usuario_nome"]); ?>!</h1>
    <p>Resumo atual dos extintores do Bloco B.</p>
</section>

<?php mostrarAviso(); ?>

<section class="grade-indicadores">
    <?php foreach ($totais as $rotulo => $valor) { ?>
        <div class="indicador"><strong><?php echo $valor; ?></strong><span><?php echo escapar($rotulo); ?></span></div>
    <?php } ?>
    <a class="indicador <?php echo $vencidos ? "alerta" : ""; ?>" href="lista_extintores.php?situacao=vencido"><strong><?php echo $vencidos; ?></strong><span>Carga vencida</span></a>
    <a class="indicador <?php echo $vencendo ? "atencao" : ""; ?>" href="lista_extintores.php?situacao=vencendo"><strong><?php echo $vencendo; ?></strong><span>Vencem em 30 dias</span></a>
    <div class="indicador <?php echo $obstruidos ? "alerta" : ""; ?>"><strong><?php echo $obstruidos; ?></strong><span>Acesso obstruído (último levantamento)</span></div>
</section>

<h2>Alertas de validade</h2>
<?php if ($alertas->num_rows === 0) { ?>
    <p class="mensagem aviso">Nenhuma data cadastrada está vencida ou vence em 30 dias. Equipamentos sem validade informada precisam de conferência.</p>
<?php } else { ?>
    <div class="tabela-responsiva">
        <table>
            <tr><th>Extintor</th><th>Local</th><th>Validade</th><th>Situação</th></tr>
            <?php while ($a = $alertas->fetch_assoc()) {
                $vencido = strtotime($a["data_validade_carga"]) < strtotime("today"); ?>
                <tr>
                    <td><?php echo escapar($a["identificacao"] !== null ? "Nº ".$a["identificacao"] : "Por localização"); ?></td>
                    <td><?php echo escapar($a["andar"] . "º — " . $a["descricao"]); ?></td>
                    <td><?php echo escapar(date("d/m/Y", strtotime($a["data_validade_carga"]))); ?></td>
                    <td><span class="etiqueta <?php echo $vencido ? "vermelha" : "amarela"; ?>"><?php echo $vencido ? "Vencido" : "Vence em breve"; ?></span></td>
                </tr>
            <?php } ?>
        </table>
    </div>
<?php } ?>

<h2>Acesso rápido</h2>
<section class="grade-painel">
    <a class="cartao-atalho" href="lista_extintores.php"><strong>Extintores</strong><span>Consulte os equipamentos cadastrados.</span></a>
    <a class="cartao-atalho" href="lista_levantamentos.php"><strong>Levantamentos</strong><span>Acompanhe as verificações.</span></a>
    <a class="cartao-atalho" href="lista_sinalizacoes.php"><strong>Sinalizações</strong><span>Condição das placas.</span></a>
    <a class="cartao-atalho" href="consome_api.php"><strong>API REST</strong><span>Consumo dos extintores em JSON.</span></a>

    <?php if (ehAdmin()) { ?>
        <a class="cartao-atalho" href="lista_tipos.php"><strong>Tipos de extintor</strong><span>Classes de fogo e descrições.</span></a>
        <a class="cartao-atalho" href="lista_fabricantes.php"><strong>Fabricantes</strong><span>Empresas fabricantes.</span></a>
        <a class="cartao-atalho" href="lista_locais.php"><strong>Locais</strong><span>Pontos do Bloco B.</span></a>
        <a class="cartao-atalho" href="lista_usuarios.php"><strong>Usuários e perfis</strong><span>Quem administra, consulta ou faz manutenção.</span></a>
    <?php } ?>

    <?php if (podeAlterar()) { ?>
        <a class="cartao-atalho destaque" href="cadastro_levantamento.php"><strong>Novo levantamento</strong><span>Registre uma inspeção.</span></a>
    <?php } ?>
</section>

<?php
$conexao->close();
fimPagina();
