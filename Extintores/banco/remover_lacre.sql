-- Use este script só se você JÁ importou o banco antes.
-- Ele remove a coluna do lacre sem apagar nenhum dado.
-- phpMyAdmin: selecione controle_extintores -> aba SQL -> cole e clique em Executar.
ALTER TABLE extintor DROP COLUMN numero_lacre;
