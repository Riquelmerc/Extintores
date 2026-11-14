-- Projeto Controle de Extintores - Bloco B / UNESC
-- Estrutura preservada do projeto enviado, com campos opcionais para dados nao confirmados.
-- NAO remove tabelas nem registros existentes.
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  perfil ENUM('administrador','usuario','manutencao') NOT NULL DEFAULT 'usuario'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tipo_extintor (
  id_tipo INT AUTO_INCREMENT PRIMARY KEY,
  descricao VARCHAR(100) NOT NULL,
  classe_fogo VARCHAR(20) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fabricante (
  id_fabricante INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS local (
  id_local INT AUTO_INCREMENT PRIMARY KEY,
  andar INT NOT NULL,
  descricao VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS extintor (
  id_extintor INT AUTO_INCREMENT PRIMARY KEY,
  identificacao INT NULL UNIQUE,
  capacidade DECIMAL(6,2) NULL,
  data_validade_carga DATE NULL,
  id_tipo INT NULL,
  id_fabricante INT NULL,
  id_local INT NOT NULL,
  CONSTRAINT fk_extintor_tipo FOREIGN KEY (id_tipo) REFERENCES tipo_extintor(id_tipo) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_extintor_fabricante FOREIGN KEY (id_fabricante) REFERENCES fabricante(id_fabricante) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_extintor_local FOREIGN KEY (id_local) REFERENCES local(id_local) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS levantamento (
  id_levantamento INT AUTO_INCREMENT PRIMARY KEY,
  id_extintor INT NOT NULL,
  data_levantamento DATE NOT NULL,
  acesso_desobstruido TINYINT(1) NULL,
  estado_extintor VARCHAR(100) NOT NULL,
  responsavel VARCHAR(120) NULL,
  observacao TEXT NULL,
  CONSTRAINT fk_levantamento_extintor FOREIGN KEY (id_extintor) REFERENCES extintor(id_extintor) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Conforme o DER do Notion, sinalizacao e 1:N em relacao ao extintor.
CREATE TABLE IF NOT EXISTS sinalizacao (
  id_sinalizacao INT AUTO_INCREMENT PRIMARY KEY,
  tipo VARCHAR(80) NOT NULL,
  estado VARCHAR(50) NOT NULL,
  observacao TEXT NULL,
  id_extintor INT NOT NULL,
  INDEX idx_sinalizacao_extintor (id_extintor),
  CONSTRAINT fk_sinalizacao_extintor FOREIGN KEY (id_extintor) REFERENCES extintor(id_extintor) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS controle_config (
  chave VARCHAR(100) PRIMARY KEY,
  valor VARCHAR(100) NOT NULL
) ENGINE=InnoDB;
