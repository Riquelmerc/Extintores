-- Dados iniciais do projeto, conciliados com as evidencias e os registros do Notion.
-- Este arquivo NAO inventa numero patrimonial, carga, validade ou fabricante.
-- Para utilizar em banco existente, deixe o sistema executar a migracao antes.

-- Login inicial. Senha: Admin@1234 (ALTERAR APOS O PRIMEIRO LOGIN).
INSERT INTO usuarios (nome,email,senha,perfil)
SELECT 'Administrador','admin@bloco-b.local','$2y$12$HiBPgczLYAhlHIjd/VVC9.MtXGfQCsWDH5S6O3Iak3v9Yxg5bLRjK','administrador'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email='admin@bloco-b.local');

INSERT INTO tipo_extintor (descricao,classe_fogo)
SELECT 'Água pressurizada','A' WHERE NOT EXISTS (SELECT 1 FROM tipo_extintor WHERE descricao='Água pressurizada');
INSERT INTO tipo_extintor (descricao,classe_fogo)
SELECT 'Pó químico',NULL WHERE NOT EXISTS (SELECT 1 FROM tipo_extintor WHERE descricao='Pó químico');
INSERT INTO tipo_extintor (descricao,classe_fogo)
SELECT 'Gás carbônico (CO2)','B, C' WHERE NOT EXISTS (SELECT 1 FROM tipo_extintor WHERE descricao='Gás carbônico (CO2)');

-- QHS e prestadora de manutencao nas etiquetas; NAO foi incluida como fabricante.
-- Cada ponto foi informado separadamente. Os numeros 206/207/210/211 sao de SALA.
INSERT INTO local (andar,descricao)
SELECT 1, 'Corredor do Bloco B - próximo à sala 206' WHERE NOT EXISTS (SELECT 1 FROM local WHERE andar=1 AND descricao='Corredor do Bloco B - próximo à sala 206');
INSERT INTO local (andar,descricao)
SELECT 1, 'Corredor do Bloco B - próximo à sala 210' WHERE NOT EXISTS (SELECT 1 FROM local WHERE andar=1 AND descricao='Corredor do Bloco B - próximo à sala 210');
INSERT INTO local (andar,descricao)
SELECT 2, 'Corredor do Bloco B - próximo à sala 206' WHERE NOT EXISTS (SELECT 1 FROM local WHERE andar=2 AND descricao='Corredor do Bloco B - próximo à sala 206');
INSERT INTO local (andar,descricao)
SELECT 2, 'Corredor do Bloco B - próximo à sala 207' WHERE NOT EXISTS (SELECT 1 FROM local WHERE andar=2 AND descricao='Corredor do Bloco B - próximo à sala 207');
INSERT INTO local (andar,descricao)
SELECT 2, 'Corredor do Bloco B - próximo à sala 210' WHERE NOT EXISTS (SELECT 1 FROM local WHERE andar=2 AND descricao='Corredor do Bloco B - próximo à sala 210');
INSERT INTO local (andar,descricao)
SELECT 2, 'Corredor do Bloco B - próximo à sala 211' WHERE NOT EXISTS (SELECT 1 FROM local WHERE andar=2 AND descricao='Corredor do Bloco B - próximo à sala 211');
INSERT INTO local (andar,descricao)
SELECT 1, 'Corredor do Bloco B - extintor de CO₂ (ponto exato a confirmar)' WHERE NOT EXISTS (SELECT 1 FROM local WHERE andar=1 AND descricao='Corredor do Bloco B - extintor de CO₂ (ponto exato a confirmar)');

-- O registro sem numero usa o proprio local como referencia exibida na interface.
INSERT INTO extintor (identificacao,capacidade,data_validade_carga,id_tipo,id_fabricante,id_local)
SELECT NULL,NULL,NULL,(SELECT id_tipo FROM tipo_extintor WHERE descricao='Água pressurizada' ORDER BY id_tipo LIMIT 1),NULL,l.id_local
FROM local l WHERE l.andar=1 AND l.descricao='Corredor do Bloco B - próximo à sala 206'
AND NOT EXISTS (SELECT 1 FROM extintor e WHERE e.id_local=l.id_local);
INSERT INTO extintor (identificacao,capacidade,data_validade_carga,id_tipo,id_fabricante,id_local)
SELECT NULL,NULL,NULL,(SELECT id_tipo FROM tipo_extintor WHERE descricao='Pó químico' ORDER BY id_tipo LIMIT 1),NULL,l.id_local
FROM local l WHERE l.andar=1 AND l.descricao='Corredor do Bloco B - próximo à sala 210'
AND NOT EXISTS (SELECT 1 FROM extintor e WHERE e.id_local=l.id_local);
INSERT INTO extintor (identificacao,capacidade,data_validade_carga,id_tipo,id_fabricante,id_local)
SELECT NULL,NULL,NULL,(SELECT id_tipo FROM tipo_extintor WHERE descricao='Pó químico' ORDER BY id_tipo LIMIT 1),NULL,l.id_local
FROM local l WHERE l.andar=2 AND l.descricao='Corredor do Bloco B - próximo à sala 206'
AND NOT EXISTS (SELECT 1 FROM extintor e WHERE e.id_local=l.id_local);
INSERT INTO extintor (identificacao,capacidade,data_validade_carga,id_tipo,id_fabricante,id_local)
SELECT NULL,NULL,NULL,(SELECT id_tipo FROM tipo_extintor WHERE descricao='Pó químico' ORDER BY id_tipo LIMIT 1),NULL,l.id_local
FROM local l WHERE l.andar=2 AND l.descricao='Corredor do Bloco B - próximo à sala 207'
AND NOT EXISTS (SELECT 1 FROM extintor e WHERE e.id_local=l.id_local);
INSERT INTO extintor (identificacao,capacidade,data_validade_carga,id_tipo,id_fabricante,id_local)
SELECT NULL,NULL,NULL,(SELECT id_tipo FROM tipo_extintor WHERE descricao='Água pressurizada' ORDER BY id_tipo LIMIT 1),NULL,l.id_local
FROM local l WHERE l.andar=2 AND l.descricao='Corredor do Bloco B - próximo à sala 210'
AND NOT EXISTS (SELECT 1 FROM extintor e WHERE e.id_local=l.id_local);
INSERT INTO extintor (identificacao,capacidade,data_validade_carga,id_tipo,id_fabricante,id_local)
SELECT NULL,NULL,NULL,NULL,NULL,l.id_local
FROM local l WHERE l.andar=2 AND l.descricao='Corredor do Bloco B - próximo à sala 211'
AND NOT EXISTS (SELECT 1 FROM extintor e WHERE e.id_local=l.id_local);
INSERT INTO extintor (identificacao,capacidade,data_validade_carga,id_tipo,id_fabricante,id_local)
SELECT NULL,NULL,NULL,(SELECT id_tipo FROM tipo_extintor WHERE descricao='Gás carbônico (CO2)' ORDER BY id_tipo LIMIT 1),NULL,l.id_local
FROM local l WHERE l.andar=1 AND l.descricao='Corredor do Bloco B - extintor de CO₂ (ponto exato a confirmar)'
AND NOT EXISTS (SELECT 1 FROM extintor e WHERE e.id_local=l.id_local);

-- Levantamentos datados conforme os registros apresentados (revisar a data individual, se necessario).
INSERT INTO levantamento (id_extintor,data_levantamento,acesso_desobstruido,estado_extintor,responsavel,observacao)
SELECT e.id_extintor,'2026-10-21',NULL,'Não avaliado','Riquelme','Foto do ponto próximo à sala 206, primeiro andar. Placa de extintor de água identificada; número, lacre, fabricante e validade ainda não conferidos.'
FROM extintor e JOIN local l ON l.id_local=e.id_local
WHERE l.andar=1 AND l.descricao='Corredor do Bloco B - próximo à sala 206'
AND NOT EXISTS (SELECT 1 FROM levantamento v WHERE v.id_extintor=e.id_extintor AND v.observacao='Foto do ponto próximo à sala 206, primeiro andar. Placa de extintor de água identificada; número, lacre, fabricante e validade ainda não conferidos.');
INSERT INTO levantamento (id_extintor,data_levantamento,acesso_desobstruido,estado_extintor,responsavel,observacao)
SELECT e.id_extintor,'2026-10-21',NULL,'Não avaliado','Riquelme','Foto do ponto próximo à sala 210, primeiro andar. Placa indicativa de extintor de pó químico; demais dados pendentes de confirmação.'
FROM extintor e JOIN local l ON l.id_local=e.id_local
WHERE l.andar=1 AND l.descricao='Corredor do Bloco B - próximo à sala 210'
AND NOT EXISTS (SELECT 1 FROM levantamento v WHERE v.id_extintor=e.id_extintor AND v.observacao='Foto do ponto próximo à sala 210, primeiro andar. Placa indicativa de extintor de pó químico; demais dados pendentes de confirmação.');
INSERT INTO levantamento (id_extintor,data_levantamento,acesso_desobstruido,estado_extintor,responsavel,observacao)
SELECT e.id_extintor,'2026-10-22',NULL,'Não avaliado','Andrey','Foto do ponto próximo à sala 206, segundo andar. Placa indicativa de extintor de pó químico; demais dados pendentes de confirmação.'
FROM extintor e JOIN local l ON l.id_local=e.id_local
WHERE l.andar=2 AND l.descricao='Corredor do Bloco B - próximo à sala 206'
AND NOT EXISTS (SELECT 1 FROM levantamento v WHERE v.id_extintor=e.id_extintor AND v.observacao='Foto do ponto próximo à sala 206, segundo andar. Placa indicativa de extintor de pó químico; demais dados pendentes de confirmação.');
INSERT INTO levantamento (id_extintor,data_levantamento,acesso_desobstruido,estado_extintor,responsavel,observacao)
SELECT e.id_extintor,'2026-10-22',NULL,'Não avaliado','Andrey','Foto do ponto próximo à sala 207, segundo andar. Placa indicativa de extintor de pó químico; demais dados pendentes de confirmação.'
FROM extintor e JOIN local l ON l.id_local=e.id_local
WHERE l.andar=2 AND l.descricao='Corredor do Bloco B - próximo à sala 207'
AND NOT EXISTS (SELECT 1 FROM levantamento v WHERE v.id_extintor=e.id_extintor AND v.observacao='Foto do ponto próximo à sala 207, segundo andar. Placa indicativa de extintor de pó químico; demais dados pendentes de confirmação.');
INSERT INTO levantamento (id_extintor,data_levantamento,acesso_desobstruido,estado_extintor,responsavel,observacao)
SELECT e.id_extintor,'2026-10-22',NULL,'Não avaliado','Andrey','Foto do ponto próximo à sala 210, segundo andar. Placa indicativa de extintor de água; capacidade e validade não associadas com segurança a este ponto.'
FROM extintor e JOIN local l ON l.id_local=e.id_local
WHERE l.andar=2 AND l.descricao='Corredor do Bloco B - próximo à sala 210'
AND NOT EXISTS (SELECT 1 FROM levantamento v WHERE v.id_extintor=e.id_extintor AND v.observacao='Foto do ponto próximo à sala 210, segundo andar. Placa indicativa de extintor de água; capacidade e validade não associadas com segurança a este ponto.');
INSERT INTO levantamento (id_extintor,data_levantamento,acesso_desobstruido,estado_extintor,responsavel,observacao)
SELECT e.id_extintor,'2026-10-22',NULL,'Não avaliado','Andrey','Registro fotográfico do ponto próximo à sala 211, segundo andar. Tipo e demais dados do equipamento precisam ser confirmados no local.'
FROM extintor e JOIN local l ON l.id_local=e.id_local
WHERE l.andar=2 AND l.descricao='Corredor do Bloco B - próximo à sala 211'
AND NOT EXISTS (SELECT 1 FROM levantamento v WHERE v.id_extintor=e.id_extintor AND v.observacao='Registro fotográfico do ponto próximo à sala 211, segundo andar. Tipo e demais dados do equipamento precisam ser confirmados no local.');
INSERT INTO levantamento (id_extintor,data_levantamento,acesso_desobstruido,estado_extintor,responsavel,observacao)
SELECT e.id_extintor,'2026-10-21',NULL,'Não avaliado','Riquelme','Registro do corredor do primeiro andar com placa indicando CO₂ e alarme de incêndio. Confirmar se corresponde a outro ponto cadastrado antes de consolidar o inventário.'
FROM extintor e JOIN local l ON l.id_local=e.id_local
WHERE l.andar=1 AND l.descricao='Corredor do Bloco B - extintor de CO₂ (ponto exato a confirmar)'
AND NOT EXISTS (SELECT 1 FROM levantamento v WHERE v.id_extintor=e.id_extintor AND v.observacao='Registro do corredor do primeiro andar com placa indicando CO₂ e alarme de incêndio. Confirmar se corresponde a outro ponto cadastrado antes de consolidar o inventário.');

-- Apenas placas identificadas nas fotos sao vinculadas; estado fisico nao avaliado.
INSERT INTO sinalizacao (tipo,estado,observacao,id_extintor)
SELECT 'Placa indicativa','Não avaliado','Placa de sinalização indicando extintor de Água no ponto fotografado.',e.id_extintor
FROM extintor e JOIN local l ON l.id_local=e.id_local
WHERE l.andar=1 AND l.descricao='Corredor do Bloco B - próximo à sala 206'
AND NOT EXISTS (SELECT 1 FROM sinalizacao s WHERE s.id_extintor=e.id_extintor AND s.observacao='Placa de sinalização indicando extintor de Água no ponto fotografado.');
INSERT INTO sinalizacao (tipo,estado,observacao,id_extintor)
SELECT 'Placa indicativa','Não avaliado','Placa de sinalização indicando extintor de Pó químico no ponto fotografado.',e.id_extintor
FROM extintor e JOIN local l ON l.id_local=e.id_local
WHERE l.andar=1 AND l.descricao='Corredor do Bloco B - próximo à sala 210'
AND NOT EXISTS (SELECT 1 FROM sinalizacao s WHERE s.id_extintor=e.id_extintor AND s.observacao='Placa de sinalização indicando extintor de Pó químico no ponto fotografado.');
INSERT INTO sinalizacao (tipo,estado,observacao,id_extintor)
SELECT 'Placa indicativa','Não avaliado','Placa de sinalização indicando extintor de Pó químico no ponto fotografado.',e.id_extintor
FROM extintor e JOIN local l ON l.id_local=e.id_local
WHERE l.andar=2 AND l.descricao='Corredor do Bloco B - próximo à sala 206'
AND NOT EXISTS (SELECT 1 FROM sinalizacao s WHERE s.id_extintor=e.id_extintor AND s.observacao='Placa de sinalização indicando extintor de Pó químico no ponto fotografado.');
INSERT INTO sinalizacao (tipo,estado,observacao,id_extintor)
SELECT 'Placa indicativa','Não avaliado','Placa de sinalização indicando extintor de Pó químico no ponto fotografado.',e.id_extintor
FROM extintor e JOIN local l ON l.id_local=e.id_local
WHERE l.andar=2 AND l.descricao='Corredor do Bloco B - próximo à sala 207'
AND NOT EXISTS (SELECT 1 FROM sinalizacao s WHERE s.id_extintor=e.id_extintor AND s.observacao='Placa de sinalização indicando extintor de Pó químico no ponto fotografado.');
INSERT INTO sinalizacao (tipo,estado,observacao,id_extintor)
SELECT 'Placa indicativa','Não avaliado','Placa de sinalização indicando extintor de Água no ponto fotografado.',e.id_extintor
FROM extintor e JOIN local l ON l.id_local=e.id_local
WHERE l.andar=2 AND l.descricao='Corredor do Bloco B - próximo à sala 210'
AND NOT EXISTS (SELECT 1 FROM sinalizacao s WHERE s.id_extintor=e.id_extintor AND s.observacao='Placa de sinalização indicando extintor de Água no ponto fotografado.');
INSERT INTO sinalizacao (tipo,estado,observacao,id_extintor)
SELECT 'Placa indicativa','Não avaliado','Placa de sinalização indicando extintor de CO₂ no ponto fotografado.',e.id_extintor
FROM extintor e JOIN local l ON l.id_local=e.id_local
WHERE l.andar=1 AND l.descricao='Corredor do Bloco B - extintor de CO₂ (ponto exato a confirmar)'
AND NOT EXISTS (SELECT 1 FROM sinalizacao s WHERE s.id_extintor=e.id_extintor AND s.observacao='Placa de sinalização indicando extintor de CO₂ no ponto fotografado.');

-- Marcador evita inserir novamente registros que o usuario venha a excluir.
INSERT INTO controle_config (chave, valor) VALUES ('cadastros_notion_v1','concluido')
ON DUPLICATE KEY UPDATE valor=VALUES(valor);
