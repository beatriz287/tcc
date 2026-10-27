-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 27/10/2026 às 01:01
-- Versão do servidor: 8.4.7
-- Versão do PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `petshop_admin`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `administradores`
--

DROP TABLE IF EXISTS `administradores`;
CREATE TABLE IF NOT EXISTS `administradores` (
  `id_admin` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Administrador',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `ultimo_login` datetime DEFAULT NULL,
  `data_cadastro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `uk_administradores_usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `administradores`
--

INSERT INTO `administradores` (`id_admin`, `usuario`, `senha_hash`, `nome`, `ativo`, `ultimo_login`, `data_cadastro`) VALUES
(1, 'admin', '$2y$12$afIFvgUH4.qFr7mRap.ePu40qpkNwkLTuQxFXmghgWi8EaEv3PCY6', 'Administrador', 1, '2026-10-26 21:06:44', '2026-10-08 18:51:01');

-- --------------------------------------------------------

--
-- Estrutura para tabela `carrossel`
--

DROP TABLE IF EXISTS `carrossel`;
CREATE TABLE IF NOT EXISTS `carrossel` (
  `id_carrossel` int NOT NULL AUTO_INCREMENT,
  `imagem` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordem` int DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_carrossel`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `carrossel`
--

INSERT INTO `carrossel` (`id_carrossel`, `imagem`, `ordem`, `ativo`, `data_cadastro`) VALUES
(6, 'uploads/admin/20260827_113000_70cf59ea95.png', 0, 1, '2026-08-19 11:21:41'),
(7, 'uploads/admin/20260827_113006_a39488a543.png', 0, 1, '2026-08-19 11:22:38'),
(8, 'uploads/admin/20260827_113011_04eb7bbf71.png', 0, 1, '2026-08-19 11:22:41'),
(9, 'uploads/admin/20260827_113015_661d618b35.png', 0, 1, '2026-08-19 11:22:46'),
(10, 'uploads/admin/20260827_113020_eeb8882ec6.png', 0, 1, '2026-08-19 11:22:49');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias_menu`
--

DROP TABLE IF EXISTS `categorias_menu`;
CREATE TABLE IF NOT EXISTS `categorias_menu` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordem` int DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `categorias_menu`
--

INSERT INTO `categorias_menu` (`id_categoria`, `nome`, `link`, `ordem`, `ativo`, `data_cadastro`) VALUES
(1, 'Cachorros', 'cachorros.html', 0, 1, '2026-08-13 09:21:51'),
(2, 'Gatos', 'gatos.html', 0, 1, '2026-08-13 09:22:09'),
(5, 'Blog', 'blog.html', 0, 1, '2026-08-27 09:02:34');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias_produtos`
--

DROP TABLE IF EXISTS `categorias_produtos`;
CREATE TABLE IF NOT EXISTS `categorias_produtos` (
  `id_categoria_produto` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_pet` enum('cachorro','gato','ambos') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ambos',
  `ordem` int NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_categoria_produto`),
  UNIQUE KEY `uk_categoria_produto_slug_pet` (`slug`,`tipo_pet`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `categorias_produtos`
--

INSERT INTO `categorias_produtos` (`id_categoria_produto`, `nome`, `slug`, `tipo_pet`, `ordem`, `ativo`, `data_cadastro`) VALUES
(2, 'Petiscos', 'petiscos', 'ambos', 2, 1, '2026-08-27 09:20:40'),
(3, 'Roupas e Acessórios', 'roupas', 'cachorro', 3, 1, '2026-08-27 09:20:40'),
(4, 'Coleiras, Guias e Peitorais', 'coleiras', 'cachorro', 4, 1, '2026-08-27 09:20:40'),
(5, 'Caminhas e Casinhas', 'camas', 'ambos', 5, 1, '2026-08-27 09:20:40'),
(6, 'Proteção e Adestramento', 'adestramento', 'cachorro', 6, 1, '2026-08-27 09:20:40'),
(8, 'Medicamentos', 'medicamentos', 'ambos', 8, 1, '2026-08-27 09:20:40'),
(9, 'Higiene e Limpeza', 'higiene', 'ambos', 9, 1, '2026-08-27 09:20:40'),
(10, 'Transporte', 'transporte', 'ambos', 10, 1, '2026-08-27 09:20:40'),
(11, 'Brinquedos', 'brinquedos', 'ambos', 11, 1, '2026-08-27 09:20:40'),
(12, 'Areias e Banheiros', 'areias', 'gato', 12, 1, '2026-08-27 09:20:40'),
(13, 'Arranhadores', 'arranhadores', 'gato', 13, 1, '2026-08-27 09:20:40'),
(14, 'Acessórios', 'acessorios', 'ambos', 14, 1, '2026-08-27 09:20:40'),
(15, 'Outros', 'outros', 'ambos', 99, 1, '2026-08-27 09:20:40'),
(16, 'Alimentação', 'alimentacao', 'ambos', 1, 1, '2026-08-27 09:21:49');

-- --------------------------------------------------------

--
-- Estrutura para tabela `enderecos_usuario`
--

DROP TABLE IF EXISTS `enderecos_usuario`;
CREATE TABLE IF NOT EXISTS `enderecos_usuario` (
  `id_endereco` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` int UNSIGNED NOT NULL,
  `cep` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rua` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `complemento` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cidade` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_endereco`),
  UNIQUE KEY `uk_endereco_usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `enderecos_usuario`
--

INSERT INTO `enderecos_usuario` (`id_endereco`, `id_usuario`, `cep`, `rua`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `criado_em`, `atualizado_em`) VALUES
(1, 1, '13760037', 'Treze de Maio', '99', 'nhugob', 'Centro', 'Tapiratiba', 'SP', '2026-10-08 22:23:53', '2026-10-08 22:23:53');

-- --------------------------------------------------------

--
-- Estrutura para tabela `exclusivos`
--

DROP TABLE IF EXISTS `exclusivos`;
CREATE TABLE IF NOT EXISTS `exclusivos` (
  `id_exclusivo` int NOT NULL AUTO_INCREMENT,
  `imagem` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordem` int DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_exclusivo`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `exclusivos`
--

INSERT INTO `exclusivos` (`id_exclusivo`, `imagem`, `ordem`, `ativo`, `data_cadastro`) VALUES
(14, 'uploads/admin/20260917_113319_4ee73420bd.png', 0, 1, '2026-08-19 11:29:50'),
(15, 'uploads/admin/20261010_170016_1f048a29d6.png', 0, 1, '2026-10-10 14:00:16'),
(16, 'uploads/admin/20261010_170036_c01dce4764.png', 0, 1, '2026-10-10 14:00:36'),
(17, 'uploads/admin/20261010_170042_a6946f3353.png', 0, 1, '2026-10-10 14:00:42'),
(18, 'uploads/admin/20261010_170052_876499f438.png', 0, 1, '2026-10-10 14:00:52'),
(19, 'uploads/admin/20261010_170058_55599840e6.png', 0, 1, '2026-10-10 14:00:58'),
(20, 'uploads/admin/20261010_170255_c1690ba665.png', 0, 1, '2026-10-10 14:02:55'),
(21, 'uploads/admin/20261010_170259_8c83131d65.png', 0, 1, '2026-10-10 14:02:59'),
(22, 'uploads/admin/20261010_170305_89dd2c738f.png', 0, 1, '2026-10-10 14:03:05'),
(23, 'uploads/admin/20261010_170308_b470b0b8a8.png', 0, 1, '2026-10-10 14:03:08'),
(24, 'uploads/admin/20261010_170314_61727ae648.png', 0, 1, '2026-10-10 14:03:14'),
(25, 'uploads/admin/20261010_170320_d02f3d43f5.png', 0, 1, '2026-10-10 14:03:20'),
(26, 'uploads/admin/20261010_170323_884c307037.png', 0, 1, '2026-10-10 14:03:23');

-- --------------------------------------------------------

--
-- Estrutura para tabela `favoritos`
--

DROP TABLE IF EXISTS `favoritos`;
CREATE TABLE IF NOT EXISTS `favoritos` (
  `id_favorito` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` int UNSIGNED NOT NULL,
  `id_produto` int NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_favorito`),
  UNIQUE KEY `uk_favorito_usuario_produto` (`id_usuario`,`id_produto`),
  KEY `idx_favoritos_produto` (`id_produto`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `favoritos`
--

INSERT INTO `favoritos` (`id_favorito`, `id_usuario`, `id_produto`, `data_cadastro`) VALUES
(11, 2, 13, '2026-10-23 19:30:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `lojas`
--

DROP TABLE IF EXISTS `lojas`;
CREATE TABLE IF NOT EXISTS `lojas` (
  `id_loja` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `endereco` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cep` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horario` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mapa_url` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imagem` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `servicos` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ordem` int DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_loja`),
  KEY `idx_lojas_ativo_ordem` (`ativo`,`ordem`),
  KEY `idx_lojas_cidade` (`cidade`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `lojas`
--

INSERT INTO `lojas` (`id_loja`, `nome`, `endereco`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `cep`, `telefone`, `whatsapp`, `horario`, `mapa_url`, `imagem`, `servicos`, `ordem`, `ativo`, `data_cadastro`) VALUES
(1, 'Garras & Patas Tapiratiba', 'Rua 13 de Maio', '144', '', 'Centro', 'Tapiratiba', 'SP', '13760037', '19 12345 6789', '19 12345 6789', '09:10', '', '', NULL, 0, 1, '2026-10-01 08:47:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `marcas`
--

DROP TABLE IF EXISTS `marcas`;
CREATE TABLE IF NOT EXISTS `marcas` (
  `id_marca` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagem` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_marca`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `marcas`
--

INSERT INTO `marcas` (`id_marca`, `nome`, `imagem`, `ativo`, `data_cadastro`) VALUES
(10, 'GranPlus', 'uploads/admin/20260827_113854_78a437bf48.png', 1, '2026-08-19 11:34:26'),
(11, 'Katbom', 'uploads/admin/20260827_113919_18a8bbc163.jpg', 1, '2026-08-19 11:37:28'),
(12, 'Chalesco', 'uploads/admin/20260827_113935_2c72daa43a.webp', 1, '2026-08-27 08:39:35'),
(13, 'Divert pet', 'uploads/admin/20260827_114707_92a18a5ca6.png', 1, '2026-08-27 08:47:07'),
(14, 'Fábrica Pet', 'uploads/admin/20260827_114716_ce459a95a5.png', 1, '2026-08-27 08:47:16'),
(15, 'Ferplast', 'uploads/admin/20260827_114725_85780438a1.png', 1, '2026-08-27 08:47:25'),
(16, 'Furacão Pet', 'uploads/admin/20260827_114740_45967ea9de.png', 1, '2026-08-27 08:47:40'),
(17, 'Kong', 'uploads/admin/20260827_121157_4c614a5c1a.png', 1, '2026-08-27 09:11:57'),
(18, 'Jambo Pet', 'uploads/admin/20260827_121207_1cb61d8567.png', 1, '2026-08-27 09:12:07'),
(19, 'Magnus', 'uploads/admin/20260827_121219_c8eb103405.png', 1, '2026-08-27 09:12:19'),
(20, 'MSD', 'uploads/admin/20260827_121231_ee94857699.png', 1, '2026-08-27 09:12:31'),
(21, 'Petbrilho', 'uploads/admin/20260827_121245_fd500dfb35.png', 1, '2026-08-27 09:12:45'),
(22, 'Pet Clean', 'uploads/admin/20260827_121306_1d0475a763.png', 1, '2026-08-27 09:13:06'),
(23, 'Petitos', 'uploads/admin/20260827_121313_8c5dd12eb2.png', 1, '2026-08-27 09:13:13'),
(24, 'Pet Society', 'uploads/admin/20260827_121328_bfd44f44c5.png', 1, '2026-08-27 09:13:28'),
(25, 'Pipicat', 'uploads/admin/20260917_114423_9dc3870a29.png', 1, '2026-09-17 08:44:23'),
(26, 'Procão', 'uploads/admin/20260917_114448_6c33193154.png', 1, '2026-09-17 08:44:48'),
(27, 'São Pet', 'uploads/admin/20260917_114504_dd62b655f8.png', 1, '2026-09-17 08:45:04'),
(28, 'Special Cat', 'uploads/admin/20260917_114755_2343739fb7.png', 1, '2026-09-17 08:47:55'),
(29, 'Selections', 'uploads/admin/20260917_114803_ad37ae48e2.png', 1, '2026-09-17 08:48:03'),
(30, 'Petz', 'uploads/admin/20260917_114821_2836e9d2b4.png', 1, '2026-09-17 08:48:21'),
(31, 'Special Dog', 'uploads/admin/20260917_114839_06c5424e8c.png', 1, '2026-09-17 08:48:39'),
(32, 'Toh', 'uploads/admin/20260917_114848_b1a0736f37.png', 1, '2026-09-17 08:48:48'),
(33, 'Vetnil', 'uploads/admin/20260917_114857_52b54486ec.png', 1, '2026-09-17 08:48:57'),
(34, 'Viva Verde', 'uploads/admin/20260917_114907_5dd73bca9d.png', 1, '2026-09-17 08:49:07'),
(35, 'Zee.dog', 'uploads/admin/20260917_114916_f9d23468b1.png', 1, '2026-09-17 08:49:16'),
(36, 'Zoetis', 'uploads/admin/20260917_114925_3001ebb40a.png', 1, '2026-09-17 08:49:25'),
(37, 'Zooz Pets', 'uploads/admin/20260917_114936_c1a522b61a.png', 1, '2026-09-17 08:49:36'),
(38, 'Plast Pet', '', 0, '2026-10-01 08:28:18'),
(39, 'Fresh', '', 0, '2026-10-10 14:30:10'),
(40, 'Gattino', '', 0, '2026-10-10 14:47:57'),
(41, 'Hello pet', '', 0, '2026-10-10 16:13:11'),
(42, 'São benedito', '', 0, '2026-10-10 16:16:33'),
(43, 'Alvorada', '', 0, '2026-10-10 19:30:48'),
(44, 'trusqys pet', '', 0, '2026-10-10 20:18:45'),
(45, 'Vida mansa', '', 0, '2026-10-10 20:21:19'),
(46, 'Nylabone', '', 0, '2026-10-13 20:31:38'),
(47, 'Petstage', '', 0, '2026-10-13 21:58:28'),
(48, 'Dura pet', '', 0, '2026-10-13 22:18:11'),
(49, 'Chalesco', '', 0, '2026-10-26 21:08:38'),
(50, 'Churu', '', 0, '2026-10-26 21:37:24'),
(51, 'Nutrifresh', '', 0, '2026-10-26 21:39:10'),
(52, 'Elaco', '', 0, '2026-10-26 21:49:30');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id_pedido` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` int UNSIGNED NOT NULL,
  `codigo_pedido` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `frete` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `nome_destinatario` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cep` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rua` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_pedido` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pedido`),
  UNIQUE KEY `uk_pedido_codigo` (`codigo_pedido`),
  KEY `idx_pedidos_usuario_data` (`id_usuario`,`data_pedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedido_itens`
--

DROP TABLE IF EXISTS `pedido_itens`;
CREATE TABLE IF NOT EXISTS `pedido_itens` (
  `id_pedido_item` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_pedido` int UNSIGNED NOT NULL,
  `id_produto` int NOT NULL,
  `id_variacao` int DEFAULT NULL,
  `nome_produto` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_variacao` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preco_unitario` decimal(10,2) NOT NULL DEFAULT '0.00',
  `desconto_percentual` decimal(5,2) NOT NULL DEFAULT '0.00',
  `quantidade` int UNSIGNED NOT NULL DEFAULT '1',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_pedido_item`),
  KEY `idx_pedido_itens_pedido` (`id_pedido`),
  KEY `idx_pedido_itens_produto` (`id_produto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `planos_saude`
--

DROP TABLE IF EXISTS `planos_saude`;
CREATE TABLE IF NOT EXISTS `planos_saude` (
  `id_plano` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `coberturas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `preco` decimal(10,2) NOT NULL,
  `ordem` int DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_plano`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `planos_saude`
--

INSERT INTO `planos_saude` (`id_plano`, `nome`, `descricao`, `coberturas`, `preco`, `ordem`, `ativo`, `data_cadastro`) VALUES
(1, 'Leve', 'Cuidado básico', 'Consulta veterinária em horário comercial\r\nConsulta veterinária de emergência\r\nVacinas essenciais\r\nOrientação veterinária\r\nDesconto em medicamentos', 14.90, 1, 1, '2026-08-13 09:16:06'),
(2, 'Tranquilo', 'Ambulatorial de rotina', 'Consultas veterinárias\r\nAtendimento de emergência\r\nVacinas essenciais\r\nExames laboratoriais básicos\r\nExames de imagem simples\r\nDesconto em medicamentos', 34.90, 2, 1, '2026-08-13 09:25:48'),
(3, 'Ideal', 'Hospitalar com internação', 'Consultas veterinárias ilimitadas\r\nAtendimento de emergência\r\nVacinas essenciais\r\nExames laboratoriais\r\nUltrassonografia\r\nRaio-X\r\nInternação veterinária\r\nDesconto em medicamentos', 94.90, 3, 1, '2026-08-13 09:26:21'),
(4, 'Completo', 'Cobertura completa', 'Consultas veterinárias ilimitadas\r\nAtendimento de emergência 24 horas\r\nVacinas\r\nExames laboratoriais completos\r\nExames de imagem avançados\r\nInternação\r\nCirurgias\r\nEspecialistas veterinários\r\nProcedimentos de alta complexidade', 415.00, 6, 1, '2026-10-02 20:51:53'),
(5, 'Premium', 'Cobertura avançada', 'Consultas veterinárias ilimitadas\r\nAtendimento 24 horas\r\nVacinas\r\nExames laboratoriais completos\r\nExames de imagem\r\nInternação\r\nProcedimentos ambulatoriais\r\nCirurgias de baixa complexidade', 149.90, 4, 1, '2026-10-02 21:08:18'),
(6, 'Essencial', 'Cobertura essencial', 'Consultas ilimitadas\r\nAtendimento veterinário 24 horas\r\nVacinação\r\nExames laboratoriais\r\nRaio-X e ultrassom\r\nInternação\r\nCirurgias\r\nProcedimentos especializados', 204.90, 5, 1, '2026-10-02 21:08:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos_destaque`
--

DROP TABLE IF EXISTS `produtos_destaque`;
CREATE TABLE IF NOT EXISTS `produtos_destaque` (
  `id_produto` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `imagem` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `id_marca` int DEFAULT NULL,
  `tipo_pet` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ambos',
  `categoria` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'outros',
  `id_categoria_produto` int DEFAULT NULL,
  `destaque` tinyint(1) NOT NULL DEFAULT '0',
  `promocao` tinyint(1) NOT NULL DEFAULT '0',
  `desconto_percentual` decimal(5,2) NOT NULL DEFAULT '0.00',
  `ordem` int DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_produto`),
  KEY `fk_produto_destaque_marca` (`id_marca`),
  KEY `idx_produtos_pet_categoria` (`tipo_pet`,`categoria`),
  KEY `idx_produtos_admin_filtros` (`categoria`,`id_marca`,`destaque`,`promocao`),
  KEY `idx_id_categoria_produto` (`id_categoria_produto`),
  KEY `idx_produtos_filtros` (`tipo_pet`,`id_categoria_produto`,`id_marca`,`destaque`,`promocao`)
) ENGINE=InnoDB AUTO_INCREMENT=249 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produtos_destaque`
--

INSERT INTO `produtos_destaque` (`id_produto`, `nome`, `descricao`, `imagem`, `preco`, `id_marca`, `tipo_pet`, `categoria`, `id_categoria_produto`, `destaque`, `promocao`, `desconto_percentual`, `ordem`, `ativo`, `data_cadastro`) VALUES
(13, 'Areia grãos finos', '', 'uploads/admin/20261010_173301_1ea7634009.jpg', 13.99, 39, 'gato', 'outros', 12, 1, 0, 0.00, 0, 1, '2026-10-10 14:33:01'),
(14, 'Areia grãos finos e perfumados', '', 'uploads/admin/20261010_174333_14e6a84b8f.jpg', 13.99, 39, 'gato', 'outros', 12, 0, 0, 0.00, 0, 1, '2026-10-10 14:43:33'),
(15, 'Areia grãos médios', '', 'uploads/admin/20261010_174500_7465bfe39a.jpg', 22.99, 39, 'gato', 'outros', 12, 0, 0, 0.00, 0, 1, '2026-10-10 14:45:00'),
(16, 'Areia milho e mandioca', '', 'uploads/admin/20261010_174546_3cb2f7f190.jpg', 22.99, 39, 'gato', 'outros', 12, 0, 0, 0.00, 0, 1, '2026-10-10 14:45:46'),
(17, 'Areia perfumada', '', 'uploads/admin/20261010_174931_2187f267b3.jpg', 14.99, 40, 'gato', 'outros', 12, 0, 0, 0.00, 0, 1, '2026-10-10 14:49:31'),
(18, 'Areia tradicional', '', 'uploads/admin/20261010_175146_5669e05fb2.jpg', 13.99, 40, 'gato', 'outros', 12, 1, 0, 0.00, 0, 1, '2026-10-10 14:51:46'),
(19, 'Areia super premium', '', 'uploads/admin/20261010_183341_603c18969a.jpg', 29.99, 11, 'gato', 'outros', 12, 0, 1, 5.00, 0, 1, '2026-10-10 15:33:41'),
(20, 'Areia limão e capim', '', 'uploads/admin/20261010_183545_c6f3443f95.jpg', 25.00, 11, 'gato', 'outros', 12, 0, 0, 0.00, 0, 1, '2026-10-10 15:35:45'),
(21, 'Areia tradicional', '', 'uploads/admin/20261010_183716_9e6c07c9b5.jpg', 15.00, 11, 'gato', 'outros', 12, 1, 2, 0.00, 0, 1, '2026-10-10 15:37:16'),
(22, 'Areia classic', '', 'uploads/admin/20261010_184102_96300be28b.jpg', 14.00, 25, 'gato', 'outros', 12, 0, 0, 0.00, 0, 1, '2026-10-10 15:41:02'),
(23, 'Areia floral', '', 'uploads/admin/20261010_184159_6403391817.png', 45.99, 25, 'gato', 'outros', 12, 1, 0, 0.00, 0, 1, '2026-10-10 15:41:59'),
(24, 'Areia multicat', '', 'uploads/admin/20261010_184257_58dc64365c.png', 52.00, 25, 'gato', 'outros', 12, 0, 0, 0.00, 0, 1, '2026-10-10 15:42:57'),
(25, 'Areia grãos finos', '', 'uploads/admin/20261010_184631_5a3c13c607.jpg', 49.99, 34, 'gato', 'outros', 12, 1, 0, 0.00, 0, 1, '2026-10-10 15:46:31'),
(26, 'Areia grãos mistos', '', 'uploads/admin/20261010_184735_c53d6b0b34.jpg', 53.99, 34, 'gato', 'outros', 12, 0, 1, 10.00, 0, 1, '2026-10-10 15:47:35'),
(27, 'Arranhador arco', '', 'uploads/admin/20261010_185059_250218117b.jpg', 89.00, 13, 'gato', 'outros', 13, 1, 2, 0.00, 0, 1, '2026-10-10 15:50:59'),
(29, 'Arranhador mola', '', 'uploads/admin/20261010_185458_6b5de1839a.jpg', 72.99, 13, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 15:54:58'),
(31, 'Arranhador peixe', '', 'uploads/admin/20261010_185618_386145acd2.png', 83.99, 13, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 15:56:18'),
(32, 'Arranhador peixe mola', '', 'uploads/admin/20261010_185711_6d4269b632.jpg', 73.99, 13, 'gato', 'outros', 13, 0, 2, 0.00, 0, 1, '2026-10-10 15:57:11'),
(34, 'Arranhador toca', '', 'uploads/admin/20261010_185853_8ed0f99c63.jpg', 105.00, 13, 'gato', 'outros', 13, 1, 0, 0.00, 0, 1, '2026-10-10 15:58:53'),
(35, 'Arranhador canto de sofá', '', 'uploads/admin/20261010_190500_8093eb001a.jpg', 95.00, 14, 'gato', 'outros', 13, 1, 2, 0.00, 0, 1, '2026-10-10 16:05:00'),
(36, 'arranhador iglu', '', 'uploads/admin/20261010_191042_da29635dbe.jpg', 135.99, 14, 'gato', 'outros', 13, 1, 0, 0.00, 0, 1, '2026-10-10 16:10:42'),
(37, 'Arranhador mola', '', 'uploads/admin/20261010_191333_977946db5d.jpg', 99.99, 41, 'gato', 'outros', 13, 1, 2, 0.00, 0, 1, '2026-10-10 16:13:33'),
(38, 'Arranhador sisal', '', 'uploads/admin/20261010_191449_06ef149aca.jpg', 125.00, 41, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 16:14:49'),
(39, 'Arranhador canto de sofá', '', 'uploads/admin/20261010_191712_aeebafe055.jpg', 142.99, 42, 'gato', 'outros', 13, 0, 2, 0.00, 0, 1, '2026-10-10 16:17:12'),
(40, 'Arranhador casa', '', 'uploads/admin/20261010_191929_e1fa29bcca.jpg', 205.00, 42, 'gato', 'outros', 13, 1, 1, 5.00, 0, 1, '2026-10-10 16:19:29'),
(41, 'Arranhador estrela', '', 'uploads/admin/20261010_220026_59c8e5c318.jpg', 110.00, 42, 'gato', 'outros', 13, 0, 1, 5.00, 0, 1, '2026-10-10 19:00:26'),
(42, 'Arranhador lua', '', 'uploads/admin/20261010_220148_d4acfc26f0.jpg', 105.00, 42, 'gato', 'outros', 13, 0, 1, 5.00, 0, 1, '2026-10-10 19:01:48'),
(43, 'Arranhador mola', '', 'uploads/admin/20261010_220355_6035db1a39.jpg', 96.99, 42, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:03:55'),
(44, 'Arranhador mola', '', 'uploads/admin/20261010_220606_a19f17c965.jpg', 99.99, 42, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:06:06'),
(45, 'Arranhador peixe', '', 'uploads/admin/20261010_220649_ef19c7912a.jpg', 89.99, 42, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:06:49'),
(46, 'Arranhador torre wave', '', 'uploads/admin/20261010_220836_4a24318658.jpg', 355.99, 42, 'gato', 'outros', 13, 0, 2, 0.00, 0, 1, '2026-10-10 19:08:36'),
(47, 'Arranhador wave', '', 'uploads/admin/20261010_221007_64017fb463.jpg', 240.00, 42, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:10:07'),
(48, 'Arranhador big ben', '', 'uploads/admin/20261010_221217_11bf23be29.png', 610.00, 27, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:12:17'),
(49, 'Arranhador casa cuco', '', 'uploads/admin/20261010_221317_c93b299dd2.png', 712.99, 27, 'gato', 'outros', 13, 0, 2, 0.00, 0, 1, '2026-10-10 19:13:17'),
(50, 'Arranhador castelo', '', 'uploads/admin/20261010_221451_749eca53b2.png', 755.00, 27, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:14:51'),
(51, 'Arranhador max ball', '', 'uploads/admin/20261010_221535_b0092653c9.png', 199.99, 27, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:15:35'),
(52, 'Arranhador mola', '', 'uploads/admin/20261010_221624_f9e43395a2.png', 154.99, 27, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:16:24'),
(53, 'Arranhador obelisco', '', 'uploads/admin/20261010_221819_e2a51c0238.png', 215.00, 27, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:18:19'),
(54, 'Arranhador olimpus', '', 'uploads/admin/20261010_221937_027759b525.png', 854.99, 27, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:19:37'),
(55, 'Arranhador parede', '', 'uploads/admin/20261010_222039_63c7f71370.png', 189.99, 27, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:20:39'),
(56, 'Arranhador peixe', '', 'uploads/admin/20261010_222135_dd3cb4b075.jpg', 89.00, 27, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:21:35'),
(57, 'Arranhador ring', '', 'uploads/admin/20261010_222400_73bff29409.png', 189.99, 27, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:24:00'),
(58, 'Arranhador snail', '', 'uploads/admin/20261010_222502_b1f24b09dc.png', 612.00, 27, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:25:02'),
(59, 'Arranhador takeball', '', 'uploads/admin/20261010_222613_c557889a16.png', 635.00, 27, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:26:13'),
(60, 'Arranhador toboga', '', 'uploads/admin/20261010_222707_0f30d071d7.jpg', 955.00, 27, 'gato', 'outros', 13, 0, 2, 0.00, 0, 1, '2026-10-10 19:27:07'),
(61, 'Arranhador torre', '', 'uploads/admin/20261010_222805_87eb9ee86b.jpg', 578.00, 27, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-10 19:28:05'),
(62, 'Bebedouro automatico', '', 'uploads/admin/20261010_223154_a650799215.png', 99.99, 43, 'ambos', 'outros', 16, 0, 1, 5.00, 0, 1, '2026-10-10 19:31:54'),
(63, 'Bebedouro cachoeira', '', 'uploads/admin/20261010_223429_088b655255.png', 109.00, 16, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-10 19:34:29'),
(64, 'Bebedouro joy', '', 'uploads/admin/20261010_223609_781bf3f8f5.png', 87.99, 16, 'ambos', 'outros', 16, 0, 1, 5.00, 0, 1, '2026-10-10 19:36:09'),
(65, 'Bebedouro pata', '', 'uploads/admin/20261010_223830_7e27444d42.png', 38.99, 16, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-10 19:38:30'),
(66, 'Bebedouro pop', '', 'uploads/admin/20261010_230918_aa52d37242.jpg', 45.00, 16, 'ambos', 'outros', 16, 0, 1, 5.00, 0, 1, '2026-10-10 20:09:18'),
(67, 'Bebedouro automático', '', 'uploads/admin/20261010_231123_4937604da4.jpg', 89.00, 38, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-10 20:11:23'),
(68, 'Bebedouro duplo', '', 'uploads/admin/20261010_231304_31348f2925.png', 69.99, 38, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-10 20:13:04'),
(69, 'Bebedouro portátil', '', 'uploads/admin/20261010_231610_6ba1dd70f3.png', 53.99, 38, 'ambos', 'outros', 16, 0, 1, 10.00, 0, 1, '2026-10-10 20:16:10'),
(70, 'Bebedouro galão automático', '', 'uploads/admin/20261010_231928_f647c5ee7b.jpg', 157.99, 44, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-10 20:19:28'),
(71, 'Bebedouro alumínio', '', 'uploads/admin/20261010_232208_1a4095f75d.png', 62.00, 45, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-10 20:22:08'),
(72, 'Bebedouro polipropileno', '', 'uploads/admin/20261010_232352_cc784fe863.png', 116.00, 45, 'ambos', 'outros', 16, 0, 1, 10.00, 0, 1, '2026-10-10 20:23:52'),
(73, 'Bebedouro portátil', '', 'uploads/admin/20261010_232546_f7daa81ec8.jpg', 83.99, 45, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-10 20:25:46'),
(74, 'Brinquedo bola', '', 'uploads/admin/20261010_232817_1b2a50ec9d.png', 26.00, 12, 'cachorro', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-10 20:28:17'),
(75, 'Brinquedo corda com bola', '', 'uploads/admin/20261010_232949_d4d6e3f21e.png', 54.00, 12, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-10 20:29:49'),
(76, 'Brinquedo bola de tênis', '', 'uploads/admin/20261010_233048_cdf5ccd7c0.png', 14.99, 12, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-10 20:30:48'),
(77, 'Brinquedo cachorro corda', '', 'uploads/admin/20261013_141055_22ce214088.png', 93.00, 12, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:10:55'),
(78, 'Brinquedo corda', '', 'uploads/admin/20261013_141136_c17eb99aa9.webp', 54.99, 12, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:11:36'),
(79, 'Brinquedo corda', '', 'uploads/admin/20261013_141216_5794e611c4.png', 65.00, 12, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:12:16'),
(80, 'Brinquedo cordatwist', '', 'uploads/admin/20261013_141315_18babca7e9.webp', 98.00, 12, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:13:15'),
(81, 'Brinquedo corda vinil', '', 'uploads/admin/20261013_141416_9753d1c10a.jpg', 112.00, 12, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:14:16'),
(82, 'Pelúcia macaco', '', 'uploads/admin/20261013_141459_4fde5745ed.jpg', 57.99, 12, 'ambos', 'outros', 11, 0, 1, 5.00, 0, 1, '2026-10-13 11:14:59'),
(83, 'Pelúcia porco', '', 'uploads/admin/20261013_141621_381dcc74e1.png', 56.00, 12, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:16:21'),
(84, 'pelúcia sapo', '', 'uploads/admin/20261013_141653_c5f6f75a11.png', 53.99, 12, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:16:53'),
(85, 'Pelúcia urso', '', 'uploads/admin/20261013_141737_f9d4669099.png', 56.00, 12, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:17:37'),
(86, 'Pelúcia tigre corda', '', 'uploads/admin/20261013_141811_0fb6f9a589.png', 78.00, 12, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:18:11'),
(87, 'Arranhador interativo', '', 'uploads/admin/20261013_141909_9c0ed53f7d.png', 124.00, 12, 'gato', 'outros', 13, 0, 0, 0.00, 0, 1, '2026-10-13 11:19:09'),
(88, 'Bolinha cat nip', '', 'uploads/admin/20261013_142005_619113dac7.png', 48.00, 12, 'gato', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:20:05'),
(89, 'Lazer', '', 'uploads/admin/20261013_142052_1293e0bdb0.png', 79.99, 12, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:20:52'),
(90, 'Polvo sxratch', '', 'uploads/admin/20261013_142141_ec465523e3.webp', 99.99, 12, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:21:41'),
(91, 'Tunel 3 saídas', '', 'uploads/admin/20261013_142246_8a8985b5df.png', 182.00, 12, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:22:46'),
(92, 'Varinha rato', '', 'uploads/admin/20261013_142321_eb8186639b.png', 63.99, 12, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:23:21'),
(93, 'Anel corda', '', 'uploads/admin/20261013_142445_299c31cb40.png', 74.00, 16, 'ambos', 'outros', 11, 0, 1, 5.00, 0, 1, '2026-10-13 11:24:45'),
(94, 'Bastão torcido', '', 'uploads/admin/20261013_142615_79cf73dabe.png', 62.00, 16, 'ambos', 'outros', 11, 0, 1, 5.00, 0, 1, '2026-10-13 11:26:15'),
(95, 'bola', '', 'uploads/admin/20261013_142718_4d394ab9ba.png', 27.00, 16, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:27:18'),
(96, 'Bola corda', '', 'uploads/admin/20261013_142820_c010b9bbb8.webp', 42.00, 16, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:28:20'),
(97, 'Bola cravo', '', 'uploads/admin/20261013_142921_dedeb3502d.png', 12.00, 16, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:29:21'),
(98, 'Halteres', '', 'uploads/admin/20261013_143020_2b093d4e34.png', 46.00, 16, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:30:20'),
(99, 'halteres curvo', '', 'uploads/admin/20261013_143203_fd62763e5c.png', 53.99, 16, 'ambos', 'outros', 11, 0, 1, 10.00, 0, 1, '2026-10-13 11:32:03'),
(100, 'Osso cravo', '', 'uploads/admin/20261013_143315_2ab4285343.png', 34.99, 16, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:33:15'),
(101, 'Bola', '', 'uploads/admin/20261013_143739_9cf68d94c3.png', 14.99, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:37:39'),
(102, 'Belly flops', '', 'uploads/admin/20261013_143813_67d1991d84.png', 29.99, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:38:13'),
(103, 'Bounzer', '', 'uploads/admin/20261013_143845_c24fc54614.png', 38.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 11:38:45'),
(104, 'Chewstix sticks', '', 'uploads/admin/20261013_223839_c8cd43eefe.png', 57.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:38:39'),
(105, 'Pelúcia cozie', '', 'uploads/admin/20261013_223924_f53d440c01.png', 67.99, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:39:24'),
(106, 'Pelúcia Elefante Cozie', '', 'uploads/admin/20261013_224201_e7d1004a4a.webp', 72.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:42:01'),
(107, 'Pelúcia Cozie', 'Cavalinho', 'uploads/admin/20261013_224252_954157a110.png', 75.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:42:52'),
(108, 'Pelúcia Cozie', '', 'uploads/admin/20261013_224418_45ee1e9d3d.png', 72.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:44:18'),
(109, 'Pelúcia Cozie', 'Jacaré', 'uploads/admin/20261013_224518_26e3473d5f.png', 72.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:45:18'),
(110, 'Pelúcia Cozie', 'Macaquinho', 'uploads/admin/20261013_224607_593820afd8.png', 72.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:46:07'),
(111, 'Pelúcia Urso', 'Ursinho', 'uploads/admin/20261013_224712_848653ce56.png', 56.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:47:12'),
(112, 'Brinquedo Dental', '', 'uploads/admin/20261013_224843_309cc16c7d.png', 32.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:48:43'),
(113, 'Brinquedo Dotz', '', 'uploads/admin/20261013_224920_6f74891b0e.png', 40.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:49:20'),
(114, 'Brinquedo Duramax', 'Bola Cinza', 'uploads/admin/20261013_225007_8779219a50.png', 50.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:50:07'),
(115, 'Brinquedo Shakers', 'Flamingo', 'uploads/admin/20261013_225106_cdcc4cfad5.png', 52.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:51:06'),
(116, 'Brinquedo Tennis', 'Bolinha', 'uploads/admin/20261013_225207_5ceb68c9c8.png', 30.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:52:07'),
(117, 'Brinquedo Flipz', '', 'uploads/admin/20261013_225243_eb7ec5970f.png', 42.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:52:43'),
(118, 'Brinquedo Bola Rosa', '', 'uploads/admin/20261013_225319_1f2a48ee4a.png', 43.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:53:19'),
(119, 'Brinquedo Jumpjack', '', 'uploads/admin/20261013_225604_f8bfd108e9.png', 53.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:56:04'),
(120, 'Pelúcia Knots', 'Macaquinho', 'uploads/admin/20261013_225653_e2c7408da1.png', 68.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:56:53'),
(121, 'Brinquedo Rewards', '', 'uploads/admin/20261013_225731_29400fa090.png', 60.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:57:31'),
(122, 'Brinquedo Tugtoy', '', 'uploads/admin/20261013_225804_661925871c.png', 49.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:58:04'),
(123, 'Brinquedo Wubba', '', 'uploads/admin/20261013_225839_afe1cacdba.png', 50.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:58:39'),
(124, 'Brinquedo Extremepinel', '', 'uploads/admin/20261013_225924_3d5c275cb7.png', 48.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 19:59:24'),
(125, 'Brinquedo Extreme', '', 'uploads/admin/20261013_230002_69aa3f96b8.png', 45.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 20:00:02'),
(126, 'Brinquedo Flyer', '', 'uploads/admin/20261013_230032_7e6b896f07.png', 48.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 20:00:32'),
(127, 'Brinquedo Gyro', '', 'uploads/admin/20261013_230108_5809ce7a66.png', 44.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 20:01:08'),
(128, 'Brinquedo Senior', '', 'uploads/admin/20261013_230139_0ff8b065a6.png', 48.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 20:01:39'),
(129, 'Brinquedo Wubba', '', 'uploads/admin/20261013_230207_7f7445ae4b.png', 50.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 20:02:07'),
(130, 'Gyroinfused', '', 'uploads/admin/20261013_232854_da247b9906.png', 0.00, 17, 'ambos', 'outros', 11, 0, 1, 5.00, 0, 1, '2026-10-13 20:28:54'),
(131, 'Rato', '', 'uploads/admin/20261013_232937_e8f9df2b98.png', 83.99, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 20:29:37'),
(132, 'Varinha passarinho', '', 'uploads/admin/20261013_233041_7cb69658f3.png', 66.00, 17, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 20:30:41'),
(133, 'Mordedor amendoin', '', 'uploads/admin/20261013_233214_746e97113c.jpg', 28.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 20:32:14'),
(134, 'Mordedor', '', 'uploads/admin/20261014_003019_b8ec83aafb.jpg', 39.99, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:30:19'),
(135, 'Mordedor osso', '', 'uploads/admin/20261014_003147_2f940d0b8a.jpg', 25.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:31:47'),
(136, 'Mordedor bacon', '', 'uploads/admin/20261014_003221_be1e106cff.jpg', 38.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:32:21'),
(137, 'Mordedor brocolis', '', 'uploads/admin/20261014_003718_b14b13d491.jpg', 41.00, 46, 'ambos', 'outros', 11, 0, 1, 5.00, 0, 1, '2026-10-13 21:37:18'),
(138, 'Mordedor chave', '', 'uploads/admin/20261014_003752_b3b9a2c499.jpg', 37.99, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:37:52'),
(139, 'Mordedor', '', 'uploads/admin/20261014_003834_4bcffc82d3.jpg', 43.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:38:34'),
(140, 'Mordedor', '', 'uploads/admin/20261014_003915_a8b152bacf.jpg', 38.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:39:15'),
(141, 'mordedor dinossauro', '', 'uploads/admin/20261014_004046_8315b90a49.jpg', 47.00, 46, 'ambos', 'outros', 11, 0, 1, 5.00, 0, 1, '2026-10-13 21:40:46'),
(142, 'Mordedor estrela', '', 'uploads/admin/20261014_004140_0c7e005717.jpg', 35.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:41:40'),
(143, 'Mordedor lagosta', '', 'uploads/admin/20261014_004719_a9ce100107.jpg', 36.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:47:19'),
(144, 'Mordedor madeira', '', 'uploads/admin/20261014_004746_5d6a8db899.jpg', 31.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:47:46'),
(145, 'Mordedor marshmallow', '', 'uploads/admin/20261014_004832_549476b532.jpg', 38.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:48:32'),
(146, 'Mordedor natal', '', 'uploads/admin/20261014_004859_56e175adef.jpg', 36.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:48:59'),
(147, 'Mordedor osso', '', 'uploads/admin/20261014_004949_d0ae7dc7fe.jpg', 43.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:49:49'),
(148, 'Mordedor osso e pano', '', 'uploads/admin/20261014_005019_e991065d17.jpg', 48.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:50:19'),
(149, 'Mordedor osso', '', 'uploads/admin/20261014_005100_85b77eda60.jpg', 34.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:51:00'),
(150, 'Mordedor oval', '', 'uploads/admin/20261014_005136_4cd5fc1914.jpg', 42.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:51:36'),
(151, 'Mordedor pretzel', '', 'uploads/admin/20261014_005241_849af0f046.jpg', 44.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:52:41'),
(152, 'Mordedor queijo', '', 'uploads/admin/20261014_005313_b1b23b43d8.jpg', 39.00, 46, 'ambos', 'outros', 11, 0, 1, 5.00, 0, 1, '2026-10-13 21:53:13'),
(153, 'Mordedor rosquinha', '', 'uploads/admin/20261014_005355_ee7ceba10c.jpg', 53.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:53:55'),
(154, 'Mordedor translucido', '', 'uploads/admin/20261014_005457_cc15abfbf3.jpg', 37.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:54:57'),
(155, 'Mordedor twistazul', '', 'uploads/admin/20261014_005534_9fa42e91fe.jpg', 46.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:55:34'),
(156, 'Mordedor x', '', 'uploads/admin/20261014_005619_67d427c66c.jpg', 41.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:56:19'),
(157, 'Mordedor curvo', '', 'uploads/admin/20261014_005658_1b6e6520b7.jpg', 32.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:56:58'),
(158, 'Bola', '', 'uploads/admin/20261014_005908_61d560c9f0.png', 38.00, 46, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:59:08'),
(159, 'Gira bola', '', 'uploads/admin/20261014_005958_eaf8d733a2.png', 86.00, 47, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 21:59:58'),
(160, 'Gaiola', '', 'uploads/admin/20261014_010030_8f64ea7f43.png', 94.00, 47, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 22:00:30'),
(161, 'Minhoca', '', 'uploads/admin/20261014_010115_16f8fd3afb.png', 31.00, 47, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 22:01:15'),
(162, 'Pelúcia abacate', '', 'uploads/admin/20261014_010246_b4c691ae6e.jpg', 48.99, 47, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 22:02:46'),
(163, 'Pelúcia dynamite', '', 'uploads/admin/20261014_010328_ed76c7e351.png', 53.00, 47, 'ambos', 'outros', 11, 0, 1, 5.00, 0, 1, '2026-10-13 22:03:28'),
(164, 'Pelúcia morango', '', 'uploads/admin/20261014_010417_2b81cc5887.png', 42.99, 47, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 22:04:17'),
(165, 'Pelúcia peixe', '', 'uploads/admin/20261014_010447_45a22bb58a.png', 48.00, 47, 'ambos', 'outros', 11, 0, 1, 5.00, 0, 1, '2026-10-13 22:04:47'),
(166, 'Pelúcia rato', '', 'uploads/admin/20261014_010524_15c6219fd5.png', 35.00, 47, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 22:05:24'),
(167, 'Pelúcia rato', '', 'uploads/admin/20261014_010558_acb7ecf31f.jpg', 48.00, 47, 'ambos', 'outros', 11, 0, 1, 10.00, 0, 1, '2026-10-13 22:05:58'),
(168, 'Pelúcia pretzel', '', 'uploads/admin/20261014_010651_18a58020dc.png', 38.00, 47, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 22:06:51'),
(169, 'Tapete', '', 'uploads/admin/20261014_010727_5aa5aa8436.png', 75.00, 47, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 22:07:27'),
(170, 'Torre bola', '', 'uploads/admin/20261014_010804_6f75ceffe6.png', 79.99, 47, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 22:08:04'),
(171, 'Frisbi', '', 'uploads/admin/20261014_011004_f3deb6cad6.png', 18.00, 47, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 22:10:04'),
(172, 'Mordedor banana', '', 'uploads/admin/20261014_011046_8d22df463d.png', 41.99, 47, 'ambos', 'outros', 11, 0, 1, 5.00, 0, 1, '2026-10-13 22:10:46'),
(173, 'Mordedor coelho', '', 'uploads/admin/20261014_011134_43cd4c73de.png', 47.99, 47, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 22:11:34'),
(174, 'Mordedor folha', '', 'uploads/admin/20261014_011226_a13260b9b1.png', 49.99, 47, 'ambos', 'outros', 11, 0, 1, 20.00, 0, 1, '2026-10-13 22:12:26'),
(175, 'Mordedor madeira', '', 'uploads/admin/20261014_011416_70fb36fe8a.webp', 34.99, 47, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 22:14:16'),
(176, 'Caixa de areia', '', 'uploads/admin/20261014_011530_e48821f023.png', 22.00, 43, 'gato', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-13 22:15:30'),
(177, 'Caixa furbox', '', 'uploads/admin/20261014_011845_b98da2cc0c.png', 43.99, 48, 'ambos', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-13 22:18:45'),
(178, 'Kit de areia', '', 'uploads/admin/20261014_011957_da25339d75.png', 47.00, 48, 'ambos', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-13 22:19:57'),
(179, 'Caixa luxo', '', 'uploads/admin/20261014_012052_aff4771d04.png', 51.00, 48, 'ambos', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-13 22:20:52'),
(180, 'Caixa single', '', 'uploads/admin/20261014_012234_04971313a2.png', 52.99, 48, 'ambos', 'outros', 11, 0, 0, 0.00, 0, 1, '2026-10-13 22:22:34'),
(181, 'Caixa classic plus', '', 'uploads/admin/20261014_012517_def10d2cd6.png', 54.99, 16, 'ambos', 'outros', 9, 0, 1, 5.00, 0, 1, '2026-10-13 22:25:17'),
(182, 'Caixa de transporte', '', 'uploads/admin/20261021_004602_6f3ac28b5f.png', 75.00, 48, 'ambos', 'outros', 10, 0, 0, 0.00, 0, 1, '2026-10-20 21:46:02'),
(183, 'Caixa de transporte', '', 'uploads/admin/20261021_004729_48cb3f5b9d.jpg', 88.00, 16, 'ambos', 'outros', 10, 0, 0, 0.00, 0, 1, '2026-10-20 21:47:29'),
(184, 'Caixa de transporte', '', 'uploads/admin/20261021_004838_911db1472a.png', 69.00, 38, 'ambos', 'outros', 10, 0, 0, 0.00, 0, 1, '2026-10-20 21:48:38'),
(185, 'Caminha americana', '', 'uploads/admin/20261021_005152_3c7f163a35.jpg', 143.00, 27, 'ambos', 'outros', 5, 0, 0, 0.00, 0, 1, '2026-10-20 21:51:52'),
(186, 'Caminha europa', '', 'uploads/admin/20261021_005238_7be13ffc2a.jpg', 157.00, 27, 'ambos', 'outros', 5, 0, 0, 0.00, 0, 1, '2026-10-20 21:52:38'),
(187, 'Caminha iglu', '', 'uploads/admin/20261021_005345_deb197e013.jpg', 168.00, 27, 'ambos', 'outros', 5, 0, 0, 0.00, 0, 1, '2026-10-20 21:53:45'),
(188, 'Caminha rubigold', '', 'uploads/admin/20261021_005459_2107cea158.jpg', 240.00, 27, 'ambos', 'outros', 5, 0, 2, 0.00, 0, 1, '2026-10-20 21:54:59'),
(189, 'Casinha', '', 'uploads/admin/20261021_005703_1ccb42e16b.png', 97.00, 43, 'ambos', 'outros', 5, 0, 0, 0.00, 0, 1, '2026-10-20 21:57:03'),
(190, 'Casinha', '', 'uploads/admin/20261021_005948_98c6c4d5fc.jpg', 110.00, 48, 'ambos', 'outros', 5, 0, 1, 5.00, 0, 1, '2026-10-20 21:59:48'),
(191, 'Casinha quadrada', '', 'uploads/admin/20261021_010224_18d70559c0.jpg', 125.00, 16, 'ambos', 'outros', 5, 0, 0, 0.00, 0, 1, '2026-10-20 22:02:24'),
(192, 'Comedouro formiga', '', 'uploads/admin/20261021_010401_b16ac87f65.jpg', 34.00, 30, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-20 22:04:01'),
(193, 'comedouro pesado', '', 'uploads/admin/20261021_010448_7c137020e3.jpg', 45.00, 30, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-20 22:04:48'),
(194, 'comedouro automático', '', 'uploads/admin/20261021_010537_3278fb1e8d.jpg', 75.00, 30, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-20 22:05:37'),
(195, 'Comedouro luxo duplo', '', 'uploads/admin/20261021_010646_df1f1c868b.png', 89.00, 16, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-20 22:06:46'),
(196, 'Ração filhote ovelha arroz', '', 'uploads/admin/20261021_010918_c80dcb0922.png', 95.00, 10, 'cachorro', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-20 22:09:18'),
(197, 'Ração pequena', '', 'uploads/admin/20261021_011027_c44b19e4fc.jpg', 129.00, 10, 'cachorro', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-20 22:10:27'),
(198, 'suporte para comedouro', '', 'uploads/admin/20261027_000745_bd6678fa5a.jpg', 45.00, 16, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-26 21:07:45'),
(199, 'Guilhotina', '', 'uploads/admin/20261027_001020_bf3c75d045.jpg', 87.00, 12, 'ambos', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-26 21:10:20'),
(200, 'Alicate de unha', '', 'uploads/admin/20261027_001116_4f468ebd42.jpg', 54.00, 12, 'ambos', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-26 21:11:16'),
(201, 'Escova dupla', '', 'uploads/admin/20261027_001153_f1895410c2.jpg', 49.00, 12, 'ambos', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-26 21:11:53'),
(202, 'Escova macia', '', 'uploads/admin/20261027_001216_86d2593029.jpg', 57.00, 49, 'ambos', 'outros', 9, 0, 1, 5.00, 0, 1, '2026-10-26 21:12:16'),
(203, 'Escova pino', '', 'uploads/admin/20261027_001323_f23964daa0.jpg', 49.99, 49, 'ambos', 'outros', 9, 0, 1, 5.00, 0, 1, '2026-10-26 21:13:23'),
(204, 'Escova luva', '', 'uploads/admin/20261027_001356_fdb0d396cc.jpg', 61.00, 49, 'ambos', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-26 21:13:56'),
(205, 'Rasqueteira', '', 'uploads/admin/20261027_001434_bbbcc5e5df.jpg', 75.99, 49, 'ambos', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-26 21:14:34'),
(206, 'Coleira fluor', '', 'uploads/admin/20261027_001614_c746e3b729.jpg', 26.00, 30, 'ambos', 'outros', 14, 0, 0, 0.00, 0, 1, '2026-10-26 21:16:14'),
(207, 'Coleira skipe', '', 'uploads/admin/20261027_001725_4e77deb8a3.jpg', 35.00, 30, 'ambos', 'outros', 14, 0, 0, 0.00, 0, 1, '2026-10-26 21:17:25'),
(208, 'Coleira ego comfort', '', 'uploads/admin/20261027_002007_3cfc1e191c.jpg', 54.00, 15, 'ambos', 'outros', 14, 0, 0, 0.00, 0, 1, '2026-10-26 21:20:07'),
(209, 'guia ego fluo', '', 'uploads/admin/20261027_002133_e4ae56fe12.jpg', 58.00, 15, 'ambos', 'outros', 10, 0, 0, 0.00, 0, 1, '2026-10-26 21:21:33'),
(210, 'Guia fluor', '', 'uploads/admin/20261027_002240_1fb1f8e63c.jpg', 45.00, 30, 'ambos', 'outros', 10, 0, 0, 0.00, 0, 1, '2026-10-26 21:22:40'),
(211, 'Guia com amortecedor', '', 'uploads/admin/20261027_002415_9f53d678e2.jpg', 75.00, 35, 'ambos', 'outros', 10, 0, 0, 0.00, 0, 1, '2026-10-26 21:24:15'),
(212, 'Perfume baby', '', 'uploads/admin/20261027_002555_62a796b3d9.jpg', 49.00, 22, 'cachorro', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-26 21:25:55'),
(213, 'Perfume frutas vermelhas', '', 'uploads/admin/20261027_002633_53fa616645.jpg', 58.00, 22, 'ambos', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-26 21:26:33'),
(214, 'Perfume maça verde', '', 'uploads/admin/20261027_003025_b5361d8b50.jpg', 42.99, 22, 'ambos', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-26 21:30:25'),
(215, 'Perfume vip', '', 'uploads/admin/20261027_003128_479c14e138.jpg', 67.99, 24, 'ambos', 'outros', 9, 0, 1, 5.00, 0, 1, '2026-10-26 21:31:28'),
(216, 'Bifinho', '', 'uploads/admin/20261027_003252_3c46a384ab.png', 5.99, 31, 'ambos', 'outros', 2, 0, 0, 0.00, 0, 1, '2026-10-26 21:32:52'),
(217, 'Biscoito', '', 'uploads/admin/20261027_003357_43eb234019.jpg', 12.00, 31, 'ambos', 'outros', 2, 0, 0, 0.00, 0, 1, '2026-10-26 21:33:57'),
(218, 'Bifinho', '', 'uploads/admin/20261027_003526_023347dd3d.jpg', 7.99, 30, 'ambos', 'outros', 2, 0, 0, 0.00, 0, 1, '2026-10-26 21:35:26'),
(219, 'Bifinho', '', 'uploads/admin/20261027_003611_0412da33d4.jpg', 14.00, 30, 'ambos', 'outros', 2, 0, 0, 0.00, 0, 1, '2026-10-26 21:36:11'),
(220, 'Churu', '', 'uploads/admin/20261027_003745_38f17efae2.jpg', 8.99, 50, 'ambos', 'outros', 2, 0, 0, 0.00, 0, 1, '2026-10-26 21:37:45'),
(221, 'Anti bola de pelo', '', 'uploads/admin/20261027_003925_9038c13f55.jpg', 14.99, 51, 'ambos', 'outros', 2, 0, 0, 0.00, 0, 1, '2026-10-26 21:39:25'),
(222, 'Crocante', '', 'uploads/admin/20261027_003946_1d09ef1b72.jpg', 17.00, 51, 'ambos', 'outros', 2, 0, 0, 0.00, 0, 1, '2026-10-26 21:39:46'),
(223, 'Ração carne', '', 'uploads/admin/20261027_004037_1e64aad8d4.jpg', 78.00, 31, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-26 21:40:37'),
(224, 'Ração carne', '', 'uploads/admin/20261027_004104_76f35ed9e8.jpg', 92.99, 31, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-26 21:41:04'),
(225, 'Ração carne', '', 'uploads/admin/20261027_004204_7c0c1a765b.jpg', 79.99, 19, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-26 21:42:04'),
(226, 'Ração carne filhote', '', 'uploads/admin/20261027_004231_ca9e1f0099.jpg', 79.99, 19, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-26 21:42:31'),
(227, 'Ração castrado', '', 'uploads/admin/20261027_004330_002a5c0a62.jpg', 89.99, 10, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-26 21:43:30'),
(228, 'Ração carne', '', 'uploads/admin/20261027_004439_96e2ed090f.jpg', 67.00, 28, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-26 21:44:39'),
(229, 'Ração castrado mix de carne', '', 'uploads/admin/20261027_004531_3e026af559.jpg', 78.00, 28, 'ambos', 'outros', 16, 0, 0, 0.00, 0, 1, '2026-10-26 21:45:31'),
(230, 'Cerenia', '', 'uploads/admin/20261027_004623_63865262de.png', 48.00, 36, 'ambos', 'outros', 8, 0, 0, 0.00, 0, 1, '2026-10-26 21:46:23'),
(231, 'trocoxil', '', 'uploads/admin/20261027_004721_cbb75ddf31.png', 51.99, 36, 'ambos', 'outros', 8, 0, 0, 0.00, 0, 1, '2026-10-26 21:47:21'),
(232, 'Apoguel', '', 'uploads/admin/20261027_004759_57cde27603.png', 57.99, 36, 'ambos', 'outros', 8, 0, 0, 0.00, 0, 1, '2026-10-26 21:47:59'),
(233, 'Actofend', '', 'uploads/admin/20261027_004832_995f53069c.png', 48.00, 36, 'ambos', 'outros', 8, 0, 0, 0.00, 0, 1, '2026-10-26 21:48:32'),
(234, 'Mibemax', '', 'uploads/admin/20261027_004953_5b3f52595b.png', 61.00, 52, 'ambos', 'outros', 8, 0, 0, 0.00, 0, 1, '2026-10-26 21:49:53'),
(235, 'Advocate', '', 'uploads/admin/20261027_005032_5b27954d7b.jpg', 75.00, 52, 'ambos', 'outros', 8, 0, 0, 0.00, 0, 1, '2026-10-26 21:50:32'),
(236, 'Surosolve', '', 'uploads/admin/20261027_005112_befe795b6a.png', 85.99, 52, 'ambos', 'outros', 8, 0, 0, 0.00, 0, 1, '2026-10-26 21:51:12'),
(237, 'coleira seresto', '', 'uploads/admin/20261027_005141_9fdeefa85a.png', 83.99, 52, 'ambos', 'outros', 8, 0, 0, 0.00, 0, 1, '2026-10-26 21:51:41'),
(238, 'Camiseta manga snoopy', '', 'uploads/admin/20261027_005312_69c931094f.jpg', 62.99, 37, 'cachorro', 'outros', 3, 0, 0, 0.00, 0, 1, '2026-10-26 21:53:12'),
(239, 'Capuz', '', 'uploads/admin/20261027_005351_a95c92e3cc.png', 73.99, 37, 'cachorro', 'outros', 3, 0, 0, 0.00, 0, 1, '2026-10-26 21:53:51'),
(240, 'Vestido', '', 'uploads/admin/20261027_005437_e131c3a070.jpg', 84.99, 37, 'cachorro', 'outros', 3, 0, 0, 0.00, 0, 1, '2026-10-26 21:54:37'),
(241, 'Sabonete citronela', '', 'uploads/admin/20261027_005549_2280f3af38.png', 39.99, 22, 'cachorro', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-26 21:55:49'),
(242, 'Sabonete coco', '', 'uploads/admin/20261027_005617_51b99ebc4a.png', 48.99, 22, 'ambos', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-26 21:56:17'),
(243, 'Sabonete natural', '', 'uploads/admin/20261027_005645_61a284c388.png', 38.99, 22, 'ambos', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-26 21:56:45'),
(244, 'Sabonete clareador', '', 'uploads/admin/20261027_005733_747f443f17.png', 52.00, 47, 'ambos', 'outros', 9, 0, 0, 0.00, 0, 1, '2026-10-26 21:57:33'),
(245, 'Sache', '', 'uploads/admin/20261027_005809_e33e42efb7.jpg', 7.99, 10, 'ambos', 'outros', 2, 0, 0, 0.00, 0, 1, '2026-10-26 21:58:09'),
(246, 'sache', '', 'uploads/admin/20261027_005841_3cf05d41fd.jpg', 8.99, 10, 'ambos', 'outros', 2, 0, 0, 0.00, 0, 1, '2026-10-26 21:58:41'),
(247, 'Sache', '', 'uploads/admin/20261027_005925_e8b8fdf061.png', 5.99, 28, 'ambos', 'outros', 2, 0, 0, 0.00, 0, 1, '2026-10-26 21:59:25'),
(248, 'Sache castrado', '', 'uploads/admin/20261027_010000_68ee6048aa.png', 9.99, 28, 'ambos', 'outros', 2, 0, 0, 0.00, 0, 1, '2026-10-26 22:00:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tudo_cachorro`
--

DROP TABLE IF EXISTS `tudo_cachorro`;
CREATE TABLE IF NOT EXISTS `tudo_cachorro` (
  `id_item` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagem` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_categoria_produto` int DEFAULT NULL,
  `ordem` int DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_item`),
  KEY `idx_tudo_cachorro_categoria` (`id_categoria_produto`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tudo_cachorro`
--

INSERT INTO `tudo_cachorro` (`id_item`, `nome`, `imagem`, `id_categoria_produto`, `ordem`, `ativo`, `data_cadastro`, `link`) VALUES
(11, 'Rações', 'uploads/admin/20261010_170903_25c5a9fb30.png', 16, 0, 1, '2026-10-10 14:09:03', NULL),
(12, 'Petiscos', 'uploads/admin/20261010_170946_f35960be41.png', 2, 0, 1, '2026-10-10 14:09:46', NULL),
(13, 'Higiene', 'uploads/admin/20261010_171051_e822d573c4.png', 9, 0, 1, '2026-10-10 14:10:51', NULL),
(14, 'Medicamentos', 'uploads/admin/20261010_171148_c491e3b6d4.png', 8, 0, 1, '2026-10-10 14:11:48', NULL),
(15, 'Brinquedos', 'uploads/admin/20261010_171230_850066f735.png', 11, 0, 1, '2026-10-10 14:12:30', NULL),
(16, 'coleiras', 'uploads/admin/20261010_171259_95e706782b.png', 4, 0, 1, '2026-10-10 14:12:59', NULL),
(17, 'Camas', 'uploads/admin/20261010_171406_b2eea25313.png', 5, 0, 1, '2026-10-10 14:14:06', NULL),
(18, 'Roupas', 'uploads/admin/20261010_171444_b910258565.png', 3, 0, 1, '2026-10-10 14:14:44', NULL),
(19, 'Transporte', 'uploads/admin/20261010_171540_af88a6d501.png', 10, 0, 1, '2026-10-10 14:15:40', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tudo_gato`
--

DROP TABLE IF EXISTS `tudo_gato`;
CREATE TABLE IF NOT EXISTS `tudo_gato` (
  `id_item` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagem` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_categoria_produto` int DEFAULT NULL,
  `ordem` int DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_item`),
  KEY `idx_tudo_gato_categoria` (`id_categoria_produto`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tudo_gato`
--

INSERT INTO `tudo_gato` (`id_item`, `nome`, `imagem`, `id_categoria_produto`, `ordem`, `ativo`, `data_cadastro`, `link`) VALUES
(7, 'Rações', 'uploads/admin/20261010_171656_ab65a684dc.png', 16, 0, 1, '2026-10-10 14:16:56', NULL),
(8, 'Petisco', 'uploads/admin/20261010_171717_619bef80e8.png', 2, 0, 1, '2026-10-10 14:17:17', NULL),
(9, 'Higiene', 'uploads/admin/20261010_171746_31856b9016.png', 9, 0, 1, '2026-10-10 14:17:46', NULL),
(10, 'Medicamentos', 'uploads/admin/20261010_171849_f22bd05778.png', 8, 0, 1, '2026-10-10 14:18:49', NULL),
(11, 'Brinquedos', 'uploads/admin/20261010_171921_2d5c901094.png', 11, 0, 1, '2026-10-10 14:19:21', NULL),
(12, 'Coleiras', 'uploads/admin/20261010_172017_dce3251ced.png', 15, 0, 1, '2026-10-10 14:20:17', NULL),
(13, 'Camas', 'uploads/admin/20261010_172041_5acb603ac3.png', 5, 0, 1, '2026-10-10 14:20:41', NULL),
(14, 'Roupas', 'uploads/admin/20261010_172113_dbe3eae7f3.png', 14, 0, 1, '2026-10-10 14:21:13', NULL),
(15, 'Transporte', 'uploads/admin/20261010_172144_056e96d67c.png', 10, 0, 1, '2026-10-10 14:21:44', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uk_usuarios_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nome`, `email`, `senha_hash`, `ativo`, `data_cadastro`, `ultimo_login`) VALUES
(1, 'mariane', 'mariane@gmail.com', '$2y$10$4jorQM.Mcy3v2j6Csp0VIe3zAt/1Vk71zF7nHOv6fP81yABSFLwWu', 1, '2026-09-17 12:14:53', '2026-10-08 20:08:53'),
(2, 'Beatriz', 'beatriz.saantoos01@gmail.com', '$2y$10$hI73fYZ5S6fZAEwPccmAV.b7tDWg75CBfjdkA2NJDcPxJmO2U8ary', 1, '2026-10-23 19:29:54', '2026-10-23 16:31:14');

-- --------------------------------------------------------

--
-- Estrutura para tabela `variacoes_produto`
--

DROP TABLE IF EXISTS `variacoes_produto`;
CREATE TABLE IF NOT EXISTS `variacoes_produto` (
  `id_variacao` int NOT NULL AUTO_INCREMENT,
  `id_produto` int NOT NULL,
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `imagem` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `promocao` tinyint(1) DEFAULT NULL,
  `desconto_percentual` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id_variacao`),
  KEY `id_produto` (`id_produto`),
  KEY `idx_variacoes_promocao` (`id_produto`,`promocao`)
) ENGINE=InnoDB AUTO_INCREMENT=392 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `variacoes_produto`
--

INSERT INTO `variacoes_produto` (`id_variacao`, `id_produto`, `nome`, `preco`, `imagem`, `promocao`, `desconto_percentual`) VALUES
(17, 13, '4kg', 13.99, 'uploads/admin/20261010_173301_1ea7634009.jpg', 0, 0.00),
(19, 14, '4kg', 19.99, 'uploads/admin/20261010_174333_14e6a84b8f.jpg', 0, 0.00),
(20, 15, '4kg', 22.99, 'uploads/admin/20261010_174500_7465bfe39a.jpg', 0, 0.00),
(21, 16, '3kg', 25.00, 'uploads/admin/20261010_174546_3cb2f7f190.jpg', 0, 0.00),
(22, 17, '4kg', 14.99, 'uploads/admin/20261010_174931_2187f267b3.jpg', 0, 0.00),
(23, 18, '4kg', 13.99, 'uploads/admin/20261010_175146_5669e05fb2.jpg', 0, 0.00),
(24, 19, '3kg', 29.99, 'uploads/admin/20261010_183341_603c18969a.jpg', 1, 5.00),
(25, 20, '3kg', 25.00, 'uploads/admin/20261010_183545_c6f3443f95.jpg', 0, 0.00),
(26, 21, '3kg', 15.00, 'uploads/admin/20261010_183716_9e6c07c9b5.jpg', 0, 0.00),
(27, 21, '6kg', 35.99, 'uploads/admin/20261010_183837_a1bc5d73ee.jpg', 1, 10.00),
(28, 22, '4kg', 14.00, 'uploads/admin/20261010_184102_96300be28b.jpg', 0, 0.00),
(29, 23, '4kg', 45.99, 'uploads/admin/20261010_184159_6403391817.png', 0, 0.00),
(30, 24, '4kg', 52.00, 'uploads/admin/20261010_184257_58dc64365c.png', 0, 0.00),
(31, 25, '4kg', 49.99, 'uploads/admin/20261010_184631_5a3c13c607.jpg', 0, 0.00),
(32, 26, '4kg', 53.99, 'uploads/admin/20261010_184735_c53d6b0b34.jpg', 1, 10.00),
(33, 27, 'Azul', 89.00, 'uploads/admin/20261010_185059_250218117b.jpg', 1, 5.00),
(35, 29, 'Azul', 72.99, 'uploads/admin/20261010_185458_6b5de1839a.jpg', 0, 0.00),
(37, 31, 'Rosa', 83.99, 'uploads/admin/20261010_185618_386145acd2.png', 0, 0.00),
(38, 32, 'Azul', 73.99, 'uploads/admin/20261010_185711_6d4269b632.jpg', 1, 10.00),
(40, 34, 'Azul', 105.00, 'uploads/admin/20261010_185853_8ed0f99c63.jpg', 0, 0.00),
(41, 27, 'Rosa', 89.00, 'uploads/admin/20261010_190225_0b12cadeef.jpg', 1, 5.00),
(42, 31, 'Azul', 83.99, 'uploads/admin/20261010_190310_d235e72c06.png', 0, 0.00),
(43, 32, 'Vermelho', 73.99, 'uploads/admin/20261010_190353_2a9ca01ef1.jpg', 1, 15.00),
(44, 35, 'Cinza', 95.00, 'uploads/admin/20261010_190500_8093eb001a.jpg', 0, 0.00),
(45, 35, 'Marrom', 95.00, 'uploads/admin/20261010_190554_629dc483a3.jpg', 1, 5.00),
(46, 35, 'Preto', 95.00, 'uploads/admin/20261010_190619_37d3d52f9c.jpg', 1, 5.00),
(47, 36, 'Verde', 135.99, 'uploads/admin/20261010_191042_da29635dbe.jpg', 0, 0.00),
(48, 37, 'Beje', 99.99, 'uploads/admin/20261010_191333_977946db5d.jpg', 0, 0.00),
(49, 37, 'Cinza', 99.99, 'uploads/admin/20261010_191411_e76f7b5644.jpg', 1, 5.00),
(50, 38, 'Cinza', 125.00, 'uploads/admin/20261010_191449_06ef149aca.jpg', 0, 0.00),
(51, 39, 'Beje', 142.99, 'uploads/admin/20261010_191712_aeebafe055.jpg', 0, 0.00),
(52, 39, 'Cinza', 142.99, 'uploads/admin/20261010_191750_3992ca5d23.jpg', 0, 0.00),
(53, 39, 'Marrom', 142.99, 'uploads/admin/20261010_191820_ddbef5f341.jpg', 0, 0.00),
(54, 39, 'Preto', 142.99, 'uploads/admin/20261010_191842_3143515cc6.jpg', 1, 5.00),
(55, 40, 'Beje', 205.00, 'uploads/admin/20261010_191929_e1fa29bcca.jpg', 1, 5.00),
(56, 40, 'Marrom', 205.00, 'uploads/admin/20261010_192032_1ba121311b.jpg', 1, 5.00),
(57, 40, 'Preto', 205.00, 'uploads/admin/20261010_192132_c56b994df4.jpg', 1, 5.00),
(58, 41, 'Azul', 110.00, 'uploads/admin/20261010_220026_59c8e5c318.jpg', 1, 5.00),
(59, 41, 'Beje', 110.00, 'uploads/admin/20261010_220058_30fdb27420.jpg', 1, 5.00),
(60, 41, 'Rosa', 110.00, 'uploads/admin/20261010_220111_0db9529d89.jpg', 1, 5.00),
(61, 42, 'Marrom', 105.00, 'uploads/admin/20261010_220246_18f892249c.jpg', 1, 5.00),
(62, 42, 'Marrom', 105.00, 'uploads/admin/20261010_220308_ea08077cd9.jpg', 1, 5.00),
(63, 43, 'Azul', 96.99, 'uploads/admin/20261010_220355_6035db1a39.jpg', 0, 0.00),
(64, 43, 'Rosa', 96.99, 'uploads/admin/20261010_220418_3df2b4aa2b.jpg', 0, 0.00),
(65, 43, 'Cinza', 96.99, 'uploads/admin/20261010_220523_61f331564e.jpg', 0, 0.00),
(66, 43, 'Preto', 96.99, 'uploads/admin/20261010_220533_82805f8ded.jpg', 0, 0.00),
(67, 44, 'Preto', 99.99, 'uploads/admin/20261010_220606_a19f17c965.jpg', 0, 0.00),
(68, 45, 'Cinza', 89.99, 'uploads/admin/20261010_220649_ef19c7912a.jpg', 0, 0.00),
(69, 45, 'Preto', 89.99, 'uploads/admin/20261010_220724_17cf94752c.jpg', 0, 0.00),
(70, 46, 'Branco', 355.99, 'uploads/admin/20261010_220836_4a24318658.jpg', 1, 5.00),
(71, 47, 'Marrom', 240.00, 'uploads/admin/20261010_221007_64017fb463.jpg', 0, 0.00),
(72, 47, 'Beje', 240.00, 'uploads/admin/20261010_221039_91006119fb.jpg', 0, 0.00),
(73, 48, 'Marrom', 610.00, 'uploads/admin/20261010_221217_11bf23be29.png', 0, 0.00),
(74, 49, 'Marrom', 712.99, 'uploads/admin/20261010_221317_c93b299dd2.png', 0, 0.00),
(75, 49, 'Preto', 712.99, 'uploads/admin/20261010_221356_dd87f51606.png', 1, 10.00),
(76, 50, 'Beje', 755.00, 'uploads/admin/20261010_221451_749eca53b2.png', 0, 0.00),
(77, 51, 'Azul', 199.99, 'uploads/admin/20261010_221535_b0092653c9.png', 0, 0.00),
(78, 52, 'Azul', 154.99, 'uploads/admin/20261010_221624_f9e43395a2.png', 0, 0.00),
(79, 52, 'Rosa', 154.99, 'uploads/admin/20261010_221655_a560447a2f.png', 0, 0.00),
(80, 52, 'Marrom', 154.99, 'uploads/admin/20261010_221715_b6904cb368.png', 0, 0.00),
(81, 53, 'Marrom', 215.00, 'uploads/admin/20261010_221819_e2a51c0238.png', 0, 0.00),
(82, 54, 'Marrom', 854.99, 'uploads/admin/20261010_221937_027759b525.png', 0, 0.00),
(83, 55, 'Marrom', 189.99, 'uploads/admin/20261010_222039_63c7f71370.png', 0, 0.00),
(84, 56, 'Marrom', 89.00, 'uploads/admin/20261010_222135_dd3cb4b075.jpg', 0, 0.00),
(85, 57, 'Marrom', 189.99, 'uploads/admin/20261010_222400_73bff29409.png', 0, 0.00),
(86, 58, 'Marrom', 612.00, 'uploads/admin/20261010_222502_b1f24b09dc.png', 0, 0.00),
(87, 59, 'Marrom', 635.00, 'uploads/admin/20261010_222613_c557889a16.png', 0, 0.00),
(88, 60, 'Marrom', 955.00, 'uploads/admin/20261010_222707_0f30d071d7.jpg', 1, 5.00),
(89, 61, 'Marrom', 578.00, 'uploads/admin/20261010_222805_87eb9ee86b.jpg', 0, 0.00),
(90, 62, 'Amarelo', 99.99, 'uploads/admin/20261010_223154_a650799215.png', 1, 5.00),
(91, 62, 'Azul', 99.99, 'uploads/admin/20261010_223233_b6ebfbd780.png', 1, 5.00),
(92, 62, 'Laranja', 99.99, 'uploads/admin/20261010_223245_7c4205eb2d.png', 1, 5.00),
(93, 62, 'Preto', 99.99, 'uploads/admin/20261010_223254_30732d1b78.png', 1, 5.00),
(94, 62, 'Rosa', 99.99, 'uploads/admin/20261010_223304_d5c0faab25.png', 1, 5.00),
(95, 62, 'Roxo', 99.99, 'uploads/admin/20261010_223319_5fc800ead0.png', 1, 5.00),
(96, 62, 'Verde', 99.99, 'uploads/admin/20261010_223329_0fa7635b10.png', 1, 5.00),
(97, 62, 'Vermelho', 99.99, 'uploads/admin/20261010_223343_ee5433c79c.png', 1, 5.00),
(98, 63, 'Azul', 109.00, 'uploads/admin/20261010_223429_088b655255.png', 0, 0.00),
(99, 63, 'Preto', 109.00, 'uploads/admin/20261010_223449_e53bf331aa.png', 0, 0.00),
(100, 63, 'Rosa', 109.00, 'uploads/admin/20261010_223518_bf0420ee9a.png', 0, 0.00),
(101, 64, 'Azul', 87.99, 'uploads/admin/20261010_223609_781bf3f8f5.png', 1, 5.00),
(102, 64, 'Rosa', 87.99, 'uploads/admin/20261010_223700_88e31fda6d.png', 1, 5.00),
(103, 64, 'Vermelho', 87.99, 'uploads/admin/20261010_223717_2a559d99db.png', 1, 5.00),
(104, 65, 'Azul', 38.99, 'uploads/admin/20261010_223830_7e27444d42.png', 0, 0.00),
(105, 65, 'Rosa', 38.99, 'uploads/admin/20261010_223852_86d5b8fc59.png', 0, 0.00),
(107, 66, 'Azul', 45.00, 'uploads/admin/20261010_230918_aa52d37242.jpg', 1, 5.00),
(108, 66, 'Rosa', 45.00, 'uploads/admin/20261010_230942_3e4417b797.jpg', 1, 5.00),
(109, 66, 'Vermelho', 45.00, 'uploads/admin/20261010_231002_31d0b1c402.jpg', 1, 5.00),
(110, 67, 'Verde', 89.00, 'uploads/admin/20261010_231123_4937604da4.jpg', 0, 0.00),
(111, 68, 'Beje', 69.99, 'uploads/admin/20261010_231304_31348f2925.png', 0, 0.00),
(112, 68, 'Roxo', 69.99, 'uploads/admin/20261010_231331_5a28d4b155.jpg', 0, 0.00),
(113, 69, 'Branco', 53.99, 'uploads/admin/20261010_231610_6ba1dd70f3.png', 1, 10.00),
(114, 69, 'Cinza', 53.99, 'uploads/admin/20261010_231644_8c4f0a1b33.png', 1, 10.00),
(115, 69, 'Marrom', 53.99, 'uploads/admin/20261010_231708_448f2fffc0.png', 1, 10.00),
(116, 69, 'Verde', 53.99, 'uploads/admin/20261010_231718_f8b515d854.png', 1, 10.00),
(117, 70, 'Verde', 157.99, 'uploads/admin/20261010_231928_f647c5ee7b.jpg', 0, 0.00),
(118, 70, 'Branco', 157.99, 'uploads/admin/20261010_231950_19a60e4e23.jpg', 0, 0.00),
(119, 70, 'Preto', 157.99, 'uploads/admin/20261010_232003_a18fef81b6.png', 0, 0.00),
(120, 70, 'Rosa', 157.99, 'uploads/admin/20261010_232013_52189e6870.png', 0, 0.00),
(121, 70, 'Rose', 157.99, 'uploads/admin/20261010_232023_59adc42ffa.jpg', 0, 0.00),
(122, 70, 'Vermelho', 157.99, 'uploads/admin/20261010_232031_e9d8027056.png', 0, 0.00),
(123, 71, 'Azul', 62.00, 'uploads/admin/20261010_232208_1a4095f75d.png', 0, 0.00),
(124, 71, 'Branco', 62.00, 'uploads/admin/20261010_232242_93a22ba864.png', 0, 0.00),
(125, 71, 'Rosa', 62.00, 'uploads/admin/20261010_232253_c2143c8395.png', 0, 0.00),
(126, 72, 'Azul', 116.00, 'uploads/admin/20261010_232352_cc784fe863.png', 1, 10.00),
(127, 72, 'Branco', 116.00, 'uploads/admin/20261010_232431_6c0392737a.png', 1, 10.00),
(128, 72, 'Preto', 116.00, 'uploads/admin/20261010_232441_6e93e32260.png', 1, 10.00),
(129, 72, 'Rosa', 116.00, 'uploads/admin/20261010_232453_b3cb6f7e49.png', 1, 10.00),
(130, 72, 'Vermelho', 116.00, 'uploads/admin/20261010_232502_4be49189b8.png', 1, 10.00),
(131, 73, 'Azul', 83.99, 'uploads/admin/20261010_232546_f7daa81ec8.jpg', 0, 0.00),
(132, 73, 'Rosa', 83.99, 'uploads/admin/20261010_232607_e83514c77c.jpg', 0, 0.00),
(133, 73, 'Roxo', 83.99, 'uploads/admin/20261010_232618_03c48a7264.jpg', 0, 0.00),
(134, 74, 'multicolor', 26.00, 'uploads/admin/20261010_232817_1b2a50ec9d.png', 0, 0.00),
(135, 75, 'multicolor', 54.00, 'uploads/admin/20261010_232949_d4d6e3f21e.png', 0, 0.00),
(136, 76, 'Verde', 14.99, 'uploads/admin/20261010_233048_cdf5ccd7c0.png', 0, 0.00),
(137, 77, 'Azul', 93.00, 'uploads/admin/20261013_141055_22ce214088.png', 0, 0.00),
(138, 78, 'Branco', 54.99, 'uploads/admin/20261013_141136_c17eb99aa9.webp', 0, 0.00),
(139, 79, 'multicolor', 65.00, 'uploads/admin/20261013_141216_5794e611c4.png', 0, 0.00),
(140, 80, 'Amarela', 98.00, 'uploads/admin/20261013_141315_18babca7e9.webp', 0, 0.00),
(141, 81, 'multicolor', 112.00, 'uploads/admin/20261013_141416_9753d1c10a.jpg', 0, 0.00),
(142, 82, 'Marrom', 57.99, 'uploads/admin/20261013_141459_4fde5745ed.jpg', 1, 5.00),
(143, 83, 'Rosa', 56.00, 'uploads/admin/20261013_141621_381dcc74e1.png', 0, 0.00),
(144, 84, 'Verde', 53.99, 'uploads/admin/20261013_141653_c5f6f75a11.png', 0, 0.00),
(145, 85, 'Azul', 56.00, 'uploads/admin/20261013_141737_f9d4669099.png', 0, 0.00),
(146, 86, 'Amarelo', 78.00, 'uploads/admin/20261013_141811_0fb6f9a589.png', 0, 0.00),
(147, 87, 'Marrom', 124.00, 'uploads/admin/20261013_141909_9c0ed53f7d.png', 0, 0.00),
(148, 88, 'Verde', 48.00, 'uploads/admin/20261013_142005_619113dac7.png', 0, 0.00),
(149, 89, 'Padrão', 79.99, 'uploads/admin/20261013_142052_1293e0bdb0.png', 0, 0.00),
(150, 90, 'Multicolor', 99.99, 'uploads/admin/20261013_142141_ec465523e3.webp', 0, 0.00),
(151, 91, 'Marrom', 182.00, 'uploads/admin/20261013_142246_8a8985b5df.png', 0, 0.00),
(152, 92, 'Verde', 63.99, 'uploads/admin/20261013_142321_eb8186639b.png', 0, 0.00),
(153, 93, 'Azul', 74.00, 'uploads/admin/20261013_142445_299c31cb40.png', 1, 5.00),
(154, 93, 'Rosa', 74.00, 'uploads/admin/20261013_142541_db03fd49c7.png', 1, 5.00),
(155, 94, 'Azul', 62.00, 'uploads/admin/20261013_142615_79cf73dabe.png', 1, 5.00),
(156, 94, 'Rosa', 62.00, 'uploads/admin/20261013_142634_e8ac12b392.png', 1, 5.00),
(157, 94, 'Vermelho', 62.00, 'uploads/admin/20261013_142643_030f698144.png', 1, 5.00),
(158, 95, 'Amarela', 27.00, 'uploads/admin/20261013_142718_4d394ab9ba.png', 0, 0.00),
(159, 95, 'Rosa', 27.00, 'uploads/admin/20261013_142740_49193dbeac.png', 0, 0.00),
(160, 95, 'Vermelho', 27.00, 'uploads/admin/20261013_142753_7514809338.png', 0, 0.00),
(161, 96, 'Marrom', 42.00, 'uploads/admin/20261013_142820_c010b9bbb8.webp', 0, 0.00),
(162, 96, 'Rosa', 42.00, 'uploads/admin/20261013_142850_45bf653c55.jpg', 0, 0.00),
(163, 96, 'Azul', 42.00, 'uploads/admin/20261013_142858_8217d5710b.png', 0, 0.00),
(164, 97, 'Azul', 12.00, 'uploads/admin/20261013_142921_dedeb3502d.png', 0, 0.00),
(165, 97, 'Rosa', 12.00, 'uploads/admin/20261013_142940_dd9eac5656.png', 0, 0.00),
(166, 97, 'Vermelho', 12.00, 'uploads/admin/20261013_142952_b56631d943.png', 0, 0.00),
(167, 98, 'Azul', 46.00, 'uploads/admin/20261013_143020_2b093d4e34.png', 0, 0.00),
(168, 98, 'Rosa', 46.00, 'uploads/admin/20261013_143105_ef6cd8e045.png', 0, 0.00),
(169, 98, 'Vermelho', 46.00, 'uploads/admin/20261013_143116_33f6d464f2.png', 0, 0.00),
(170, 99, 'Azul', 53.99, 'uploads/admin/20261013_143203_fd62763e5c.png', 1, 10.00),
(171, 99, 'Rosa', 53.99, 'uploads/admin/20261013_143221_f1e3a8a690.png', 1, 10.00),
(172, 99, 'Verde', 53.99, 'uploads/admin/20261013_143232_a6f18295dd.png', 1, 10.00),
(173, 99, 'Vermelho', 53.99, 'uploads/admin/20261013_143242_3311826f15.png', 1, 10.00),
(174, 100, 'Azul', 34.99, 'uploads/admin/20261013_143315_2ab4285343.png', 0, 0.00),
(175, 100, 'Rosa', 34.99, 'uploads/admin/20261013_143335_528ccec316.png', 0, 0.00),
(176, 101, 'Preto', 14.99, 'uploads/admin/20261013_143739_9cf68d94c3.png', 0, 0.00),
(177, 102, 'Vermelho', 29.99, 'uploads/admin/20261013_143813_67d1991d84.png', 0, 0.00),
(178, 103, 'Vermelho', 38.00, 'uploads/admin/20261013_143845_c24fc54614.png', 0, 0.00),
(179, 104, 'Beje', 57.00, 'uploads/admin/20261013_223839_c8cd43eefe.png', 0, 0.00),
(180, 105, 'Azul', 67.99, 'uploads/admin/20261013_223924_f53d440c01.png', 0, 0.00),
(181, 106, 'Cinza', 72.00, 'uploads/admin/20261013_224201_e7d1004a4a.webp', 0, 0.00),
(182, 107, 'Marrom', 75.00, 'uploads/admin/20261013_224252_954157a110.png', 0, 0.00),
(183, 108, 'Roxo', 72.00, 'uploads/admin/20261013_224418_45ee1e9d3d.png', 0, 0.00),
(184, 109, 'Verde', 72.00, 'uploads/admin/20261013_224518_26e3473d5f.png', 0, 0.00),
(185, 110, 'Marrom', 72.00, 'uploads/admin/20261013_224607_593820afd8.png', 0, 0.00),
(186, 111, 'Cinza', 56.00, 'uploads/admin/20261013_224712_848653ce56.png', 0, 0.00),
(187, 112, 'Vermelho', 32.00, 'uploads/admin/20261013_224843_309cc16c7d.png', 0, 0.00),
(188, 113, 'Vermelho e Preto', 40.00, 'uploads/admin/20261013_224920_6f74891b0e.png', 0, 0.00),
(189, 114, 'Cinza', 50.00, 'uploads/admin/20261013_225007_8779219a50.png', 0, 0.00),
(191, 115, 'Rosa', 52.00, 'uploads/admin/20261013_225106_cdcc4cfad5.png', 0, 0.00),
(192, 116, 'Amarelo', 30.00, 'uploads/admin/20261013_225207_5ceb68c9c8.png', 0, 0.00),
(193, 117, 'Verde', 42.00, 'uploads/admin/20261013_225243_eb7ec5970f.png', 0, 0.00),
(194, 118, 'Rosa', 43.00, 'uploads/admin/20261013_225319_1f2a48ee4a.png', 0, 0.00),
(195, 119, 'Vermelho', 53.00, 'uploads/admin/20261013_225604_f8bfd108e9.png', 0, 0.00),
(196, 120, 'Azul', 68.00, 'uploads/admin/20261013_225653_e2c7408da1.png', 0, 0.00),
(197, 121, 'Azul', 60.00, 'uploads/admin/20261013_225731_29400fa090.png', 0, 0.00),
(198, 122, 'Vermelho', 49.00, 'uploads/admin/20261013_225804_661925871c.png', 0, 0.00),
(199, 123, 'Roxo', 50.00, 'uploads/admin/20261013_225839_afe1cacdba.png', 0, 0.00),
(200, 124, 'Preto', 48.00, 'uploads/admin/20261013_225924_3d5c275cb7.png', 0, 0.00),
(201, 125, 'Preto', 45.00, 'uploads/admin/20261013_230002_69aa3f96b8.png', 0, 0.00),
(202, 126, 'Vermelho', 48.00, 'uploads/admin/20261013_230032_7e6b896f07.png', 0, 0.00),
(203, 127, 'Vermelho e Azul', 44.00, 'uploads/admin/20261013_230108_5809ce7a66.png', 0, 0.00),
(204, 128, 'Roxo', 48.00, 'uploads/admin/20261013_230139_0ff8b065a6.png', 0, 0.00),
(205, 129, 'Multicor', 50.00, 'uploads/admin/20261013_230207_7f7445ae4b.png', 0, 0.00),
(206, 130, 'Roxo', 89.00, 'uploads/admin/20261013_232854_da247b9906.png', 1, 5.00),
(207, 131, 'Multicolor', 83.99, 'uploads/admin/20261013_232937_e8f9df2b98.png', 0, 0.00),
(208, 132, 'Rosa', 46.00, 'uploads/admin/20261013_233041_7cb69658f3.png', 0, 0.00),
(209, 133, 'Padrão', 28.00, 'uploads/admin/20261013_233214_746e97113c.jpg', 0, 0.00),
(210, 134, 'Amarelo', 39.99, 'uploads/admin/20261014_003019_b8ec83aafb.jpg', 0, 0.00),
(211, 134, 'Marrom', 39.99, 'uploads/admin/20261014_003105_be7e0c83f5.jpg', 0, 0.00),
(212, 135, 'Azul', 25.00, 'uploads/admin/20261014_003147_2f940d0b8a.jpg', 0, 0.00),
(213, 136, 'Padrão', 38.00, 'uploads/admin/20261014_003221_be1e106cff.jpg', 0, 0.00),
(214, 137, 'Padrão', 41.00, 'uploads/admin/20261014_003718_b14b13d491.jpg', 1, 5.00),
(215, 138, 'Padrão', 37.99, 'uploads/admin/20261014_003752_b3b9a2c499.jpg', 0, 0.00),
(216, 139, 'Multicolor verde', 43.00, 'uploads/admin/20261014_003834_4bcffc82d3.jpg', 0, 0.00),
(217, 140, 'Multicolor vermelho', 38.00, 'uploads/admin/20261014_003915_a8b152bacf.jpg', 0, 0.00),
(218, 141, 'Branco', 47.00, 'uploads/admin/20261014_004046_8315b90a49.jpg', 1, 5.00),
(219, 142, 'Padrão', 35.00, 'uploads/admin/20261014_004140_0c7e005717.jpg', 0, 0.00),
(220, 143, 'Padrão', 36.00, 'uploads/admin/20261014_004719_a9ce100107.jpg', 0, 0.00),
(221, 144, 'Padrão', 31.00, 'uploads/admin/20261014_004746_5d6a8db899.jpg', 0, 0.00),
(222, 145, 'Padrão', 38.00, 'uploads/admin/20261014_004832_549476b532.jpg', 0, 0.00),
(223, 146, 'Padrão', 36.00, 'uploads/admin/20261014_004859_56e175adef.jpg', 0, 0.00),
(224, 147, 'Padrão', 43.00, 'uploads/admin/20261014_004949_d0ae7dc7fe.jpg', 0, 0.00),
(225, 148, 'Padrão', 48.00, 'uploads/admin/20261014_005019_e991065d17.jpg', 0, 0.00),
(226, 149, 'Vermelho', 34.00, 'uploads/admin/20261014_005100_85b77eda60.jpg', 0, 0.00),
(227, 150, 'Vermelho', 42.00, 'uploads/admin/20261014_005136_4cd5fc1914.jpg', 0, 0.00),
(228, 151, 'Padrão', 44.00, 'uploads/admin/20261014_005241_849af0f046.jpg', 0, 0.00),
(229, 152, 'Padrão', 39.00, 'uploads/admin/20261014_005313_b1b23b43d8.jpg', 1, 5.00),
(230, 153, 'Laranja', 53.00, 'uploads/admin/20261014_005355_ee7ceba10c.jpg', 0, 0.00),
(231, 153, 'Rosa', 53.00, 'uploads/admin/20261014_005420_1053348db1.jpg', 0, 0.00),
(232, 154, 'Padrão', 37.00, 'uploads/admin/20261014_005457_cc15abfbf3.jpg', 0, 0.00),
(233, 155, 'Azul', 46.00, 'uploads/admin/20261014_005534_9fa42e91fe.jpg', 0, 0.00),
(234, 156, 'Azul', 41.00, 'uploads/admin/20261014_005619_67d427c66c.jpg', 0, 0.00),
(235, 157, 'Verde', 32.00, 'uploads/admin/20261014_005658_1b6e6520b7.jpg', 0, 0.00),
(236, 158, 'Multicolor', 38.00, 'uploads/admin/20261014_005908_61d560c9f0.png', 0, 0.00),
(237, 159, 'Padrão', 86.00, 'uploads/admin/20261014_005958_eaf8d733a2.png', 0, 0.00),
(238, 160, 'Verde', 94.00, 'uploads/admin/20261014_010030_8f64ea7f43.png', 0, 0.00),
(239, 161, 'Laranja', 31.00, 'uploads/admin/20261014_010115_16f8fd3afb.png', 0, 0.00),
(240, 162, 'Padrão', 48.99, 'uploads/admin/20261014_010246_b4c691ae6e.jpg', 0, 0.00),
(241, 163, 'Padrão', 53.00, 'uploads/admin/20261014_010328_ed76c7e351.png', 1, 5.00),
(242, 164, 'Padrão', 42.99, 'uploads/admin/20261014_010417_2b81cc5887.png', 0, 0.00),
(243, 165, 'Padrão', 48.00, 'uploads/admin/20261014_010447_45a22bb58a.png', 1, 5.00),
(244, 166, 'Padrão', 35.00, 'uploads/admin/20261014_010524_15c6219fd5.png', 0, 0.00),
(245, 167, 'Azul', 48.00, 'uploads/admin/20261014_010558_acb7ecf31f.jpg', 1, 10.00),
(246, 168, 'Padrão', 38.00, 'uploads/admin/20261014_010651_18a58020dc.png', 0, 0.00),
(247, 169, 'Roxo', 75.00, 'uploads/admin/20261014_010727_5aa5aa8436.png', 0, 0.00),
(248, 170, 'Azul', 79.99, 'uploads/admin/20261014_010804_6f75ceffe6.png', 0, 0.00),
(249, 170, 'Laranja', 79.99, 'uploads/admin/20261014_010836_31d2316887.webp', 0, 0.00),
(250, 171, 'Azul', 18.00, 'uploads/admin/20261014_011004_f3deb6cad6.png', 0, 0.00),
(251, 172, 'Padrão', 41.99, 'uploads/admin/20261014_011046_8d22df463d.png', 1, 5.00),
(252, 173, 'Padrão', 47.99, 'uploads/admin/20261014_011134_43cd4c73de.png', 0, 0.00),
(253, 174, 'Padrão', 49.99, 'uploads/admin/20261014_011226_a13260b9b1.png', 1, 20.00),
(254, 175, 'Padrão', 34.99, 'uploads/admin/20261014_011416_70fb36fe8a.webp', 0, 0.00),
(255, 176, 'Amarela', 22.00, 'uploads/admin/20261014_011530_e48821f023.png', 0, 0.00),
(256, 176, 'Laranja', 22.00, 'uploads/admin/20261014_011553_7303ca33a7.png', 0, 0.00),
(257, 176, 'Roxo', 22.00, 'uploads/admin/20261014_011636_71263599be.png', 0, 0.00),
(258, 176, 'Verde', 22.00, 'uploads/admin/20261014_011647_7a0420968f.png', 0, 0.00),
(259, 177, 'Azul', 43.99, 'uploads/admin/20261014_011845_b98da2cc0c.png', 0, 0.00),
(260, 177, 'Preta', 43.99, 'uploads/admin/20261014_011903_e01b2d19fe.png', 0, 0.00),
(261, 177, 'Rosa', 43.99, 'uploads/admin/20261014_011919_9ddf691d57.png', 0, 0.00),
(262, 178, 'Azul', 47.00, 'uploads/admin/20261014_011957_da25339d75.png', 0, 0.00),
(263, 178, 'Rosa', 47.00, 'uploads/admin/20261014_012015_e74b42f0ea.png', 0, 0.00),
(264, 179, 'Azul', 51.00, 'uploads/admin/20261014_012052_aff4771d04.png', 0, 0.00),
(265, 179, 'Beje', 51.00, 'uploads/admin/20261014_012121_2feb39d1ba.png', 0, 0.00),
(266, 179, 'Preta', 51.00, 'uploads/admin/20261014_012140_f0c087b637.png', 0, 0.00),
(267, 179, 'Rosa', 51.00, 'uploads/admin/20261014_012156_72a8cedf3b.png', 0, 0.00),
(268, 180, 'Azul', 52.99, 'uploads/admin/20261014_012234_04971313a2.png', 0, 0.00),
(269, 180, 'Beje', 52.99, 'uploads/admin/20261014_012256_abb91c3b82.png', 0, 0.00),
(270, 180, 'Laranja', 52.99, 'uploads/admin/20261014_012308_1e93eae401.png', 0, 0.00),
(271, 180, 'Preta', 52.99, 'uploads/admin/20261014_012335_7a4ccede21.png', 0, 0.00),
(272, 180, 'Rosa', 52.99, 'uploads/admin/20261014_012348_8b28071603.png', 0, 0.00),
(273, 180, 'Vermelha', 52.99, 'uploads/admin/20261014_012401_10d819cada.png', 0, 0.00),
(274, 181, 'Preta', 54.99, 'uploads/admin/20261014_012517_def10d2cd6.png', 1, 5.00),
(275, 181, 'Rosa', 54.99, 'uploads/admin/20261014_012540_cb63ee67cc.png', 1, 5.00),
(276, 181, 'Vermelha', 54.99, 'uploads/admin/20261014_012551_9168547b85.png', 1, 5.00),
(277, 182, 'Laranja', 75.00, 'uploads/admin/20261021_004602_6f3ac28b5f.png', 0, 0.00),
(278, 182, 'Rosa', 75.00, 'uploads/admin/20261021_004632_93dbebfda6.png', 0, 0.00),
(279, 182, 'Verde', 75.00, 'uploads/admin/20261021_004646_cf14f6be71.png', 0, 0.00),
(280, 183, 'Azul', 88.00, 'uploads/admin/20261021_004729_48cb3f5b9d.jpg', 0, 0.00),
(281, 183, 'Rosa', 88.00, 'uploads/admin/20261021_004748_aa757637c6.jpg', 0, 0.00),
(282, 183, 'Vermelho', 88.00, 'uploads/admin/20261021_004757_2a7b48ba74.png', 0, 0.00),
(283, 184, 'Azul', 69.00, 'uploads/admin/20261021_004838_911db1472a.png', 0, 0.00),
(284, 184, 'Cinza', 69.00, 'uploads/admin/20261021_004901_6a23ebeff6.png', 0, 0.00),
(285, 184, 'Preto', 71.00, 'uploads/admin/20261021_004912_3c16a2e2f2.png', 0, 0.00),
(286, 184, 'Rosa', 68.00, 'uploads/admin/20261021_004924_cf44b21caa.png', 0, 0.00),
(287, 184, 'Roxo', 65.00, 'uploads/admin/20261021_004935_457a31dc76.png', 0, 0.00),
(288, 184, 'Vermelho', 65.00, 'uploads/admin/20261021_004945_0d68f0c9d4.png', 0, 0.00),
(289, 185, 'Azul', 143.00, 'uploads/admin/20261021_005152_3c7f163a35.jpg', 0, 0.00),
(290, 185, 'Vermelho', 143.00, 'uploads/admin/20261021_005208_1401a68f50.jpg', 0, 0.00),
(291, 186, 'Azul', 157.00, 'uploads/admin/20261021_005238_7be13ffc2a.jpg', 0, 0.00),
(292, 186, 'Marrom', 157.00, 'uploads/admin/20261021_005259_bd6f5ee821.jpg', 0, 0.00),
(293, 186, 'Rosa', 157.00, 'uploads/admin/20261021_005310_62d289d0c3.jpg', 0, 0.00),
(294, 186, 'Verde', 157.00, 'uploads/admin/20261021_005321_2a62548caf.jpg', 0, 0.00),
(295, 187, 'Azul', 168.00, 'uploads/admin/20261021_005345_deb197e013.jpg', 0, 0.00),
(296, 187, 'Marrom', 168.00, 'uploads/admin/20261021_005404_12830d8985.jpg', 0, 0.00),
(297, 187, 'Rosa', 170.00, 'uploads/admin/20261021_005417_a5bc4fa994.jpg', 0, 0.00),
(298, 188, 'Azul', 240.00, 'uploads/admin/20261021_005459_2107cea158.jpg', 0, 0.00),
(299, 188, 'Verde', 240.00, 'uploads/admin/20261021_005530_e7ea20697e.jpg', 1, 5.00),
(300, 188, 'Vermelho', 240.00, 'uploads/admin/20261021_005546_293081d6fa.jpg', 1, 10.00),
(301, 189, 'Amarelo', 97.00, 'uploads/admin/20261021_005703_1ccb42e16b.png', 0, 0.00),
(302, 189, 'Azul', 99.99, 'uploads/admin/20261021_005722_c4544c44ae.png', 0, 0.00),
(303, 189, 'Preto', 99.99, 'uploads/admin/20261021_005731_81c7e4883f.png', 0, 0.00),
(304, 189, 'Roxo', 97.00, 'uploads/admin/20261021_005741_dc8709e802.jpg', 0, 0.00),
(305, 189, 'Verde', 95.00, 'uploads/admin/20261021_005752_1e92f14e02.png', 0, 0.00),
(306, 190, 'Preto', 110.00, 'uploads/admin/20261021_005948_98c6c4d5fc.jpg', 1, 5.00),
(307, 190, 'Rosa', 110.00, 'uploads/admin/20261021_010011_b3647aad5c.jpg', 1, 5.00),
(308, 190, 'Vermelho', 110.00, 'uploads/admin/20261021_010020_7479d13b84.jpg', 1, 5.00),
(309, 191, 'Azul', 125.00, 'uploads/admin/20261021_010224_18d70559c0.jpg', 0, 0.00),
(310, 191, 'Preto', 125.00, 'uploads/admin/20261021_010249_b257d8fe94.jpg', 0, 0.00),
(311, 191, 'Rosa', 125.00, 'uploads/admin/20261021_010257_b56d0edd0e.jpg', 0, 0.00),
(312, 192, 'Azul', 34.00, 'uploads/admin/20261021_010401_b16ac87f65.jpg', 0, 0.00),
(313, 192, 'Rosa', 34.00, 'uploads/admin/20261021_010414_e6d0f3b9e2.jpg', 0, 0.00),
(314, 193, 'Verde', 45.00, 'uploads/admin/20261021_010448_7c137020e3.jpg', 0, 0.00),
(315, 193, 'Vermelho', 45.00, 'uploads/admin/20261021_010502_032be7d809.jpg', 0, 0.00),
(316, 194, 'Branco', 75.00, 'uploads/admin/20261021_010537_3278fb1e8d.jpg', 0, 0.00),
(317, 194, 'Preto', 75.00, 'uploads/admin/20261021_010555_893abd91c3.jpg', 0, 0.00),
(318, 195, 'Azul', 89.00, 'uploads/admin/20261021_010646_df1f1c868b.png', 0, 0.00),
(319, 195, 'Rosa', 90.00, 'uploads/admin/20261021_010701_a8382d1b74.png', 0, 0.00),
(320, 196, '15kg', 95.00, 'uploads/admin/20261021_010918_c80dcb0922.png', 0, 0.00),
(321, 197, '15kg', 129.00, 'uploads/admin/20261021_011027_c44b19e4fc.jpg', 0, 0.00),
(322, 198, 'Beje', 45.00, 'uploads/admin/20261027_000745_bd6678fa5a.jpg', 0, 0.00),
(323, 199, 'Padrão', 87.00, 'uploads/admin/20261027_001020_bf3c75d045.jpg', 0, 0.00),
(324, 200, 'Padrão', 54.00, 'uploads/admin/20261027_001116_4f468ebd42.jpg', 0, 0.00),
(325, 201, 'Padrão', 49.00, 'uploads/admin/20261027_001153_f1895410c2.jpg', 0, 0.00),
(328, 202, 'Padrão', 57.00, 'uploads/admin/20261027_001216_86d2593029.jpg', 1, 5.00),
(329, 203, 'Padrão', 49.99, 'uploads/admin/20261027_001323_f23964daa0.jpg', 1, 5.00),
(330, 204, 'Padrão', 61.00, 'uploads/admin/20261027_001356_fdb0d396cc.jpg', 0, 0.00),
(331, 205, 'Padrão', 75.99, 'uploads/admin/20261027_001434_bbbcc5e5df.jpg', 0, 0.00),
(332, 206, 'Azul', 26.00, 'uploads/admin/20261027_001614_c746e3b729.jpg', 0, 0.00),
(333, 206, 'Laranja', 26.00, 'uploads/admin/20261027_001636_1eb6dd6e84.jpg', 0, 0.00),
(334, 206, 'Lima', 26.00, 'uploads/admin/20261027_001649_0ee478fb2d.jpg', 0, 0.00),
(335, 207, 'Azul', 35.00, 'uploads/admin/20261027_001725_4e77deb8a3.jpg', 0, 0.00),
(336, 207, 'Rosa', 35.00, 'uploads/admin/20261027_001745_069ec2e7b5.jpg', 0, 0.00),
(337, 208, 'Azul', 54.00, 'uploads/admin/20261027_002007_3cfc1e191c.jpg', 0, 0.00),
(338, 208, 'Preto', 54.00, 'uploads/admin/20261027_002033_b6490dd0d2.jpg', 0, 0.00),
(339, 208, 'Verde', 54.00, 'uploads/admin/20261027_002042_0a39277f06.jpg', 0, 0.00),
(340, 208, 'Vermelho', 54.00, 'uploads/admin/20261027_002050_4657b43c3c.jpg', 0, 0.00),
(341, 209, 'Verde', 58.00, 'uploads/admin/20261027_002133_e4ae56fe12.jpg', 0, 0.00),
(342, 209, 'Azul', 57.00, 'uploads/admin/20261027_002149_11fccab4bb.jpg', 0, 0.00),
(343, 209, 'Laranja', 54.00, 'uploads/admin/20261027_002159_99babb570e.jpg', 0, 0.00),
(344, 209, 'Rosa', 58.00, 'uploads/admin/20261027_002210_8aeb2d9b07.jpg', 0, 0.00),
(345, 210, 'Azul', 45.00, 'uploads/admin/20261027_002240_1fb1f8e63c.jpg', 0, 0.00),
(346, 210, 'Laranja', 45.00, 'uploads/admin/20261027_002303_40774c97e6.jpg', 0, 0.00),
(347, 210, 'Lima', 45.00, 'uploads/admin/20261027_002313_7bd51abb79.jpg', 0, 0.00),
(348, 210, 'Rosa', 45.00, 'uploads/admin/20261027_002325_35c63e529d.jpg', 0, 0.00),
(349, 211, 'Rosa', 75.00, 'uploads/admin/20261027_002415_9f53d678e2.jpg', 0, 0.00),
(350, 211, 'Laranja', 75.00, 'uploads/admin/20261027_002431_a12b2ae751.jpg', 0, 0.00),
(351, 211, 'Verde', 75.00, 'uploads/admin/20261027_002439_8b76fd24dc.jpg', 0, 0.00),
(352, 212, 'Padrão', 49.00, 'uploads/admin/20261027_002555_62a796b3d9.jpg', 0, 0.00),
(353, 213, 'Padrão', 58.00, 'uploads/admin/20261027_002633_53fa616645.jpg', 0, 0.00),
(354, 214, 'Padrão', 42.99, 'uploads/admin/20261027_003025_b5361d8b50.jpg', 0, 0.00),
(355, 215, 'Padrão', 67.99, 'uploads/admin/20261027_003128_479c14e138.jpg', 1, 5.00),
(356, 216, 'Carne', 5.99, 'uploads/admin/20261027_003252_3c46a384ab.png', 0, 0.00),
(357, 216, 'Frango', 4.99, 'uploads/admin/20261027_003321_e7d0baa288.png', 0, 0.00),
(358, 217, 'Banana', 12.00, 'uploads/admin/20261027_003357_43eb234019.jpg', 0, 0.00),
(359, 217, 'Frutas vermelhas', 15.00, 'uploads/admin/20261027_003429_eb098695ea.jpg', 0, 0.00),
(360, 217, 'Original', 12.00, 'uploads/admin/20261027_003448_8be0d0b905.jpg', 0, 0.00),
(361, 218, 'Churrasco', 7.99, 'uploads/admin/20261027_003526_023347dd3d.jpg', 0, 0.00),
(362, 219, 'Cordeiro', 14.00, 'uploads/admin/20261027_003611_0412da33d4.jpg', 0, 0.00),
(363, 220, 'Atum', 8.99, 'uploads/admin/20261027_003745_38f17efae2.jpg', 0, 0.00),
(364, 221, 'Padrão', 14.99, 'uploads/admin/20261027_003925_9038c13f55.jpg', 0, 0.00),
(365, 222, 'Atum', 17.00, 'uploads/admin/20261027_003946_1d09ef1b72.jpg', 0, 0.00),
(366, 223, '15kg', 78.00, 'uploads/admin/20261027_004037_1e64aad8d4.jpg', 0, 0.00),
(367, 224, '20Kg', 92.99, 'uploads/admin/20261027_004104_76f35ed9e8.jpg', 0, 0.00),
(368, 225, '15kg', 79.99, 'uploads/admin/20261027_004204_7c0c1a765b.jpg', 0, 0.00),
(369, 226, '15kg', 79.99, 'uploads/admin/20261027_004231_ca9e1f0099.jpg', 0, 0.00),
(370, 227, 'Ovelha e arroz', 89.99, 'uploads/admin/20261027_004330_002a5c0a62.jpg', 0, 0.00),
(371, 228, '10Kg', 67.00, 'uploads/admin/20261027_004439_96e2ed090f.jpg', 0, 0.00),
(372, 229, '10Kg', 78.00, 'uploads/admin/20261027_004531_3e026af559.jpg', 0, 0.00),
(373, 230, '10mg', 48.00, 'uploads/admin/20261027_004623_63865262de.png', 0, 0.00),
(374, 231, '6mg', 51.99, 'uploads/admin/20261027_004721_cbb75ddf31.png', 0, 0.00),
(375, 232, 'Padrão', 57.99, 'uploads/admin/20261027_004759_57cde27603.png', 0, 0.00),
(376, 233, '48cm', 52.99, 'uploads/admin/20261027_004832_995f53069c.png', 0, 0.00),
(377, 234, 'Padrão', 61.00, 'uploads/admin/20261027_004953_5b3f52595b.png', 0, 0.00),
(378, 235, '0,4ml', 75.00, 'uploads/admin/20261027_005032_5b27954d7b.jpg', 0, 0.00),
(379, 236, 'Padrão', 85.99, 'uploads/admin/20261027_005112_befe795b6a.png', 0, 0.00),
(380, 237, 'Padrão', 83.99, 'uploads/admin/20261027_005141_9fdeefa85a.png', 0, 0.00),
(381, 238, 'Padrão', 62.99, 'uploads/admin/20261027_005312_69c931094f.jpg', 0, 0.00),
(382, 239, 'Preto', 73.99, 'uploads/admin/20261027_005351_a95c92e3cc.png', 0, 0.00),
(383, 240, 'Padrão', 84.99, 'uploads/admin/20261027_005437_e131c3a070.jpg', 0, 0.00),
(384, 241, 'Padrão', 39.99, 'uploads/admin/20261027_005549_2280f3af38.png', 0, 0.00),
(385, 242, 'Padrão', 48.99, 'uploads/admin/20261027_005617_51b99ebc4a.png', 0, 0.00),
(386, 243, 'Padrão', 38.99, 'uploads/admin/20261027_005645_61a284c388.png', 0, 0.00),
(387, 244, 'Padrão', 52.00, 'uploads/admin/20261027_005733_747f443f17.png', 0, 0.00),
(388, 245, 'carne', 7.99, 'uploads/admin/20261027_005809_e33e42efb7.jpg', 0, 0.00),
(389, 246, 'Salmão', 8.99, 'uploads/admin/20261027_005841_3cf05d41fd.jpg', 0, 0.00),
(390, 247, 'Carne', 5.99, 'uploads/admin/20261027_005925_e8b8fdf061.png', 0, 0.00),
(391, 248, 'Carne', 9.99, 'uploads/admin/20261027_010000_68ee6048aa.png', 0, 0.00);

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `enderecos_usuario`
--
ALTER TABLE `enderecos_usuario`
  ADD CONSTRAINT `fk_endereco_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `fk_favoritos_produto` FOREIGN KEY (`id_produto`) REFERENCES `produtos_destaque` (`id_produto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_favoritos_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Restrições para tabelas `pedido_itens`
--
ALTER TABLE `pedido_itens`
  ADD CONSTRAINT `fk_pedido_itens_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `produtos_destaque`
--
ALTER TABLE `produtos_destaque`
  ADD CONSTRAINT `fk_produto_categoria` FOREIGN KEY (`id_categoria_produto`) REFERENCES `categorias_produtos` (`id_categoria_produto`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produto_destaque_marca` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `variacoes_produto`
--
ALTER TABLE `variacoes_produto`
  ADD CONSTRAINT `fk_variacao_produto` FOREIGN KEY (`id_produto`) REFERENCES `produtos_destaque` (`id_produto`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
