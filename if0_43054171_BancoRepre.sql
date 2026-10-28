-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql112.infinityfree.com
-- Tempo de geração: 28/10/2026 às 17:56
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
  `foto_url` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_login` datetime DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `alunos`
--

INSERT INTO `alunos` (`id`, `nome`, `email`, `senha_hash`, `tipo`, `status_conta`, `matricula`, `foto_url`, `ativo`, `ultimo_login`, `criado_em`, `atualizado_em`) VALUES
(1, 'Lula', 'lulala@teste.com', '$2y$12$Lhb5uG5JGR0FBFicUvCPa.LQZRWi6tNf69TCnvItNx83rhK5eR/tK', 'aluno', 'ativo', NULL, NULL, 1, '2026-09-30 13:33:50', '2026-09-30 12:09:14', '2026-09-30 13:33:50'),
(2, 'bolsonaro lixo', 'lixoso@teste.com', '$2y$12$xdRBz6Ly6Rsmw0Wcd82ewOGcNKScHIySQa/eN4Y/MepqpeRHcGhs.', 'aluno', 'ativo', NULL, NULL, 1, '2026-09-30 13:49:22', '2026-09-30 13:47:08', '2026-09-30 13:49:22'),
(4, 'augusto cury', 'drone@gmail.com', '$2y$12$slquBYcFfOmfMAsTERaEceiNEbCiH4EuUendM9g32LMjR4ANz7ujC', 'aluno', 'ativo', NULL, NULL, 1, '2026-10-01 12:24:20', '2026-09-30 14:05:58', '2026-10-01 12:24:20'),
(12, 'Matheus Camilo', 'camilomatheus0710@gmail.com', '$2y$12$pOjJrL4hxunZ/GjhHYdfJ.MD25Z60KhnEwVVesqe814VrYzH3iIB.', 'representante', 'ativo', NULL, '????\0JFIF\0\0H\0H\0\0???Exif\0\0MM\0*\0\0\0\0\0\0\0\0\0\0\0?\0\0\0\0\0\0\0?\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0?\0\0\0\0\0\0\0?(\0\0\0\0\0\0\01\0\0\0\0\0\0\0?2\0\0\0\0\0\0\0?<\0\0\0\0\0\0\0?\0\0\0\0\0\0\0?i\0\0\0\0\0\0\0??%\0\0\0\0\0\0\nd\0\0\0\0Apple\0iPhone 14 Pro Max\0\0\0\0H\0\0\0\0\0\0H\0\0\027.0.1\0\02026:10:18 08:27:42', 1, '2026-10-27 10:58:28', '2026-10-02 05:11:42', '2026-10-27 10:58:28'),
(13, 'Nicollas Gomes', '1213@nicollas.com', '$2y$12$.vOvrJtKgdNXkiDS7c0I7uGlffE3/bey7SEXWzPHy0lOV0AJ9PBE.', 'aluno', 'ativo', NULL, NULL, 1, '2026-10-05 08:06:31', '2026-10-05 08:05:43', '2026-10-05 08:06:31'),
(15, 'heloisa', 'heloisa@gmail.com', '$2y$12$0j8ojQqX/hcjqKAKhUFHpOlru9BapckFaYL/mwfKRjUo7luPuLUHC', 'aluno', 'ativo', NULL, NULL, 1, '2026-10-19 14:56:21', '2026-10-19 14:56:06', '2026-10-19 14:56:21'),
(16, 'Wilson', 'wilson@professor.com', 'wilson001', 'professor', 'ativo', 'PROF001', NULL, 1, NULL, '2026-10-20 13:35:20', '2026-10-20 13:42:03'),
(17, 'Renata', 'renata@professor.com', 'renata001', 'professor', 'ativo', 'PROF002', NULL, 1, NULL, '2026-10-20 13:36:16', '2026-10-20 13:41:46'),
(18, 'Cleybson', 'cleybson@professor.com', 'cleybson001', 'professor', 'ativo', 'PROF003', NULL, 1, NULL, '2026-10-20 13:36:49', '2026-10-20 13:43:43'),
(19, 'Lívia Marques', 'lili67555@gmail.com', '$2y$12$kpmYZpdW6..N6i0J88OMiu5SXq4lxnlFFGy91Tz.Px4tqsPYFsSC2', 'aluno', 'ativo', NULL, NULL, 1, '2026-10-23 05:22:19', '2026-10-23 05:21:06', '2026-10-23 05:22:19'),
(20, 'celia Regina', 'celia@gggg.com.br', '$2y$12$zqfXzuqMc.vWf1DYHpawa.Tdui8/i/5k2VZY4uTSDWX/ukP6Ju2U.', 'aluno', 'ativo', NULL, NULL, 1, '2026-10-23 05:31:38', '2026-10-23 05:30:44', '2026-10-23 05:31:38'),
(21, 'MAYARA DE OLIVEIRA ARAUJO', 'ogmaya453@gmail.com', '$2y$12$hccayet9YNqbHbtxuvD/Zu/iEp7Xwuib4BfvJXr346fte2nvVTspO', 'aluno', 'ativo', NULL, NULL, 1, '2026-10-28 12:12:02', '2026-10-23 09:39:20', '2026-10-28 12:12:02'),
(22, 'Heloisa Auletta', '1dsm@representante.com', '$2y$12$vuhuuQhLYG2VmY/35JUBs.ao/yVjqZXOwGN1hfa9pCUZKny0dVpVu', 'representante', 'ativo', NULL, NULL, 1, '2026-10-26 12:48:54', '2026-10-26 12:44:18', '2026-10-26 12:48:54'),
(23, 'Guilherme Cortez', '3dsm@representante.com', '$2y$12$hrohutZdexg.2wwLFligL.zAwdULGMzq2X/eAVoSY.E.3HQ1fnsdC', 'representante', 'ativo', NULL, NULL, 1, '2026-10-26 14:43:45', '2026-10-26 14:43:32', '2026-10-26 14:43:45'),
(24, 'Pedro Neves', 'pedro3dsm@representante.com', '$2y$12$.miMDd/k90K3FXrXPdUzH.m88xDbC.FtOoMxbds2eBIcq2wZ5U3n2', 'representante', 'ativo', NULL, NULL, 1, '2026-10-27 05:37:04', '2026-10-27 05:03:02', '2026-10-27 05:37:04'),
(25, 'Mayara Araujo', 'maira3dsm@representante.com', '$2y$12$KQ4l1LCZ7UONE.kcLD8H5OjEeWNc3fnT0x4IFgMwMzoETTXy4Zqm2', 'representante', 'ativo', NULL, NULL, 1, '2026-10-27 05:38:11', '2026-10-27 05:37:58', '2026-10-27 05:38:11'),
(26, 'guigo', 'guigo2adm@representante.com', '$2y$12$35cTQ0lq39Y5bdgTVrmLS..arfAmmRGGokpdfu01zARXJSInX3oHu', 'representante', 'ativo', NULL, NULL, 1, '2026-10-27 11:41:03', '2026-10-27 11:40:48', '2026-10-27 11:41:03'),
(27, 'Pedro Neves', 'pedro1dsm@representante.com', '$2y$12$tXizFzzeWnnVwAaq8jHB4.MXuBZvaGHMDIJx0p3cv74lCB.U5BasO', 'representante', 'ativo', NULL, NULL, 1, '2026-10-27 11:56:59', '2026-10-27 11:56:30', '2026-10-27 11:56:59'),
(28, 'Guilherme Cortez', 'guicortez3dsm@representante.com', '$2y$12$CQda9mDPSbBQ8wbgNWvSrexXYygtG8avF6IOzM3jDEauDoAs2ttnC', 'representante', 'ativo', NULL, NULL, 1, '2026-10-27 12:03:34', '2026-10-27 12:03:21', '2026-10-27 12:03:34'),
(29, 'Livia Pinehiro', 'livia@aluno.com', '$2y$12$QtwoV8G.2URonF2gQaJr/ejfes/K/glDJSfyHIwx.CWy0yL0cah6a', 'aluno', 'ativo', NULL, NULL, 1, '2026-10-27 12:25:55', '2026-10-27 12:19:54', '2026-10-27 12:25:55'),
(30, 'Heloisa Auletta', 'heloisa@aluno.com', '$2y$12$Yt2kCeDw/xtncgayphfjvOOLjyekfDGb5CZ1ENUcm0YDTklDanx96', 'aluno', 'ativo', NULL, NULL, 1, '2026-10-27 12:28:34', '2026-10-27 12:28:18', '2026-10-27 12:28:34');

-- --------------------------------------------------------

--
-- Estrutura para tabela `anexos`
--

CREATE TABLE `anexos` (
  `id` int(10) UNSIGNED NOT NULL,
  `atividade_id` int(10) UNSIGNED DEFAULT NULL,
  `entrega_id` int(10) UNSIGNED DEFAULT NULL,
  `mensagem_id` bigint(20) UNSIGNED DEFAULT NULL,
  `aviso_id` int(10) UNSIGNED DEFAULT NULL,
  `enviado_por` int(10) UNSIGNED NOT NULL,
  `nome_original` varchar(255) NOT NULL,
  `caminho` varchar(255) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `tamanho_bytes` bigint(20) UNSIGNED DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `atividades`
--

CREATE TABLE `atividades` (
  `id` int(10) UNSIGNED NOT NULL,
  `turma_disciplina_id` int(10) UNSIGNED NOT NULL,
  `periodo_id` int(10) UNSIGNED DEFAULT NULL,
  `criador_id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo` enum('tarefa','trabalho','prova','quiz','projeto','leitura') NOT NULL DEFAULT 'tarefa',
  `status` enum('rascunho','publicada','encerrada') NOT NULL DEFAULT 'rascunho',
  `em_grupo` tinyint(1) NOT NULL DEFAULT 0,
  `pontos` decimal(5,2) NOT NULL DEFAULT 10.00,
  `peso` decimal(4,2) NOT NULL DEFAULT 1.00,
  `permite_atraso` tinyint(1) NOT NULL DEFAULT 1,
  `publicada_em` datetime DEFAULT NULL,
  `data_limite` datetime DEFAULT NULL,
  `criada_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizada_em` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `atividades`
--

INSERT INTO `atividades` (`id`, `turma_disciplina_id`, `periodo_id`, `criador_id`, `titulo`, `descricao`, `tipo`, `status`, `em_grupo`, `pontos`, `peso`, `permite_atraso`, `publicada_em`, `data_limite`, `criada_em`, `atualizada_em`) VALUES
(20, 11, 1, 12, 'Pesquisa sobre Sustentabilidade', 'Pesquise sobre a importância da sustentabilidade, abordando reciclagem, economia de água e preservação do meio ambiente. Apresente exemplos de práticas sustentáveis no dia a dia.', 'trabalho', 'publicada', 1, '10.00', '1.00', 0, '2026-10-23 10:56:30', '2026-10-30 11:56:00', '2026-10-23 07:56:32', '2026-10-23 07:56:32'),
(21, 12, 2, 12, 'A importância da cultura brasileira', 'Pesquise sobre as principais manifestações culturais do Brasil, incluindo festas populares, danças, músicas e culinária. Escolha uma manifestação cultural e explique sua origem e importância para a sociedade.', 'projeto', 'publicada', 1, '10.00', '1.00', 0, '2026-10-23 10:57:22', '2026-10-31 11:57:00', '2026-10-23 07:57:23', '2026-10-23 07:57:23'),
(22, 10, 2, 12, 'Os impactos da tecnologia na sociedade', 'Elabore um trabalho sobre como a tecnologia influencia o cotidiano das pessoas. Aborde os benefícios e desafios do uso da internet, das redes sociais e da inteligência artificial na educação e no mercado de trabalho.', 'leitura', 'publicada', 1, '10.00', '1.00', 0, '2026-10-23 10:58:14', '2026-10-31 11:58:00', '2026-10-23 07:58:15', '2026-10-23 07:58:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `aulas`
--

CREATE TABLE `aulas` (
  `id` int(10) UNSIGNED NOT NULL,
  `turma_disciplina_id` int(10) UNSIGNED NOT NULL,
  `data_aula` date NOT NULL,
  `conteudo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `avisos`
--

CREATE TABLE `avisos` (
  `id` int(10) UNSIGNED NOT NULL,
  `turma_id` int(10) UNSIGNED DEFAULT NULL,
  `turma_disciplina_id` int(10) UNSIGNED DEFAULT NULL,
  `autor_id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `conteudo` text NOT NULL,
  `fixado` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `avisos`
--

INSERT INTO `avisos` (`id`, `turma_id`, `turma_disciplina_id`, `autor_id`, `titulo`, `conteudo`, `fixado`, `criado_em`) VALUES
(1, 1, NULL, 12, 'Prova de matemática', 'Prova de matemática surpresa ocorrerá semana que vem.', 1, '2026-10-05 14:45:51'),
(2, 1, NULL, 12, 'Aula do provão paulista', 'faz o l', 0, '2026-10-19 03:34:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `canais`
--

CREATE TABLE `canais` (
  `id` int(10) UNSIGNED NOT NULL,
  `turma_disciplina_id` int(10) UNSIGNED NOT NULL,
  `equipe_id` int(10) UNSIGNED DEFAULT NULL,
  `nome` varchar(80) NOT NULL,
  `tipo` enum('geral','avisos','duvidas','equipe') NOT NULL DEFAULT 'geral',
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `comentarios`
--

CREATE TABLE `comentarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `atividade_id` int(10) UNSIGNED DEFAULT NULL,
  `entrega_id` int(10) UNSIGNED DEFAULT NULL,
  `autor_id` int(10) UNSIGNED NOT NULL,
  `texto` text NOT NULL,
  `privado` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `criterios_avaliacao`
--

CREATE TABLE `criterios_avaliacao` (
  `id` int(10) UNSIGNED NOT NULL,
  `atividade_id` int(10) UNSIGNED NOT NULL,
  `descricao` varchar(200) NOT NULL,
  `pontos_max` decimal(5,2) NOT NULL
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
(1, 'Matemática', '#6040DB'),
(2, 'História', '#E6535C'),
(3, 'Inglês', '#3470D9');

-- --------------------------------------------------------

--
-- Estrutura para tabela `entregas`
--

CREATE TABLE `entregas` (
  `id` int(10) UNSIGNED NOT NULL,
  `atividade_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `equipe_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('pendente','entregue','atrasada','corrigida','devolvida') NOT NULL DEFAULT 'pendente',
  `texto` text DEFAULT NULL,
  `enviado_em` datetime DEFAULT NULL,
  `nota` decimal(5,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `corrigido_por` int(10) UNSIGNED DEFAULT NULL,
  `corrigido_em` datetime DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `entrega_criterios`
--

CREATE TABLE `entrega_criterios` (
  `entrega_id` int(10) UNSIGNED NOT NULL,
  `criterio_id` int(10) UNSIGNED NOT NULL,
  `pontos` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `equipes`
--

CREATE TABLE `equipes` (
  `id` int(10) UNSIGNED NOT NULL,
  `turma_disciplina_id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(80) NOT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `equipe_alunos`
--

CREATE TABLE `equipe_alunos` (
  `equipe_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `lider` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `eventos`
--

CREATE TABLE `eventos` (
  `id` int(10) UNSIGNED NOT NULL,
  `turma_id` int(10) UNSIGNED DEFAULT NULL,
  `atividade_id` int(10) UNSIGNED DEFAULT NULL,
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

INSERT INTO `eventos` (`id`, `turma_id`, `atividade_id`, `criador_id`, `titulo`, `descricao`, `tipo`, `inicio`, `fim`) VALUES
(1, 1, NULL, 12, 'Reposição de aula', 'hwduiawfhwah\\', 'aula', '2026-10-31 15:58:00', '2026-10-31 16:58:00'),
(2, 1, NULL, 12, 'Reunião de professores', 'eafwaef', 'reuniao', '2026-10-23 16:01:00', '2026-10-23 17:01:00'),
(3, 1, NULL, 12, 'Prova de matemática', 'fawef', 'prova', '2026-10-22 16:01:00', '2026-10-22 16:01:00'),
(5, 1, NULL, 12, 'dawda', 'efsefs', 'feriado', '2026-10-30 18:27:00', '2026-12-08 18:21:00'),
(6, 1, NULL, 12, 'Reunião de classe', 'Não tem aula', 'reuniao', '2026-10-22 07:38:00', '2026-10-23 07:39:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `log_auditoria`
--

CREATE TABLE `log_auditoria` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `acao` varchar(60) NOT NULL,
  `entidade` varchar(60) NOT NULL,
  `entidade_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `log_auditoria`
--

INSERT INTO `log_auditoria` (`id`, `usuario_id`, `acao`, `entidade`, `entidade_id`, `ip`, `criado_em`) VALUES
(1, 12, 'criar', 'atividade', 1, '187.58.18.176', '2026-10-02 06:26:33'),
(2, 12, 'criar', 'atividade', 2, '187.58.18.176', '2026-10-02 06:36:09'),
(3, 12, 'criar', 'atividade', 3, '187.58.18.176', '2026-10-02 06:37:19'),
(4, 12, 'criar', 'atividade', 4, '187.58.18.176', '2026-10-02 06:37:41'),
(5, 12, 'criar', 'atividade', 5, '187.58.18.176', '2026-10-02 06:37:45'),
(6, 12, 'criar', 'atividade', 6, '187.58.18.176', '2026-10-02 06:39:16'),
(7, 12, 'criar', 'atividade', 7, '187.58.18.176', '2026-10-02 06:42:26'),
(8, 12, 'criar', 'atividade', 8, '187.58.18.176', '2026-10-19 03:34:15'),
(9, 12, 'criar', 'atividade', 9, '200.212.209.122', '2026-10-20 13:58:15'),
(10, 12, 'criar', 'atividade', 10, '200.212.209.122', '2026-10-21 11:11:43'),
(11, 12, 'criar', 'atividade', 11, '200.212.209.122', '2026-10-21 14:27:41'),
(12, 12, 'criar', 'atividade', 12, '200.212.209.122', '2026-10-21 14:28:35'),
(13, 12, 'excluir', 'atividade', 9, '200.212.209.122', '2026-10-22 13:27:30'),
(14, 12, 'criar', 'atividade', 13, '200.212.209.122', '2026-10-22 14:09:48'),
(15, 12, 'criar', 'atividade', 14, '200.212.209.122', '2026-10-22 14:10:40'),
(16, 12, 'criar', 'atividade', 15, '200.212.209.122', '2026-10-22 14:11:25'),
(17, 12, 'criar', 'atividade', 16, '200.212.209.122', '2026-10-22 14:12:14'),
(18, 12, 'criar', 'atividade', 17, '200.212.209.122', '2026-10-22 14:12:34'),
(19, 12, 'criar', 'atividade', 18, '187.58.18.176', '2026-10-23 05:34:37'),
(20, 12, 'criar', 'atividade', 19, '187.58.18.176', '2026-10-23 05:35:34'),
(21, 12, 'excluir', 'atividade', 10, '187.58.18.176', '2026-10-23 07:53:50'),
(22, 12, 'excluir', 'atividade', 11, '187.58.18.176', '2026-10-23 07:53:54'),
(23, 12, 'excluir', 'atividade', 12, '187.58.18.176', '2026-10-23 07:53:58'),
(24, 12, 'excluir', 'atividade', 19, '187.58.18.176', '2026-10-23 07:54:02'),
(25, 12, 'excluir', 'atividade', 18, '187.58.18.176', '2026-10-23 07:54:04'),
(26, 12, 'excluir', 'atividade', 17, '187.58.18.176', '2026-10-23 07:54:07'),
(27, 12, 'excluir', 'atividade', 16, '187.58.18.176', '2026-10-23 07:54:09'),
(28, 12, 'excluir', 'atividade', 15, '187.58.18.176', '2026-10-23 07:54:12'),
(29, 12, 'excluir', 'atividade', 14, '187.58.18.176', '2026-10-23 07:54:15'),
(30, 12, 'excluir', 'atividade', 13, '187.58.18.176', '2026-10-23 07:54:17'),
(31, 12, 'criar', 'atividade', 20, '187.58.18.176', '2026-10-23 07:56:32'),
(32, 12, 'criar', 'atividade', 21, '187.58.18.176', '2026-10-23 07:57:23'),
(33, 12, 'criar', 'atividade', 22, '187.58.18.176', '2026-10-23 07:58:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `materiais_estudos`
--

CREATE TABLE `materiais_estudos` (
  `id` int(10) UNSIGNED NOT NULL,
  `turma_id` int(11) NOT NULL,
  `turma_disciplinas_id` int(11) NOT NULL,
  `publicado_por` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo` enum('pdf','documento','imagem','slide','link','outro') NOT NULL DEFAULT 'pdf',
  `link_url` varchar(2048) DEFAULT NULL,
  `arquivo_nome` varchar(255) DEFAULT NULL,
  `arquivo_caminho` varchar(500) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `materiais_estudos`
--

INSERT INTO `materiais_estudos` (`id`, `turma_id`, `turma_disciplinas_id`, `publicado_por`, `titulo`, `descricao`, `tipo`, `link_url`, `arquivo_nome`, `arquivo_caminho`, `criado_em`) VALUES
(3, 1, 3, 12, 'dawda', 'wqfqa', 'slide', NULL, 'caminhao-orange.png', 'uploads/materiais/d7c6b68eb73df8a1fb739e02994d89e4.png', '2026-10-15 21:21:14');

-- --------------------------------------------------------

--
-- Estrutura para tabela `mensagens`
--

CREATE TABLE `mensagens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `canal_id` int(10) UNSIGNED NOT NULL,
  `autor_id` int(10) UNSIGNED NOT NULL,
  `resposta_a_id` bigint(20) UNSIGNED DEFAULT NULL,
  `conteudo` text NOT NULL,
  `editada` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacoes`
--

CREATE TABLE `notificacoes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `tipo` enum('nova_atividade','nota','prazo','mensagem','aviso','sistema','atividade_atualizada','atividade_cancelada') NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `mensagem` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `lida` tinyint(1) NOT NULL DEFAULT 0,
  `criada_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `periodos_letivos`
--

CREATE TABLE `periodos_letivos` (
  `id` int(10) UNSIGNED NOT NULL,
  `ano` year(4) NOT NULL,
  `nome` varchar(40) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL
) ;

--
-- Despejando dados para a tabela `periodos_letivos`
--

INSERT INTO `periodos_letivos` (`id`, `ano`, `nome`, `data_inicio`, `data_fim`) VALUES
(1, 2026, '1º Bimestre', '2026-02-02', '2026-04-30'),
(2, 2026, '2º Bimestre', '2026-05-01', '2026-07-31'),
(3, 2026, '3º Bimestre', '2026-08-03', '2026-09-30'),
(4, 2026, '4º Bimestre', '2026-10-01', '2026-12-18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `presencas`
--

CREATE TABLE `presencas` (
  `aula_id` int(10) UNSIGNED NOT NULL,
  `aluno_id` int(10) UNSIGNED NOT NULL,
  `situacao` enum('presente','falta','justificada') NOT NULL DEFAULT 'presente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefas_pessoais`
--

CREATE TABLE `tarefas_pessoais` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `atividade_id` int(10) UNSIGNED DEFAULT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `coluna` enum('a_fazer','fazendo','concluida') NOT NULL DEFAULT 'a_fazer',
  `prioridade` enum('baixa','media','alta') NOT NULL DEFAULT 'media',
  `prazo` date DEFAULT NULL,
  `criada_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `codigo_convite` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `turmas`
--

INSERT INTO `turmas` (`id`, `nome`, `serie`, `turno`, `ano_letivo`, `ativa`, `codigo_convite`) VALUES
(1, '3° DS', 'Ensino Médio', 'manha', 2026, 1, NULL),
(5, '1° DSM', '1° ano', 'manha', 2026, 1, '474EFAA7'),
(6, '3° DSM', '3° ano', 'manha', 2026, 1, '05E5B8DE');

-- --------------------------------------------------------

--
-- Estrutura para tabela `turma_disciplinas`
--

CREATE TABLE `turma_disciplinas` (
  `id` int(10) UNSIGNED NOT NULL,
  `turma_id` int(10) UNSIGNED NOT NULL,
  `disciplina_id` int(10) UNSIGNED NOT NULL,
  `professor_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `turma_disciplinas`
--

INSERT INTO `turma_disciplinas` (`id`, `turma_id`, `disciplina_id`, `professor_id`) VALUES
(10, 1, 1, 16),
(11, 1, 2, 18),
(12, 1, 3, 17);

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
(1, 1, 'aluno', '2026-10-01 14:47:39'),
(1, 2, 'aluno', '2026-10-01 14:47:39'),
(1, 4, 'aluno', '2026-10-01 14:47:39'),
(1, 12, 'representante', '2026-10-02 06:13:41'),
(1, 19, 'aluno', '2026-10-23 05:23:44'),
(1, 20, 'aluno', '2026-10-23 05:31:15'),
(5, 27, 'representante', '2026-10-27 12:01:27'),
(6, 28, 'representante', '2026-10-27 12:03:53'),
(6, 29, 'aluno', '2026-10-27 12:20:38'),
(6, 30, 'aluno', '2026-10-27 12:28:44');

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
-- Índices de tabela `anexos`
--
ALTER TABLE `anexos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `atividade_id` (`atividade_id`),
  ADD KEY `entrega_id` (`entrega_id`),
  ADD KEY `mensagem_id` (`mensagem_id`),
  ADD KEY `aviso_id` (`aviso_id`),
  ADD KEY `enviado_por` (`enviado_por`);

--
-- Índices de tabela `atividades`
--
ALTER TABLE `atividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ativ_limite` (`data_limite`),
  ADD KEY `idx_ativ_td` (`turma_disciplina_id`,`status`),
  ADD KEY `periodo_id` (`periodo_id`),
  ADD KEY `criador_id` (`criador_id`);

--
-- Índices de tabela `aulas`
--
ALTER TABLE `aulas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_aula` (`turma_disciplina_id`,`data_aula`);

--
-- Índices de tabela `avisos`
--
ALTER TABLE `avisos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `turma_id` (`turma_id`),
  ADD KEY `turma_disciplina_id` (`turma_disciplina_id`),
  ADD KEY `autor_id` (`autor_id`);

--
-- Índices de tabela `canais`
--
ALTER TABLE `canais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `turma_disciplina_id` (`turma_disciplina_id`),
  ADD KEY `equipe_id` (`equipe_id`);

--
-- Índices de tabela `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `atividade_id` (`atividade_id`),
  ADD KEY `entrega_id` (`entrega_id`),
  ADD KEY `autor_id` (`autor_id`);

--
-- Índices de tabela `criterios_avaliacao`
--
ALTER TABLE `criterios_avaliacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `atividade_id` (`atividade_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_entrega` (`atividade_id`,`aluno_id`),
  ADD KEY `idx_entrega_aluno` (`aluno_id`,`status`),
  ADD KEY `equipe_id` (`equipe_id`),
  ADD KEY `corrigido_por` (`corrigido_por`);

--
-- Índices de tabela `entrega_criterios`
--
ALTER TABLE `entrega_criterios`
  ADD PRIMARY KEY (`entrega_id`,`criterio_id`),
  ADD KEY `criterio_id` (`criterio_id`);

--
-- Índices de tabela `equipes`
--
ALTER TABLE `equipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `turma_disciplina_id` (`turma_disciplina_id`);

--
-- Índices de tabela `equipe_alunos`
--
ALTER TABLE `equipe_alunos`
  ADD PRIMARY KEY (`equipe_id`,`aluno_id`),
  ADD KEY `aluno_id` (`aluno_id`);

--
-- Índices de tabela `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_evento_inicio` (`inicio`),
  ADD KEY `turma_id` (`turma_id`),
  ADD KEY `atividade_id` (`atividade_id`),
  ADD KEY `criador_id` (`criador_id`);

--
-- Índices de tabela `log_auditoria`
--
ALTER TABLE `log_auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `materiais_estudos`
--
ALTER TABLE `materiais_estudos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_materiais_turma_data` (`turma_id`,`criado_em`),
  ADD KEY `idx_materiais_disciplina` (`turma_disciplinas_id`),
  ADD KEY `idx_materiais_publicados_por` (`publicado_por`);

--
-- Índices de tabela `mensagens`
--
ALTER TABLE `mensagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_msg_canal` (`canal_id`,`criado_em`),
  ADD KEY `autor_id` (`autor_id`),
  ADD KEY `resposta_a_id` (`resposta_a_id`);

--
-- Índices de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notif_usuario` (`usuario_id`,`lida`,`criada_em`);

--
-- Índices de tabela `periodos_letivos`
--
ALTER TABLE `periodos_letivos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_periodo` (`ano`,`nome`);

--
-- Índices de tabela `presencas`
--
ALTER TABLE `presencas`
  ADD PRIMARY KEY (`aula_id`,`aluno_id`),
  ADD KEY `aluno_id` (`aluno_id`);

--
-- Índices de tabela `tarefas_pessoais`
--
ALTER TABLE `tarefas_pessoais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `atividade_id` (`atividade_id`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `anexos`
--
ALTER TABLE `anexos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `atividades`
--
ALTER TABLE `atividades`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `aulas`
--
ALTER TABLE `aulas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `avisos`
--
ALTER TABLE `avisos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `canais`
--
ALTER TABLE `canais`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `criterios_avaliacao`
--
ALTER TABLE `criterios_avaliacao`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `disciplinas`
--
ALTER TABLE `disciplinas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `entregas`
--
ALTER TABLE `entregas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `equipes`
--
ALTER TABLE `equipes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `log_auditoria`
--
ALTER TABLE `log_auditoria`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de tabela `materiais_estudos`
--
ALTER TABLE `materiais_estudos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `mensagens`
--
ALTER TABLE `mensagens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `periodos_letivos`
--
ALTER TABLE `periodos_letivos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tarefas_pessoais`
--
ALTER TABLE `tarefas_pessoais`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `turmas`
--
ALTER TABLE `turmas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `turma_disciplinas`
--
ALTER TABLE `turma_disciplinas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restrições para dumps de tabelas
--

--
-- Restrições para tabelas `anexos`
--
ALTER TABLE `anexos`
  ADD CONSTRAINT `anexos_ibfk_1` FOREIGN KEY (`atividade_id`) REFERENCES `atividades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anexos_ibfk_2` FOREIGN KEY (`entrega_id`) REFERENCES `entregas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anexos_ibfk_3` FOREIGN KEY (`mensagem_id`) REFERENCES `mensagens` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anexos_ibfk_4` FOREIGN KEY (`aviso_id`) REFERENCES `avisos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anexos_ibfk_5` FOREIGN KEY (`enviado_por`) REFERENCES `alunos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `atividades`
--
ALTER TABLE `atividades`
  ADD CONSTRAINT `atividades_ibfk_1` FOREIGN KEY (`turma_disciplina_id`) REFERENCES `turma_disciplinas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `atividades_ibfk_2` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_letivos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `atividades_ibfk_3` FOREIGN KEY (`criador_id`) REFERENCES `alunos` (`id`);

--
-- Restrições para tabelas `aulas`
--
ALTER TABLE `aulas`
  ADD CONSTRAINT `aulas_ibfk_1` FOREIGN KEY (`turma_disciplina_id`) REFERENCES `turma_disciplinas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `avisos`
--
ALTER TABLE `avisos`
  ADD CONSTRAINT `avisos_ibfk_1` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avisos_ibfk_2` FOREIGN KEY (`turma_disciplina_id`) REFERENCES `turma_disciplinas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avisos_ibfk_3` FOREIGN KEY (`autor_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `canais`
--
ALTER TABLE `canais`
  ADD CONSTRAINT `canais_ibfk_1` FOREIGN KEY (`turma_disciplina_id`) REFERENCES `turma_disciplinas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `canais_ibfk_2` FOREIGN KEY (`equipe_id`) REFERENCES `equipes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`atividade_id`) REFERENCES `atividades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comentarios_ibfk_2` FOREIGN KEY (`entrega_id`) REFERENCES `entregas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comentarios_ibfk_3` FOREIGN KEY (`autor_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `criterios_avaliacao`
--
ALTER TABLE `criterios_avaliacao`
  ADD CONSTRAINT `criterios_avaliacao_ibfk_1` FOREIGN KEY (`atividade_id`) REFERENCES `atividades` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `entregas`
--
ALTER TABLE `entregas`
  ADD CONSTRAINT `entregas_ibfk_1` FOREIGN KEY (`atividade_id`) REFERENCES `atividades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `entregas_ibfk_2` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `entregas_ibfk_3` FOREIGN KEY (`equipe_id`) REFERENCES `equipes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `entregas_ibfk_4` FOREIGN KEY (`corrigido_por`) REFERENCES `alunos` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `entrega_criterios`
--
ALTER TABLE `entrega_criterios`
  ADD CONSTRAINT `entrega_criterios_ibfk_1` FOREIGN KEY (`entrega_id`) REFERENCES `entregas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `entrega_criterios_ibfk_2` FOREIGN KEY (`criterio_id`) REFERENCES `criterios_avaliacao` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `equipes`
--
ALTER TABLE `equipes`
  ADD CONSTRAINT `equipes_ibfk_1` FOREIGN KEY (`turma_disciplina_id`) REFERENCES `turma_disciplinas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `equipe_alunos`
--
ALTER TABLE `equipe_alunos`
  ADD CONSTRAINT `equipe_alunos_ibfk_1` FOREIGN KEY (`equipe_id`) REFERENCES `equipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipe_alunos_ibfk_2` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventos_ibfk_2` FOREIGN KEY (`atividade_id`) REFERENCES `atividades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventos_ibfk_3` FOREIGN KEY (`criador_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `log_auditoria`
--
ALTER TABLE `log_auditoria`
  ADD CONSTRAINT `log_auditoria_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `alunos` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `mensagens`
--
ALTER TABLE `mensagens`
  ADD CONSTRAINT `mensagens_ibfk_1` FOREIGN KEY (`canal_id`) REFERENCES `canais` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensagens_ibfk_2` FOREIGN KEY (`autor_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensagens_ibfk_3` FOREIGN KEY (`resposta_a_id`) REFERENCES `mensagens` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD CONSTRAINT `notificacoes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `presencas`
--
ALTER TABLE `presencas`
  ADD CONSTRAINT `presencas_ibfk_1` FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `presencas_ibfk_2` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tarefas_pessoais`
--
ALTER TABLE `tarefas_pessoais`
  ADD CONSTRAINT `tarefas_pessoais_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tarefas_pessoais_ibfk_2` FOREIGN KEY (`atividade_id`) REFERENCES `atividades` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `turma_disciplinas`
--
ALTER TABLE `turma_disciplinas`
  ADD CONSTRAINT `turma_disciplinas_ibfk_1` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `turma_disciplinas_ibfk_2` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`),
  ADD CONSTRAINT `turma_disciplinas_ibfk_3` FOREIGN KEY (`professor_id`) REFERENCES `alunos` (`id`);

--
-- Restrições para tabelas `turma_membros`
--
ALTER TABLE `turma_membros`
  ADD CONSTRAINT `turma_membros_ibfk_1` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `turma_membros_ibfk_2` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
