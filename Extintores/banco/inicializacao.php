<?php
/**
 * Instala os cadastros iniciais no localhost (XAMPP) uma unica vez.
 * Atualiza a estrutura existente sem remover dados do usuario.
 */
function executarArquivoSql(mysqli $db, string $arquivo): void
{
    $sql = file_get_contents($arquivo);
    if ($sql === false || trim($sql) === "") {
        throw new RuntimeException("Não foi possível carregar o arquivo de instalação.");
    }
    $db->multi_query($sql);
    do {
        $resultado = $db->store_result();
        if ($resultado instanceof mysqli_result) {
            $resultado->free();
        }
        if (!$db->more_results()) {
            break;
        }
    } while ($db->next_result());
}

function campoNaoPermiteNulo(mysqli $db, string $tabela, string $campo): bool
{
    $tabelas = ['extintor', 'tipo_extintor', 'levantamento'];
    $colunas = [
        'extintor' => ['identificacao', 'capacidade', 'data_validade_carga', 'id_tipo', 'id_fabricante'],
        'tipo_extintor' => ['classe_fogo'],
        'levantamento' => ['acesso_desobstruido']
    ];
    if (!in_array($tabela, $tabelas, true) || !in_array($campo, $colunas[$tabela], true)) {
        throw new InvalidArgumentException('Campo não autorizado para migração.');
    }
    $resultado = $db->query("SHOW COLUMNS FROM `$tabela` LIKE '$campo'");
    $coluna = $resultado->fetch_assoc();
    return !$coluna || $coluna['Null'] === 'NO';
}

function inicializarBancoExtintores(mysqli $db): void
{
    // A consulta rapida evita consultas DDL em cada navegacao pelo site.
    $existeConfig = $db->query("SHOW TABLES LIKE 'controle_config'")->num_rows > 0;
    if ($existeConfig) {
        $versao = $db->query("SELECT valor FROM controle_config WHERE chave='cadastros_notion_v1'")->fetch_assoc();
        if ($versao) return; // Nao recadastra registros excluidos nem regrava edicoes.
    }
    executarArquivoSql($db, __DIR__ . '/estrutura.sql');

    $alteracoes = [
        ['extintor', 'identificacao', 'INT NULL'],
        ['extintor', 'capacidade', 'DECIMAL(6,2) NULL'],
        ['extintor', 'data_validade_carga', 'DATE NULL'],
        ['extintor', 'id_tipo', 'INT NULL'],
        ['extintor', 'id_fabricante', 'INT NULL'],
        ['tipo_extintor', 'classe_fogo', 'VARCHAR(20) NULL'],
        ['levantamento', 'acesso_desobstruido', 'TINYINT(1) NULL'],
    ];
    foreach ($alteracoes as [$tabela, $campo, $definicao]) {
        if (campoNaoPermiteNulo($db, $tabela, $campo)) {
            $db->query("ALTER TABLE `$tabela` MODIFY COLUMN `$campo` $definicao");
        }
    }

    // A documentacao do Notion informa responsaveis nos levantamentos por andar.
    $responsavel = $db->query("SHOW COLUMNS FROM levantamento LIKE 'responsavel'")->fetch_assoc();
    if (!$responsavel) {
        $db->query("ALTER TABLE levantamento ADD COLUMN responsavel VARCHAR(120) NULL AFTER estado_extintor");
    }
    executarArquivoSql($db, __DIR__ . '/dados_iniciais_notion.sql');
}
