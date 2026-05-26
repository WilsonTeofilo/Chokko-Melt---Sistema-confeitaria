/* =========================================================
   - Cria banco + tabelas + FKs
 
   Login inicial (usuario id=1):
   - E-mail: admin@chokkomelt.local
   - Senha:  admin123

   Hash gerado com: password_hash('admin123', PASSWORD_BCRYPT)
   Troque a senha depois do primeiro acesso.
   ========================================================= */

SET NAMES utf8mb4;
SET time_zone = '-03:00';

DROP DATABASE IF EXISTS `chokko_melt`;
CREATE DATABASE `chokko_melt` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `chokko_melt`;

SET FOREIGN_KEY_CHECKS = 0;

/* =========================
   status_pedido    ========================= */
CREATE TABLE `status_pedido` (
  `id_status_pedido` INT NOT NULL AUTO_INCREMENT,
  `descricao` ENUM(
    'PENDENTE',
    'ACEITO',
    'EM_PREPARO',
    'ENVIADO',
    'ENTREGUE',
    'CANCELADO_CLIENTE',
    'CANCELADO_LOJA'
  ) NOT NULL,
  PRIMARY KEY (`id_status_pedido`),
  UNIQUE KEY `uk_status_pedido_desc` (`descricao`)
) ENGINE=InnoDB;

INSERT INTO `status_pedido` (`id_status_pedido`, `descricao`) VALUES
(1,'PENDENTE'),
(2,'ACEITO'),
(3,'EM_PREPARO'),
(4,'ENVIADO'),
(5,'ENTREGUE'),
(6,'CANCELADO_CLIENTE'),
(7,'CANCELADO_LOJA');

/* =========================
   status_pagamento    ========================= */
CREATE TABLE `status_pagamento` (
  `id_status_pagamento` INT NOT NULL AUTO_INCREMENT,
  `descricao` ENUM('PENDENTE','PAGO','CANCELADO','REEMBOLSADO') NOT NULL,
  PRIMARY KEY (`id_status_pagamento`),
  UNIQUE KEY `uk_status_pagamento_desc` (`descricao`)
) ENGINE=InnoDB;

INSERT INTO `status_pagamento` (`id_status_pagamento`, `descricao`) VALUES
(1,'PENDENTE'),
(2,'PAGO'),
(3,'CANCELADO'),
(4,'REEMBOLSADO');

/* =========================
   config_loja
   ========================= */
CREATE TABLE `config_loja` (
  `id_config` TINYINT NOT NULL DEFAULT 1,
  `hora_abre` TIME NOT NULL,
  `hora_fecha` TIME NOT NULL,
  `whatsapp` VARCHAR(20) NULL,
  `taxa_entrega_padrao` DECIMAL(8,2) NOT NULL DEFAULT 5.00,
  PRIMARY KEY (`id_config`)
) ENGINE=InnoDB;

INSERT INTO `config_loja` (`id_config`,`hora_abre`,`hora_fecha`,`whatsapp`,`taxa_entrega_padrao`)
VALUES (1,'15:00:00','22:00:00',NULL,5.00);

/* =========================
   usuario (admin/func) + Google opcional
   ========================= */
CREATE TABLE `usuario` (
  `id_usuario` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(50) NOT NULL,
  `email` VARCHAR(50) NOT NULL,
  `senha` VARCHAR(255) NULL,
  `telefone` VARCHAR(20) NOT NULL,
  `tipo_usuario` ENUM('ADMIN','FUNCIONARIO') NOT NULL,
  `root` BOOLEAN NOT NULL DEFAULT FALSE,
  `permissoes` VARCHAR(255) NULL COMMENT 'Modulos liberados: pedidos,extrato,produtos,usuarios,config',
  `auth_provider` ENUM('LOCAL','GOOGLE') NOT NULL DEFAULT 'LOCAL',
  `google_subject` VARCHAR(191) NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uk_usuario_email` (`email`),
  UNIQUE KEY `uk_usuario_telefone` (`telefone`),
  UNIQUE KEY `uk_usuario_google_subject` (`google_subject`)
) ENGINE=InnoDB;

INSERT INTO `usuario` (
  `id_usuario`,`nome`,`email`,`senha`,`telefone`,`tipo_usuario`,`root`,`permissoes`,`auth_provider`,`google_subject`
) VALUES (
  1,
  'Administrador',
  'admin@chokkomelt.local',
  '$2y$10$s1ZwdFfofCtlfDptvhiqDe7scUwXLcaWFgxFYfb6DFpCKMGwbOjLi',
  '11999999999',
  'ADMIN',
  TRUE,
  'pedidos,extrato,produtos,usuarios,config',
  'LOCAL',
  NULL
);

/* =========================
   cliente + Google +
   ========================= */
CREATE TABLE `cliente` (
  `id_cliente` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(50) NOT NULL,
  `email` VARCHAR(50) NOT NULL,
  `senha` VARCHAR(255) NULL,
  `telefone` VARCHAR(20) NOT NULL,
  `auth_provider` ENUM('LOCAL','GOOGLE') NOT NULL DEFAULT 'LOCAL',
  `google_subject` VARCHAR(191) NULL,
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `uk_cliente_email` (`email`),
  UNIQUE KEY `uk_cliente_telefone` (`telefone`),
  UNIQUE KEY `uk_cliente_google_subject` (`google_subject`)
) ENGINE=InnoDB;

/* =========================
   categoria
   ========================= */
CREATE TABLE `categoria` (
  `id_categoria` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `uk_categoria_nome` (`nome`)
) ENGINE=InnoDB;

INSERT INTO `categoria` (`id_categoria`,`nome`) VALUES (1,'Bolos de Pote');

/* =========================
   adicional
   ========================= */
CREATE TABLE `adicional` (
  `id_adicional` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(80) NOT NULL,
  `preco` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `custo` DECIMAL(8,2) NULL,
  `ativo` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id_adicional`),
  UNIQUE KEY `uk_adicional_nome` (`nome`)
) ENGINE=InnoDB;

INSERT INTO `adicional` (`id_adicional`,`nome`,`preco`,`custo`,`ativo`)
VALUES (1,'Cobertura extra',2.50,0.80,TRUE);

/* =========================
   produto
   ========================= */
CREATE TABLE `produto` (
  `id_produto` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(50) NOT NULL,
  `descricao` VARCHAR(500) NULL,
  `disponibilidade` BOOLEAN NOT NULL DEFAULT TRUE,
  `imagem` VARCHAR(255) NULL,
  `preco` DECIMAL(8,2) NOT NULL,
  `custo_compra` DECIMAL(8,2) NULL,
  `id_categoria` INT NULL,
  PRIMARY KEY (`id_produto`),
  KEY `fk_produto_categoria` (`id_categoria`),
  CONSTRAINT `fk_produto_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`)
) ENGINE=InnoDB;

INSERT INTO `produto` (`id_produto`,`nome`,`descricao`,`disponibilidade`,`imagem`,`preco`,`custo_compra`,`id_categoria`)
VALUES (1,'Bolo de Chocolate no Pote','Recheio tradicional',TRUE,NULL,12.90,5.50,1);

CREATE TABLE `produto_adicional` (
  `id_produto` INT NOT NULL,
  `id_adicional` INT NOT NULL,
  PRIMARY KEY (`id_produto`,`id_adicional`),
  CONSTRAINT `fk_pa_produto` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`) ON DELETE CASCADE,
  CONSTRAINT `fk_pa_adicional` FOREIGN KEY (`id_adicional`) REFERENCES `adicional` (`id_adicional`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `produto_adicional` (`id_produto`,`id_adicional`) VALUES (1,1);

/* =========================
   endereco
   ========================= */
CREATE TABLE `endereco` (
  `id_endereco` INT NOT NULL AUTO_INCREMENT,
  `rua` VARCHAR(50) NULL,
  `numero` VARCHAR(10) NULL,
  `complemento` VARCHAR(50) NULL,
  `bairro` VARCHAR(30) NULL,
  `cep` VARCHAR(10) NULL,
  `ponto_referencia` VARCHAR(120) NULL,
  `id_cliente` INT NULL,
  PRIMARY KEY (`id_endereco`),
  KEY `fk_endereco_cliente` (`id_cliente`),
  CONSTRAINT `fk_endereco_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE CASCADE
) ENGINE=InnoDB;

/* =========================
   pedido
   ========================= */
CREATE TABLE `pedido` (
  `id_pedido` INT NOT NULL AUTO_INCREMENT,
  `data_hora` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `valor_total` DECIMAL(8,2) NOT NULL,
  `subtotal` DECIMAL(8,2) NULL,
  `custo_total` DECIMAL(10,2) NULL,
  `lucro` DECIMAL(10,2) NULL,
  `observacao` VARCHAR(200) NULL,
  `tipo_entrega` ENUM('DELIVERY','RETIRADA','LOCAL') NOT NULL,
  `taxa_entrega` DECIMAL(8,2) NULL DEFAULT 0.00,
  `cpf_nota` VARCHAR(14) NULL,
  `motivo_cancelamento` VARCHAR(500) NULL,
  `cancelado_por` ENUM('CLIENTE','ADMIN') NULL,
  `cancelado_em` TIMESTAMP NULL DEFAULT NULL,
  `id_usuario` INT NULL,
  `id_status_pedido` INT NULL,
  `id_cliente` INT NULL,
  `id_endereco` INT NULL,
  PRIMARY KEY (`id_pedido`),
  KEY `fk_pedido_usuario` (`id_usuario`),
  KEY `fk_pedido_status` (`id_status_pedido`),
  KEY `fk_pedido_cliente` (`id_cliente`),
  KEY `fk_pedido_endereco` (`id_endereco`),
  CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_pedido_status` FOREIGN KEY (`id_status_pedido`) REFERENCES `status_pedido` (`id_status_pedido`),
  CONSTRAINT `fk_pedido_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE SET NULL,
  CONSTRAINT `fk_pedido_endereco` FOREIGN KEY (`id_endereco`) REFERENCES `endereco` (`id_endereco`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `item_pedido` (
  `id_item_pedido` INT NOT NULL AUTO_INCREMENT,
  `preco_unitario` DECIMAL(8,2) NOT NULL,
  `quantidade` INT NOT NULL DEFAULT 1,
  `custo_unitario` DECIMAL(8,2) NULL,
  `observacao` VARCHAR(200) NULL,
  `id_produto` INT NULL,
  `id_pedido` INT NULL,
  PRIMARY KEY (`id_item_pedido`),
  KEY `fk_item_pedido_produto` (`id_produto`),
  KEY `fk_item_pedido_pedido` (`id_pedido`),
  CONSTRAINT `fk_item_pedido_produto` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`) ON DELETE SET NULL,
  CONSTRAINT `fk_item_pedido_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `item_pedido_adicional` (
  `id_item_pedido_adicional` INT NOT NULL AUTO_INCREMENT,
  `id_item_pedido` INT NOT NULL,
  `id_adicional` INT NULL,
  `nome_snapshot` VARCHAR(80) NOT NULL,
  `preco_unitario_snapshot` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `custo_unitario_snapshot` DECIMAL(8,2) NULL,
  `quantidade` INT NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_item_pedido_adicional`),
  KEY `fk_ipa_item` (`id_item_pedido`),
  KEY `fk_ipa_ad` (`id_adicional`),
  CONSTRAINT `fk_ipa_item` FOREIGN KEY (`id_item_pedido`) REFERENCES `item_pedido` (`id_item_pedido`) ON DELETE CASCADE,
  CONSTRAINT `fk_ipa_ad` FOREIGN KEY (`id_adicional`) REFERENCES `adicional` (`id_adicional`) ON DELETE SET NULL
) ENGINE=InnoDB;

/* =========================
   avaliacao
   ========================= */
CREATE TABLE `avaliacao` (
  `id_avaliacao` INT NOT NULL AUTO_INCREMENT,
  `estrelas` INT NOT NULL,
  `comentarios` VARCHAR(500) NULL,
  `id_pedido` INT NULL,
  PRIMARY KEY (`id_avaliacao`),
  UNIQUE KEY `uk_avaliacao_pedido` (`id_pedido`),
  CONSTRAINT `fk_avaliacao_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE
) ENGINE=InnoDB;

/* =========================
   carrinho (1 carrinho por cliente)
   ========================= */
CREATE TABLE `carrinho` (
  `id_carrinho` INT NOT NULL AUTO_INCREMENT,
  `id_cliente` INT NULL,
  PRIMARY KEY (`id_carrinho`),
  UNIQUE KEY `uk_carrinho_cliente` (`id_cliente`),
  CONSTRAINT `fk_carrinho_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `item_carrinho` (
  `id_item_carrinho` INT NOT NULL AUTO_INCREMENT,
  `preco_unitario` DECIMAL(8,2) NOT NULL,
  `quantidade` INT NOT NULL DEFAULT 1,
  `observacao` VARCHAR(200) NULL,
  `id_produto` INT NULL,
  `id_carrinho` INT NULL,
  PRIMARY KEY (`id_item_carrinho`),
  KEY `fk_ic_produto` (`id_produto`),
  KEY `fk_ic_carrinho` (`id_carrinho`),
  CONSTRAINT `fk_ic_produto` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`) ON DELETE CASCADE,
  CONSTRAINT `fk_ic_carrinho` FOREIGN KEY (`id_carrinho`) REFERENCES `carrinho` (`id_carrinho`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `item_carrinho_adicional` (
  `id_item_carrinho_adicional` INT NOT NULL AUTO_INCREMENT,
  `id_item_carrinho` INT NOT NULL,
  `id_adicional` INT NULL,
  `nome_snapshot` VARCHAR(80) NOT NULL,
  `preco_unitario_snapshot` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `custo_unitario_snapshot` DECIMAL(8,2) NULL,
  `quantidade` INT NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_item_carrinho_adicional`),
  KEY `fk_ica_ic` (`id_item_carrinho`),
  KEY `fk_ica_ad` (`id_adicional`),
  CONSTRAINT `fk_ica_ic` FOREIGN KEY (`id_item_carrinho`) REFERENCES `item_carrinho` (`id_item_carrinho`) ON DELETE CASCADE,
  CONSTRAINT `fk_ica_ad` FOREIGN KEY (`id_adicional`) REFERENCES `adicional` (`id_adicional`) ON DELETE CASCADE
) ENGINE=InnoDB;

/* =========================
   pagamento
   ========================= */
CREATE TABLE `pagamento` (
  `id_pagamento` INT NOT NULL AUTO_INCREMENT,
  `data_hora` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `valor_pago` DECIMAL(8,2) NOT NULL,
  `forma_pagamento` ENUM('PIX','DEBITO','CREDITO','DINHEIRO') NOT NULL,
  `valor_entregue` DECIMAL(8,2) NULL,
  `troco` DECIMAL(8,2) NULL,
  `id_status_pagamento` INT NULL,
  `id_pedido` INT NULL,
  PRIMARY KEY (`id_pagamento`),
  KEY `fk_pagamento_status` (`id_status_pagamento`),
  KEY `fk_pagamento_pedido` (`id_pedido`),
  CONSTRAINT `fk_pagamento_status` FOREIGN KEY (`id_status_pagamento`) REFERENCES `status_pagamento` (`id_status_pagamento`),
  CONSTRAINT `fk_pagamento_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

/* Fim do script. */
