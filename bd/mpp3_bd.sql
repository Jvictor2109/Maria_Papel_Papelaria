-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 29-Jun-2026 às 14:24
-- Versão do servidor: 10.4.18-MariaDB
-- versão do PHP: 8.0.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `mpp3_bd`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `agrupamento`
--

CREATE TABLE `agrupamento` (
  `id_agrupamento` int(11) NOT NULL,
  `nome_agrupamento` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `agrupamento`
--

INSERT INTO `agrupamento` (`id_agrupamento`, `nome_agrupamento`) VALUES
(1, 'Agrupamento de escolas oliveira do bairro'),
(2, 'Agrupamento de escolas agueda Sul'),
(3, 'AE Vagos');

-- --------------------------------------------------------

--
-- Estrutura da tabela `ano_escolar`
--

CREATE TABLE `ano_escolar` (
  `id_ano_escolar` int(11) NOT NULL,
  `nome_ano_escolar` varchar(255) NOT NULL,
  `encomendas_ano` int(5) NOT NULL,
  `encomendas_inicial` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `ano_escolar`
--

INSERT INTO `ano_escolar` (`id_ano_escolar`, `nome_ano_escolar`, `encomendas_ano`, `encomendas_inicial`) VALUES
(4, '1ANO', 1001, 1000),
(5, '2ANO', 2000, 2000),
(6, '11ANO', 11000, 11000),
(10, '3 ANO', 3001, 3000);

-- --------------------------------------------------------

--
-- Estrutura da tabela `ano_letivo`
--

CREATE TABLE `ano_letivo` (
  `id_ano_letivo` int(11) NOT NULL,
  `nome_ano_letivo` varchar(20) NOT NULL,
  `enc_ano_letivo` int(11) NOT NULL DEFAULT 0,
  `ano_letivo_ativo` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `ano_letivo`
--

INSERT INTO `ano_letivo` (`id_ano_letivo`, `nome_ano_letivo`, `enc_ano_letivo`, `ano_letivo_ativo`) VALUES
(1, '2025/2026', 17, 0),
(2, '2026/2027', 0, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `disciplina`
--

CREATE TABLE `disciplina` (
  `id_disciplina` int(11) NOT NULL,
  `nome_disciplina` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `disciplina`
--

INSERT INTO `disciplina` (`id_disciplina`, `nome_disciplina`) VALUES
(1, 'Programação A'),
(4, 'Português'),
(7, 'Matemática'),
(8, 'Química'),
(9, 'Física'),
(10, 'Inglês'),
(16, 'Estudo do Meio');

-- --------------------------------------------------------

--
-- Estrutura da tabela `editora`
--

CREATE TABLE `editora` (
  `id_editora` int(11) NOT NULL,
  `nome_editora` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `editora`
--

INSERT INTO `editora` (`id_editora`, `nome_editora`) VALUES
(1, 'Grupo Penguin X'),
(2, 'Leya'),
(3, 'Porto Editora'),
(4, 'Areal');

-- --------------------------------------------------------

--
-- Estrutura da tabela `encomenda`
--

CREATE TABLE `encomenda` (
  `id_encomenda` int(11) NOT NULL,
  `data_encomenda` date NOT NULL,
  `nome_aluno_encomenda` text NOT NULL,
  `nif_encomenda` varchar(20) DEFAULT NULL,
  `ee_encomenda` text DEFAULT NULL,
  `telefone_encomenda` varchar(30) DEFAULT NULL,
  `email_encomenda` varchar(255) DEFAULT NULL,
  `num_encomenda` int(11) NOT NULL,
  `plast_manuais` tinyint(1) NOT NULL DEFAULT 0,
  `plast_livro_fichas` tinyint(1) NOT NULL DEFAULT 0,
  `etiquetas` tinyint(1) NOT NULL DEFAULT 0,
  `obs_etiquetas` text DEFAULT NULL,
  `total_encomenda` decimal(10,2) DEFAULT 0.00,
  `valor_caucao` decimal(10,2) DEFAULT 0.00,
  `id_utilizador` int(11) NOT NULL,
  `doc_encomenda` text DEFAULT NULL,
  `estado_encomenda` enum('registada','pedida','concluida','entregue','cancelada') NOT NULL DEFAULT 'registada',
  `data_pedido` date DEFAULT NULL,
  `data_concluida` date DEFAULT NULL,
  `id_concluida` int(11) DEFAULT NULL,
  `data_entregue` date DEFAULT NULL,
  `id_entregue` int(11) DEFAULT NULL,
  `codigo_mega` varchar(255) DEFAULT NULL,
  `id_ano_encomenda` int(11) NOT NULL,
  `data_cancelado` date DEFAULT NULL,
  `id_cancelado` int(11) DEFAULT NULL,
  `avisado` tinyint(1) DEFAULT 0,
  `id_avisado` int(11) DEFAULT NULL,
  `data_aviso` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `encomenda`
--

INSERT INTO `encomenda` (`id_encomenda`, `data_encomenda`, `nome_aluno_encomenda`, `nif_encomenda`, `ee_encomenda`, `telefone_encomenda`, `email_encomenda`, `num_encomenda`, `plast_manuais`, `plast_livro_fichas`, `etiquetas`, `obs_etiquetas`, `total_encomenda`, `valor_caucao`, `id_utilizador`, `doc_encomenda`, `estado_encomenda`, `data_pedido`, `data_concluida`, `id_concluida`, `data_entregue`, `id_entregue`, `codigo_mega`, `id_ano_encomenda`, `data_cancelado`, `id_cancelado`, `avisado`, `id_avisado`, `data_aviso`) VALUES
(1, '2026-06-26', 'joao', '', 'libna', '', 'joaobrasil2109@gmail.com', 3001, 0, 0, 0, NULL, '12.23', '0.00', 9, '/MPP_3/encomendas/3 ANO/encomenda_3001.pdf', 'entregue', '2026-06-26', '2026-06-26', 9, NULL, NULL, '', 10, NULL, NULL, 1, 9, '2026-06-26 17:23:28'),
(2, '2026-06-29', 'joao', '', 'libna', '928396756', '', 1001, 0, 0, 0, NULL, '66.84', '0.00', 9, '/MPP_3/encomendas/1ANO/encomenda_1001.pdf', 'entregue', NULL, '2026-06-29', 9, '2026-06-29', 9, '', 4, NULL, NULL, 1, 9, '2026-06-29 13:22:56');

-- --------------------------------------------------------

--
-- Estrutura da tabela `encomenda_editora`
--

CREATE TABLE `encomenda_editora` (
  `id_encomenda_editora` int(11) NOT NULL,
  `id_utilizador` int(11) NOT NULL,
  `data_encomenda_editora` date NOT NULL,
  `doc_encomenda_editora` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `encomenda_editora`
--

INSERT INTO `encomenda_editora` (`id_encomenda_editora`, `id_utilizador`, `data_encomenda_editora`, `doc_encomenda_editora`) VALUES
(1, 9, '2026-06-26', 'encomendas_a_editora/encomenda_editora_2026-06-26.xlsx');

-- --------------------------------------------------------

--
-- Estrutura da tabela `encomenda_manual`
--

CREATE TABLE `encomenda_manual` (
  `id_encomenda` int(11) NOT NULL,
  `id_manual` int(11) NOT NULL,
  `manual_separado` tinyint(1) NOT NULL DEFAULT 0,
  `data_separacao` date DEFAULT NULL,
  `voucher` tinyint(1) NOT NULL DEFAULT 0,
  `id_separado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `encomenda_manual`
--

INSERT INTO `encomenda_manual` (`id_encomenda`, `id_manual`, `manual_separado`, `data_separacao`, `voucher`, `id_separado`) VALUES
(1, 58, 1, '2026-06-26', 1, 9),
(1, 60, 1, '2026-06-26', 1, 9),
(1, 62, 1, '2026-06-26', 1, 9),
(1, 64, 1, '2026-06-26', 0, 9),
(2, 52, 1, '2026-06-29', 0, 9),
(2, 53, 1, '2026-06-29', 0, 9),
(2, 54, 1, '2026-06-29', 0, 9),
(2, 55, 1, '2026-06-29', 0, 9),
(2, 56, 1, '2026-06-29', 0, 9),
(2, 57, 1, '2026-06-29', 0, 9);

-- --------------------------------------------------------

--
-- Estrutura da tabela `manual`
--

CREATE TABLE `manual` (
  `id_manual` int(11) NOT NULL,
  `isbn_manual` varchar(20) NOT NULL,
  `nome_manual` varchar(255) NOT NULL,
  `preco_manual` decimal(10,2) NOT NULL,
  `cod_manual` varchar(50) DEFAULT NULL,
  `tipo_manual` enum('Manual','Livro de Fichas') NOT NULL,
  `quant_manuais_pedir` int(11) NOT NULL DEFAULT 0,
  `quant_manuais_enc` int(11) NOT NULL DEFAULT 0,
  `id_disciplina` int(11) NOT NULL,
  `id_editora` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `manual`
--

INSERT INTO `manual` (`id_manual`, `isbn_manual`, `nome_manual`, `preco_manual`, `cod_manual`, `tipo_manual`, `quant_manuais_pedir`, `quant_manuais_enc`, `id_disciplina`, `id_editora`) VALUES
(52, '9789720111289', 'VAMOS aprender Português - 1.º Ano', '10.26', '', 'Manual', 1, 2, 4, 3),
(53, '9789720111296', 'VAMOS praticar + (Livro de Fichas) - Português - 1.º Ano', '12.40', '', 'Livro de Fichas', 1, 2, 4, 3),
(54, '9789720130266', 'VAMOS aprender Matemática - 1.º Ano', '10.25', '', 'Manual', 1, 2, 7, 3),
(55, '9789720130273', 'VAMOS praticar + (Livro de Fichas) - Matemática - 1.º Ano', '12.40', '', 'Livro de Fichas', 1, 2, 7, 3),
(56, '9789720120045', 'VAMOS aprender Estudo do Meio - 1.º Ano', '10.18', '', 'Manual', 1, 2, 16, 3),
(57, '9789720120052', 'VAMOS praticar + (Livro de Fichas) - Estudo do Meio - 1.º Ano', '11.35', '', 'Livro de Fichas', 1, 2, 16, 3),
(58, '9789720112590', 'MISSÃO Zupi - Português - 3.º Ano', '12.26', '', 'Manual', 0, 3, 4, 3),
(59, '9789720112606', 'Livro de Fichas - MISSÃO Zupi - Português - 3.º Ano', '13.25', '', 'Livro de Fichas', 0, 1, 4, 3),
(60, '9789720132413', 'MISSÃO Zupi - Matemática - 3.º Ano', '12.18', '', 'Manual', 0, 3, 7, 3),
(61, '9789720132420', 'Livro de Fichas - MISSÃO Zupi - Matemática - 3.º Ano', '13.25', '', 'Livro de Fichas', 0, 1, 7, 3),
(62, '9789720123138', 'MISSÃO Zupi - Estudo do Meio - 3.º Ano', '12.29', '', 'Manual', 0, 3, 16, 3),
(63, '9789720123145', 'Livro de Fichas - MISSÃO Zupi - Estudo do Meio - 3.º Ano', '13.05', '', 'Livro de Fichas', 0, 1, 16, 3),
(64, '9789724757261', 'Start the Magic! 3', '12.23', '', 'Manual', 0, 3, 10, 2),
(65, '9789724757278', 'Start the Magic! 3 - Workbook', '13.08', '', 'Livro de Fichas', 0, 1, 10, 2);

-- --------------------------------------------------------

--
-- Estrutura da tabela `manual_agrupamento`
--

CREATE TABLE `manual_agrupamento` (
  `id_manual` int(11) NOT NULL,
  `id_agrupamento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `manual_agrupamento`
--

INSERT INTO `manual_agrupamento` (`id_manual`, `id_agrupamento`) VALUES
(52, 3),
(53, 3),
(54, 3),
(55, 3),
(56, 3),
(57, 3),
(58, 3),
(59, 3),
(60, 3),
(61, 3),
(62, 3),
(63, 3),
(64, 3),
(65, 3);

-- --------------------------------------------------------

--
-- Estrutura da tabela `manual_ano_escolar`
--

CREATE TABLE `manual_ano_escolar` (
  `id_manual` int(11) NOT NULL,
  `id_ano_escolar` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `manual_ano_escolar`
--

INSERT INTO `manual_ano_escolar` (`id_manual`, `id_ano_escolar`) VALUES
(52, 4),
(53, 4),
(54, 4),
(55, 4),
(56, 4),
(57, 4),
(58, 10),
(59, 10),
(60, 10),
(61, 10),
(62, 10),
(63, 10),
(64, 10),
(65, 10);

-- --------------------------------------------------------

--
-- Estrutura da tabela `observacao_encomenda`
--

CREATE TABLE `observacao_encomenda` (
  `id_obs_encomenda` int(11) NOT NULL,
  `id_encomenda` int(11) NOT NULL,
  `observacao_encomenda` text NOT NULL,
  `data_observacao` datetime NOT NULL,
  `id_utilizador` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `observacao_encomenda`
--

INSERT INTO `observacao_encomenda` (`id_obs_encomenda`, `id_encomenda`, `observacao_encomenda`, `data_observacao`, `id_utilizador`) VALUES
(1, 1, 'A encomenda foi registada.\n', '2026-06-26 17:20:29', 9),
(2, 1, 'A encomenda passou ao estado de pedida.', '2026-06-26 17:21:00', 9),
(3, 1, 'A encomenda passou ao estado de concluída.', '2026-06-26 17:23:21', 9),
(4, 1, 'O cliente foi avisado novamente para vir levantar a encomenda.', '2026-06-26 17:24:25', 9),
(5, 1, 'A encomenda passou ao estado de entregue.', '2026-06-26 17:24:42', 9),
(6, 2, 'A encomenda foi registada.\n', '2026-06-29 13:22:22', 9),
(7, 2, 'A encomenda passou ao estado de concluída.', '2026-06-29 13:22:49', 9),
(8, 2, 'A encomenda passou ao estado de entregue.', '2026-06-29 13:23:49', 9);

-- --------------------------------------------------------

--
-- Estrutura da tabela `reposicao`
--

CREATE TABLE `reposicao` (
  `item_id` int(11) NOT NULL,
  `artigo` varchar(50) NOT NULL,
  `referencia` varchar(30) DEFAULT NULL,
  `tipo` enum('livros','tinteiros','papelaria') NOT NULL,
  `urgencia` enum('urgente','muito urgente','nao urgente') NOT NULL,
  `nome_cliente` varchar(20) DEFAULT NULL,
  `telefone_cliente` varchar(9) DEFAULT NULL,
  `data_criacao` date NOT NULL,
  `pedido` tinyint(1) DEFAULT 0,
  `data_pedido` date DEFAULT NULL,
  `concluido` tinyint(1) DEFAULT 0,
  `data_conclusao` date DEFAULT NULL,
  `id_criado_por` int(11) DEFAULT NULL,
  `id_pedido_por` int(11) DEFAULT NULL,
  `id_concluido_por` int(11) DEFAULT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 1 CHECK (`quantidade` > 0),
  `observacoes` text DEFAULT NULL,
  `cancelado` tinyint(1) DEFAULT 0,
  `cancelado_por` int(11) DEFAULT NULL,
  `data_cancelado` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `reposicao`
--

INSERT INTO `reposicao` (`item_id`, `artigo`, `referencia`, `tipo`, `urgencia`, `nome_cliente`, `telefone_cliente`, `data_criacao`, `pedido`, `data_pedido`, `concluido`, `data_conclusao`, `id_criado_por`, `id_pedido_por`, `id_concluido_por`, `quantidade`, `observacoes`, `cancelado`, `cancelado_por`, `data_cancelado`) VALUES
(32, 'Canetas Bic Azul', '', 'papelaria', 'muito urgente', 'João', '965458563', '2026-04-08', 1, '2026-04-08', 1, '2026-04-08', 1, 4, 4, 1, 'Obs dsljn', 0, NULL, NULL),
(33, 'Os Maias', '', 'livros', 'nao urgente', '', '', '2026-04-08', 1, '2026-05-19', 1, '2026-05-19', 4, NULL, NULL, 3, '', 0, NULL, NULL),
(34, 'Toner 83A', '', 'tinteiros', 'nao urgente', 'Maria', '999999999', '2026-04-08', 1, '2026-05-19', 1, '2026-05-19', 4, NULL, NULL, 2, 'A senhora pede que ligue mas se ninguém atender que envie mensagem', 0, NULL, NULL),
(53, 'aaaaa', '', 'papelaria', 'muito urgente', '', '', '2026-06-17', 1, '2026-06-17', 1, '2026-06-17', 9, 9, 9, 1, '', 0, NULL, NULL),
(54, 'caneta', '', 'papelaria', 'muito urgente', '', '', '2026-06-24', 1, '2026-06-24', 1, '2026-06-24', 9, 9, 9, 1, '', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizador`
--

CREATE TABLE `utilizador` (
  `id_utilizador` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_utilizador` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_utilizador` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `e_administrador` tinyint(1) DEFAULT 0,
  `utilizador_ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_login` datetime DEFAULT NULL,
  `tentativas_falhas` int(11) DEFAULT 0,
  `password_alterada` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `utilizador`
--

INSERT INTO `utilizador` (`id_utilizador`, `username`, `email_utilizador`, `nome_utilizador`, `password_hash`, `e_administrador`, `utilizador_ativo`, `data_criacao`, `ultimo_login`, `tentativas_falhas`, `password_alterada`) VALUES
(1, 'valterornelas', 'valterornelas@gmail.com', 'Valter Duarte Vieira Ornelas', '$2y$10$BpNGgfITjyXX0wkJYgeWc.8pDdE/cFVoalKCI2qdJ2J3hPwk8saku', 1, 1, '2026-02-04 15:23:37', '2026-04-17 11:22:18', 0, 1),
(4, 'miguelvieira', 'mvieira@gmail.com', 'Luís Miguel Vieira', '$2y$10$XcyWb9nfG/alA9tuRqEv4OtXxBhT6r30XM7VXjigHq1c.y2B7eoSy', 0, 1, '2026-02-12 17:28:06', '2026-04-08 09:57:07', 0, 1),
(5, 'duarteornelas', 'dmornelas@gmail.com', 'Duarte Mariano Ornelas', '$2y$10$pmC8l3o.3i0Mc.r0Pck3zu6TcEJPbb//bxhCTJ2Ff7tDt4eAsWDQi', 0, 1, '2026-02-12 17:28:51', NULL, 0, 0),
(9, 'joaocastro', 'joaobrasil2109@gmail.com', 'João Castro', '$2y$10$jI4UnZPMZG6MUIFUrhNuHe.hBZt0InH5tn/f2YtuxDyoBCteLxpHa', 1, 1, '2026-06-17 09:39:01', '2026-06-29 13:19:09', 0, 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `agrupamento`
--
ALTER TABLE `agrupamento`
  ADD PRIMARY KEY (`id_agrupamento`);

--
-- Índices para tabela `ano_escolar`
--
ALTER TABLE `ano_escolar`
  ADD PRIMARY KEY (`id_ano_escolar`);

--
-- Índices para tabela `ano_letivo`
--
ALTER TABLE `ano_letivo`
  ADD PRIMARY KEY (`id_ano_letivo`);

--
-- Índices para tabela `disciplina`
--
ALTER TABLE `disciplina`
  ADD PRIMARY KEY (`id_disciplina`);

--
-- Índices para tabela `editora`
--
ALTER TABLE `editora`
  ADD PRIMARY KEY (`id_editora`);

--
-- Índices para tabela `encomenda`
--
ALTER TABLE `encomenda`
  ADD PRIMARY KEY (`id_encomenda`),
  ADD KEY `fk_encomenda_utilizador` (`id_utilizador`),
  ADD KEY `fk_encomenda_concluida` (`id_concluida`),
  ADD KEY `fk_encomenda_entregue` (`id_entregue`),
  ADD KEY `fk_ano_escolar_encomenda` (`id_ano_encomenda`),
  ADD KEY `fk_user_cancelado` (`id_cancelado`),
  ADD KEY `fk_id_avisado` (`id_avisado`);

--
-- Índices para tabela `encomenda_editora`
--
ALTER TABLE `encomenda_editora`
  ADD PRIMARY KEY (`id_encomenda_editora`),
  ADD KEY `id_utilizador` (`id_utilizador`);

--
-- Índices para tabela `encomenda_manual`
--
ALTER TABLE `encomenda_manual`
  ADD PRIMARY KEY (`id_encomenda`,`id_manual`),
  ADD KEY `fk_em_manual` (`id_manual`),
  ADD KEY `fk_id_separado` (`id_separado`);

--
-- Índices para tabela `manual`
--
ALTER TABLE `manual`
  ADD PRIMARY KEY (`id_manual`),
  ADD KEY `fk_manual_disciplina` (`id_disciplina`),
  ADD KEY `fk_manual_editora` (`id_editora`);

--
-- Índices para tabela `manual_agrupamento`
--
ALTER TABLE `manual_agrupamento`
  ADD PRIMARY KEY (`id_manual`,`id_agrupamento`),
  ADD KEY `fk_manual_agrupamento_agrupamento` (`id_agrupamento`);

--
-- Índices para tabela `manual_ano_escolar`
--
ALTER TABLE `manual_ano_escolar`
  ADD PRIMARY KEY (`id_manual`,`id_ano_escolar`),
  ADD KEY `fk_manual_ano_ano_escolar` (`id_ano_escolar`);

--
-- Índices para tabela `observacao_encomenda`
--
ALTER TABLE `observacao_encomenda`
  ADD PRIMARY KEY (`id_obs_encomenda`),
  ADD KEY `fk_oe_encomenda` (`id_encomenda`),
  ADD KEY `fk_oe_utilizador` (`id_utilizador`);

--
-- Índices para tabela `reposicao`
--
ALTER TABLE `reposicao`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `fk_criado_por` (`id_criado_por`),
  ADD KEY `fk_pedido_por` (`id_pedido_por`),
  ADD KEY `fk_concluido_por` (`id_concluido_por`),
  ADD KEY `fk_cancelado_por` (`cancelado_por`);

--
-- Índices para tabela `utilizador`
--
ALTER TABLE `utilizador`
  ADD PRIMARY KEY (`id_utilizador`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email_utilizador` (`email_utilizador`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agrupamento`
--
ALTER TABLE `agrupamento`
  MODIFY `id_agrupamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `ano_escolar`
--
ALTER TABLE `ano_escolar`
  MODIFY `id_ano_escolar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `ano_letivo`
--
ALTER TABLE `ano_letivo`
  MODIFY `id_ano_letivo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `disciplina`
--
ALTER TABLE `disciplina`
  MODIFY `id_disciplina` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `editora`
--
ALTER TABLE `editora`
  MODIFY `id_editora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `encomenda`
--
ALTER TABLE `encomenda`
  MODIFY `id_encomenda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `encomenda_editora`
--
ALTER TABLE `encomenda_editora`
  MODIFY `id_encomenda_editora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `manual`
--
ALTER TABLE `manual`
  MODIFY `id_manual` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT de tabela `observacao_encomenda`
--
ALTER TABLE `observacao_encomenda`
  MODIFY `id_obs_encomenda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `reposicao`
--
ALTER TABLE `reposicao`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de tabela `utilizador`
--
ALTER TABLE `utilizador`
  MODIFY `id_utilizador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `encomenda`
--
ALTER TABLE `encomenda`
  ADD CONSTRAINT `fk_ano_escolar_encomenda` FOREIGN KEY (`id_ano_encomenda`) REFERENCES `ano_escolar` (`id_ano_escolar`),
  ADD CONSTRAINT `fk_encomenda_concluida` FOREIGN KEY (`id_concluida`) REFERENCES `utilizador` (`id_utilizador`),
  ADD CONSTRAINT `fk_encomenda_entregue` FOREIGN KEY (`id_entregue`) REFERENCES `utilizador` (`id_utilizador`),
  ADD CONSTRAINT `fk_encomenda_utilizador` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizador` (`id_utilizador`),
  ADD CONSTRAINT `fk_id_avisado` FOREIGN KEY (`id_avisado`) REFERENCES `utilizador` (`id_utilizador`),
  ADD CONSTRAINT `fk_user_cancelado` FOREIGN KEY (`id_cancelado`) REFERENCES `utilizador` (`id_utilizador`);

--
-- Limitadores para a tabela `encomenda_editora`
--
ALTER TABLE `encomenda_editora`
  ADD CONSTRAINT `encomenda_editora_ibfk_1` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizador` (`id_utilizador`);

--
-- Limitadores para a tabela `encomenda_manual`
--
ALTER TABLE `encomenda_manual`
  ADD CONSTRAINT `fk_em_encomenda` FOREIGN KEY (`id_encomenda`) REFERENCES `encomenda` (`id_encomenda`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_em_manual` FOREIGN KEY (`id_manual`) REFERENCES `manual` (`id_manual`),
  ADD CONSTRAINT `fk_id_separado` FOREIGN KEY (`id_separado`) REFERENCES `utilizador` (`id_utilizador`);

--
-- Limitadores para a tabela `manual`
--
ALTER TABLE `manual`
  ADD CONSTRAINT `fk_manual_disciplina` FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina` (`id_disciplina`),
  ADD CONSTRAINT `fk_manual_editora` FOREIGN KEY (`id_editora`) REFERENCES `editora` (`id_editora`);

--
-- Limitadores para a tabela `manual_agrupamento`
--
ALTER TABLE `manual_agrupamento`
  ADD CONSTRAINT `fk_manual_agrupamento_agrupamento` FOREIGN KEY (`id_agrupamento`) REFERENCES `agrupamento` (`id_agrupamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_manual_agrupamento_manual` FOREIGN KEY (`id_manual`) REFERENCES `manual` (`id_manual`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `manual_ano_escolar`
--
ALTER TABLE `manual_ano_escolar`
  ADD CONSTRAINT `fk_manual_ano_ano_escolar` FOREIGN KEY (`id_ano_escolar`) REFERENCES `ano_escolar` (`id_ano_escolar`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_manual_ano_manual` FOREIGN KEY (`id_manual`) REFERENCES `manual` (`id_manual`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `reposicao`
--
ALTER TABLE `reposicao`
  ADD CONSTRAINT `fk_cancelado_por` FOREIGN KEY (`cancelado_por`) REFERENCES `utilizador` (`id_utilizador`),
  ADD CONSTRAINT `fk_concluido_por` FOREIGN KEY (`id_concluido_por`) REFERENCES `utilizador` (`id_utilizador`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_criado_por` FOREIGN KEY (`id_criado_por`) REFERENCES `utilizador` (`id_utilizador`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pedido_por` FOREIGN KEY (`id_pedido_por`) REFERENCES `utilizador` (`id_utilizador`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
