-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql112.infinityfree.com
-- Tempo de geração: 06/11/2026 às 13:19
-- Versão do servidor: 11.4.13-MariaDB
-- Versão do PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `if0_43054171_BancoRepre`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `alunos`
--

CREATE TABLE `alunos` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `tipo` enum('aluno','representante','professor','coordenador','gremio_atletica') NOT NULL,
  `status_conta` enum('pendente','ativo','recusado') NOT NULL DEFAULT 'ativo',
  `matricula` varchar(30) DEFAULT NULL,
  `ultimo_login` datetime DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `alunos`
--

INSERT INTO `alunos` (`id`, `nome`, `email`, `senha_hash`, `tipo`, `status_conta`, `matricula`, `ultimo_login`, `criado_em`, `atualizado_em`) VALUES
(6, 'Matheus Camilo', 'matheusrepre@representante.com', '$2y$12$m0urWEbHaAjIeffLMeMZJ.UP0iDiWQeZFkM0tIrMJMjt04WmdPcRu', 'representante', 'ativo', NULL, '2026-11-05 10:27:09', '2026-11-04 10:50:10', '2026-11-05 10:27:09'),
(7, 'Heloisa Auletta', 'heauletta@aluno.com', '$2y$12$maWQKVH/1wtyJkJK.fHoHuC2FAAm.AGcdaQP5r1Og2cHM0pVGIMwi', 'aluno', 'ativo', NULL, '2026-11-04 11:18:48', '2026-11-04 11:18:34', '2026-11-04 11:18:48'),
(8, 'Melody', 'melody@representante.com', '$2y$12$9QCK9LOfAHwjnCzsA17K1u54tr5LQ5cZOgdzEvEkJhP6Wpx02uZ2K', 'representante', 'ativo', NULL, '2026-11-04 12:56:03', '2026-11-04 12:55:12', '2026-11-04 12:56:03'),
(9, 'Livia Pinehiro', 'olivia@aluno.com', '$2y$12$KOLrNcbrLyy1t.PI8QjOGOi65AU32uZnf3Kn.MIJSwxSeXirNYbMC', 'aluno', 'ativo', NULL, '2026-11-04 13:40:47', '2026-11-04 13:40:34', '2026-11-04 13:40:47'),
(10, 'teste', 'teste@representante.com', '$2y$12$CvskLAHrsA2FPcEZEbLERuD3cxL1b9w/U4uXVUYUsO7kJRzNe5QZO', 'representante', 'ativo', NULL, '2026-11-04 13:48:38', '2026-11-04 13:48:26', '2026-11-04 13:48:38'),
(11, 'testealuno', 'teste@aluno.com', '$2y$12$89gcleEDLW2YIwpRwOGkLePFzYzZNZEMIuq2myEnJ5pc.7RyHThGK', 'aluno', 'ativo', NULL, '2026-11-04 13:49:56', '2026-11-04 13:49:43', '2026-11-04 13:49:56');

-- --------------------------------------------------------

--
-- Estrutura para tabela `atividades`
--

CREATE TABLE `atividades` (
  `id` int(10) UNSIGNED NOT NULL,
  `turma_disciplina_id` int(10) UNSIGNED NOT NULL,
  `criador_id` int(10) UNSIGNED NOT NULL,
  `bimestre` tinyint(3) UNSIGNED DEFAULT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo` enum('tarefa','trabalho','prova','quiz','projeto','leitura') NOT NULL DEFAULT 'tarefa',
  `status` enum('rascunho','publicada','encerrada') NOT NULL DEFAULT 'rascunho',
  `data_limite` datetime DEFAULT NULL,
  `criada_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizada_em` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `em_grupo` tinyint(1) DEFAULT 0
) ;

--
-- Despejando dados para a tabela `atividades`
--

INSERT INTO `atividades` (`id`, `turma_disciplina_id`, `criador_id`, `bimestre`, `titulo`, `descricao`, `tipo`, `status`, `data_limite`, `criada_em`, `atualizada_em`, `em_grupo`) VALUES
(3, 5, 6, 3, 'wscazs', 'eafwsa', 'trabalho', 'publicada', '2026-11-06 16:17:00', '2026-11-04 11:17:40', '2026-11-04 11:17:40', 0),
(4, 7, 6, 4, 'teste', 'wdad', 'projeto', 'publicada', '2026-11-27 18:31:00', '2026-11-05 13:31:43', '2026-11-05 13:31:43', 0),
(5, 6, 6, 3, 'testet2', 'wfas', 'trabalho', 'rascunho', '2026-11-24 18:32:00', '2026-11-05 13:32:11', '2026-11-05 13:32:11', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `comunicados`
--

CREATE TABLE `comunicados` (
  `id` int(10) UNSIGNED NOT NULL,
  `turma_id` int(10) UNSIGNED DEFAULT NULL,
  `turma_disciplina_id` int(10) UNSIGNED DEFAULT NULL,
  `canal` enum('Eventos','Arquivo de aula') DEFAULT NULL,
  `autor_id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `conteudo` text NOT NULL,
  `arquivo_nome` varchar(255) DEFAULT NULL,
  `arquivo_caminho` varchar(500) DEFAULT NULL,
  `fixado` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `disciplinas`
--

CREATE TABLE `disciplinas` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(80) NOT NULL,
  `cor_hex` char(7) NOT NULL DEFAULT '#6B5CC5'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `disciplinas`
--

INSERT INTO `disciplinas` (`id`, `nome`, `cor_hex`) VALUES
(1, 'Base Comum', '#32b521'),
(2, 'Base Técnica', '#2821b5'),
(3, 'Atividade Interdiciplinar', '#e3cf19');

-- --------------------------------------------------------

--
-- Estrutura para tabela `entregas`
--

CREATE TABLE `entregas` (
  `atividade_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `enviado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `entregas`
--

INSERT INTO `entregas` (`atividade_id`, `aluno_id`, `enviado_em`) VALUES
(3, 6, '2026-11-04 11:17:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `eventos`
--

CREATE TABLE `eventos` (
  `id` int(10) UNSIGNED NOT NULL,
  `turma_id` int(10) UNSIGNED DEFAULT NULL,
  `criador_id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo` enum('aula','prova','entrega','reuniao','feriado','outro') NOT NULL DEFAULT 'outro',
  `inicio` datetime NOT NULL,
  `fim` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `eventos`
--

INSERT INTO `eventos` (`id`, `turma_id`, `criador_id`, `titulo`, `descricao`, `tipo`, `inicio`, `fim`) VALUES
(3, 5, 6, 'Reunião de professores', 'dvgdxv', 'reuniao', '2026-11-06 16:57:00', '2026-11-06 18:58:00'),
(4, 5, 6, 'aaaaaaaaaaaa', 'aaaaaaaaaaaaa', 'entrega', '2026-11-19 17:37:00', '2026-11-23 17:38:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `materiais_estudos`
--

CREATE TABLE `materiais_estudos` (
  `id` int(10) UNSIGNED NOT NULL,
  `turma_id` int(10) UNSIGNED NOT NULL,
  `turma_disciplinas_id` int(10) UNSIGNED NOT NULL,
  `publicado_por` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo` enum('pdf','documento','imagem','slide','link','outro') NOT NULL DEFAULT 'pdf',
  `link_url` varchar(2048) DEFAULT NULL,
  `arquivo_nome` varchar(255) DEFAULT NULL,
  `arquivo_caminho` varchar(500) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `materiais_estudos`
--

INSERT INTO `materiais_estudos` (`id`, `turma_id`, `turma_disciplinas_id`, `publicado_por`, `titulo`, `descricao`, `tipo`, `link_url`, `arquivo_nome`, `arquivo_caminho`, `criado_em`) VALUES
(1, 5, 5, 6, 'lista de matematica', 'vbbcf', 'pdf', NULL, 'lista_nomes_completos.xlsx', 'uploads/materiais/ebebafc1941d0bf2e66b8880242c25ad.xlsx', '2026-11-04 20:04:20'),
(2, 5, 6, 6, 'aaaaaaaaaaa', 'aaaaaaaaaaa', 'slide', NULL, 'Capa_Investigacao_Evolutiva_ABNT.docx', 'uploads/materiais/0d10c8efd7318ba792b90d6701dcecf7.docx', '2026-11-04 20:38:44');

-- --------------------------------------------------------

--
-- Estrutura para tabela `turmas`
--

CREATE TABLE `turmas` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(80) NOT NULL,
  `serie` varchar(40) DEFAULT NULL,
  `turno` enum('manha','tarde','noite') NOT NULL DEFAULT 'manha',
  `ano_letivo` year(4) NOT NULL,
  `ativa` tinyint(1) NOT NULL DEFAULT 1,
  `codigo_convite` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `turmas`
--

INSERT INTO `turmas` (`id`, `nome`, `serie`, `turno`, `ano_letivo`, `ativa`, `codigo_convite`) VALUES
(5, '3° DSM', '3° ano', 'manha', 2026, 1, 'CA27DC32'),
(6, '3° INFONET', '3° ano', 'tarde', 2026, 1, 'E571C29C'),
(8, 'Protocolo', '1° ano', 'noite', 2026, 1, '349B8A28');

-- --------------------------------------------------------

--
-- Estrutura para tabela `turma_disciplinas`
--

CREATE TABLE `turma_disciplinas` (
  `id` int(10) UNSIGNED NOT NULL,
  `turma_id` int(10) UNSIGNED NOT NULL,
  `disciplina_id` int(10) UNSIGNED NOT NULL,
  `professor_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `turma_disciplinas`
--

INSERT INTO `turma_disciplinas` (`id`, `turma_id`, `disciplina_id`, `professor_id`) VALUES
(5, 5, 1, NULL),
(6, 5, 2, NULL),
(7, 5, 3, NULL),
(8, 6, 1, NULL),
(9, 6, 2, NULL),
(10, 6, 3, NULL),
(11, 8, 1, NULL),
(12, 8, 2, NULL),
(13, 8, 3, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `turma_membros`
--

CREATE TABLE `turma_membros` (
  `turma_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `papel` enum('aluno','representante') NOT NULL DEFAULT 'aluno',
  `entrou_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `turma_membros`
--

INSERT INTO `turma_membros` (`turma_id`, `aluno_id`, `papel`, `entrou_em`) VALUES
(5, 6, 'representante', '2026-11-04 11:16:52'),
(5, 7, 'aluno', '2026-11-04 11:19:17'),
(6, 8, 'representante', '2026-11-04 13:34:07'),
(6, 9, 'aluno', '2026-11-04 13:43:38'),
(8, 10, 'representante', '2026-11-04 13:49:08'),
(8, 11, 'aluno', '2026-11-04 13:50:33');

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `matricula` (`matricula`),
  ADD KEY `idx_alunos_tipo` (`tipo`),
  ADD KEY `idx_alunos_status` (`status_conta`);

--
-- Índices de tabela `atividades`
--
ALTER TABLE `atividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ativ_limite` (`data_limite`),
  ADD KEY `idx_ativ_td` (`turma_disciplina_id`,`status`),
  ADD KEY `criador_id` (`criador_id`);

--
-- Índices de tabela `comunicados`
--
ALTER TABLE `comunicados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `turma_id` (`turma_id`),
  ADD KEY `turma_disciplina_id` (`turma_disciplina_id`),
  ADD KEY `idx_com_canal` (`canal`,`criado_em`),
  ADD KEY `autor_id` (`autor_id`);

--
-- Índices de tabela `disciplinas`
--
ALTER TABLE `disciplinas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `entregas`
--
ALTER TABLE `entregas`
  ADD PRIMARY KEY (`atividade_id`,`aluno_id`),
  ADD KEY `idx_entregas_aluno` (`aluno_id`,`enviado_em`);

--
-- Índices de tabela `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_evento_inicio` (`inicio`),
  ADD KEY `turma_id` (`turma_id`),
  ADD KEY `criador_id` (`criador_id`);

--
-- Índices de tabela `materiais_estudos`
--
ALTER TABLE `materiais_estudos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_materiais_turma_data` (`turma_id`,`criado_em`),
  ADD KEY `idx_materiais_disciplina` (`turma_disciplinas_id`),
  ADD KEY `idx_materiais_publicados_por` (`publicado_por`);

--
-- Índices de tabela `turmas`
--
ALTER TABLE `turmas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_turma` (`nome`,`ano_letivo`),
  ADD UNIQUE KEY `uq_turmas_codigo_convite` (`codigo_convite`);

--
-- Índices de tabela `turma_disciplinas`
--
ALTER TABLE `turma_disciplinas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_td` (`turma_id`,`disciplina_id`),
  ADD KEY `disciplina_id` (`disciplina_id`),
  ADD KEY `professor_id` (`professor_id`);

--
-- Índices de tabela `turma_membros`
--
ALTER TABLE `turma_membros`
  ADD PRIMARY KEY (`turma_id`,`aluno_id`),
  ADD KEY `aluno_id` (`aluno_id`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `atividades`
--
ALTER TABLE `atividades`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `disciplinas`
--
ALTER TABLE `disciplinas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `materiais_estudos`
--
ALTER TABLE `materiais_estudos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `turmas`
--
ALTER TABLE `turmas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `turma_disciplinas`
--
ALTER TABLE `turma_disciplinas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restrições para dumps de tabelas
--

--
-- Restrições para tabelas `atividades`
--
ALTER TABLE `atividades`
  ADD CONSTRAINT `fk_ativ_criador` FOREIGN KEY (`criador_id`) REFERENCES `alunos` (`id`),
  ADD CONSTRAINT `fk_ativ_td` FOREIGN KEY (`turma_disciplina_id`) REFERENCES `turma_disciplinas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `comunicados`
--
ALTER TABLE `comunicados`
  ADD CONSTRAINT `fk_com_autor` FOREIGN KEY (`autor_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_com_td` FOREIGN KEY (`turma_disciplina_id`) REFERENCES `turma_disciplinas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_com_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `entregas`
--
ALTER TABLE `entregas`
  ADD CONSTRAINT `fk_ent_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ent_atividade` FOREIGN KEY (`atividade_id`) REFERENCES `atividades` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `fk_evt_criador` FOREIGN KEY (`criador_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_evt_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `materiais_estudos`
--
ALTER TABLE `materiais_estudos`
  ADD CONSTRAINT `fk_mat_autor` FOREIGN KEY (`publicado_por`) REFERENCES `alunos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mat_td` FOREIGN KEY (`turma_disciplinas_id`) REFERENCES `turma_disciplinas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mat_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `turma_disciplinas`
--
ALTER TABLE `turma_disciplinas`
  ADD CONSTRAINT `fk_td_disciplina` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`),
  ADD CONSTRAINT `fk_td_professor` FOREIGN KEY (`professor_id`) REFERENCES `alunos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_td_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `turma_membros`
--
ALTER TABLE `turma_membros`
  ADD CONSTRAINT `fk_tm_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tm_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
