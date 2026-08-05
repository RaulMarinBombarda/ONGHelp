-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 08/06/2026 às 23:54
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `onghelp`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentacoes`
--

CREATE TABLE `movimentacoes` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `tipo` enum('receita','despesa') NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(12,2) NOT NULL,
  `data_mov` date NOT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `movimentacoes`
--

INSERT INTO `movimentacoes` (`id`, `usuario_id`, `tipo`, `descricao`, `valor`, `data_mov`, `criado_em`) VALUES
(201, 24, 'receita', 'Doação empresa local', 2500.00, '2024-02-10', '2026-06-08 17:00:42'),
(202, 24, 'receita', 'Doação mensal padrinhos', 1800.00, '2024-03-05', '2026-06-08 17:00:42'),
(203, 24, 'receita', 'Evento beneficente', 3200.00, '2024-05-20', '2026-06-08 17:00:42'),
(204, 24, 'despesa', 'Material didático', 890.00, '2024-03-15', '2026-06-08 17:00:42'),
(205, 24, 'despesa', 'Contas de água/luz', 410.00, '2024-04-01', '2026-06-08 17:00:42'),
(206, 24, 'despesa', 'Salário professores', 3500.00, '2024-04-05', '2026-06-08 17:00:42'),
(207, 24, 'receita', 'Doação recorrente', 1500.00, '2025-01-10', '2026-06-08 17:00:42'),
(208, 24, 'receita', 'Kit escolar doação', 2100.00, '2025-02-18', '2026-06-08 17:00:42'),
(209, 24, 'despesa', 'Manutenção sede', 620.00, '2025-01-20', '2026-06-08 17:00:42'),
(210, 24, 'despesa', 'Material pedagógico', 750.00, '2025-03-02', '2026-06-08 17:00:42'),
(211, 25, 'receita', 'Convênio Prefeitura', 5000.00, '2024-01-15', '2026-06-08 17:00:42'),
(212, 25, 'receita', 'Doação de medicamentos', 1200.00, '2024-03-22', '2026-06-08 17:00:42'),
(213, 25, 'receita', 'Doação campanha', 2800.00, '2024-06-10', '2026-06-08 17:00:42'),
(214, 25, 'despesa', 'Compra de vacinas', 3200.00, '2024-02-01', '2026-06-08 17:00:42'),
(215, 25, 'despesa', 'Material hospitalar', 980.00, '2024-04-18', '2026-06-08 17:00:42'),
(216, 25, 'despesa', 'Transporte pacientes', 450.00, '2024-05-25', '2026-06-08 17:00:42'),
(217, 25, 'receita', 'Doação pessoa física', 1600.00, '2025-01-20', '2026-06-08 17:00:42'),
(218, 25, 'despesa', 'Equipamentos', 2400.00, '2025-02-10', '2026-06-08 17:00:42'),
(219, 25, 'receita', 'Subvenção municipal', 3500.00, '2025-03-05', '2026-06-08 17:00:42'),
(220, 25, 'despesa', 'Medicamentos', 1800.00, '2025-03-20', '2026-06-08 17:00:42'),
(221, 26, 'receita', 'Feira de adoção', 1500.00, '2024-02-08', '2026-06-08 17:00:43'),
(222, 26, 'receita', 'Doação de apoiadores', 2300.00, '2024-04-12', '2026-06-08 17:00:43'),
(223, 26, 'receita', 'Bazar beneficente', 980.00, '2024-07-05', '2026-06-08 17:00:43'),
(224, 26, 'despesa', 'Ração e medicamentos', 2100.00, '2024-03-01', '2026-06-08 17:00:43'),
(225, 26, 'despesa', 'Castração', 1800.00, '2024-05-15', '2026-06-08 17:00:43'),
(226, 26, 'despesa', 'Contas veterinário', 750.00, '2024-06-20', '2026-06-08 17:00:43'),
(227, 26, 'receita', 'Doação campanha', 1900.00, '2025-01-25', '2026-06-08 17:00:43'),
(228, 26, 'despesa', 'Manutenção abrigo', 520.00, '2025-02-05', '2026-06-08 17:00:43'),
(229, 26, 'receita', 'Doação mensal', 1200.00, '2025-03-10', '2026-06-08 17:00:43'),
(230, 26, 'despesa', 'Cirurgias', 2500.00, '2025-03-15', '2026-06-08 17:00:43'),
(231, 27, 'receita', 'Doação empresa', 3000.00, '2024-02-20', '2026-06-08 17:00:43'),
(232, 27, 'receita', 'Edital ambiental', 5000.00, '2024-04-10', '2026-06-08 17:00:43'),
(233, 27, 'receita', 'Doação pessoas físicas', 1500.00, '2024-06-05', '2026-06-08 17:00:43'),
(234, 27, 'despesa', 'Mudas e insumos', 2200.00, '2024-03-15', '2026-06-08 17:00:43'),
(235, 27, 'despesa', 'Transporte voluntários', 680.00, '2024-05-20', '2026-06-08 17:00:43'),
(236, 27, 'despesa', 'Equipamentos campo', 1600.00, '2024-07-01', '2026-06-08 17:00:43'),
(237, 27, 'receita', 'Doação recorrente', 1800.00, '2025-01-10', '2026-06-08 17:00:43'),
(238, 27, 'despesa', 'Manutenção viveiro', 420.00, '2025-02-15', '2026-06-08 17:00:43'),
(239, 27, 'receita', 'Parceria empresa', 2500.00, '2025-03-01', '2026-06-08 17:00:43'),
(240, 27, 'despesa', 'Material educativo', 350.00, '2025-03-20', '2026-06-08 17:00:43'),
(241, 28, 'receita', 'Doação igreja parceira', 3500.00, '2024-01-20', '2026-06-08 17:00:43'),
(242, 28, 'receita', 'Campanha Natal', 4200.00, '2024-12-10', '2026-06-08 17:00:43'),
(243, 28, 'receita', 'Doação empresas', 2800.00, '2024-06-15', '2026-06-08 17:00:43'),
(244, 28, 'despesa', 'Cestas básicas', 3800.00, '2024-02-01', '2026-06-08 17:00:43'),
(245, 28, 'despesa', 'Aluguel galpão', 1200.00, '2024-03-05', '2026-06-08 17:00:43'),
(246, 28, 'despesa', 'Contas água/luz', 380.00, '2024-04-10', '2026-06-08 17:00:43'),
(247, 28, 'receita', 'Doação Páscoa', 2100.00, '2025-03-25', '2026-06-08 17:00:43'),
(248, 28, 'despesa', 'Cestas básicas', 4100.00, '2025-01-15', '2026-06-08 17:00:43'),
(249, 28, 'receita', 'Doação mensal', 1600.00, '2025-02-10', '2026-06-08 17:00:43'),
(250, 28, 'despesa', 'Material cozinha', 520.00, '2025-03-05', '2026-06-08 17:00:43'),
(251, 29, 'receita', 'Edital ProAC', 5000.00, '2024-03-01', '2026-06-08 17:00:43'),
(252, 29, 'receita', 'Doação apoiadores', 1200.00, '2024-05-20', '2026-06-08 17:00:43'),
(253, 29, 'receita', 'Apresentação cultural', 1800.00, '2024-08-15', '2026-06-08 17:00:43'),
(254, 29, 'despesa', 'Instrumentos musicais', 2500.00, '2024-04-10', '2026-06-08 17:00:43'),
(255, 29, 'despesa', 'Material artes cênicas', 620.00, '2024-06-05', '2026-06-08 17:00:43'),
(256, 29, 'despesa', 'Professor oficineiro', 1800.00, '2024-07-01', '2026-06-08 17:00:43'),
(257, 29, 'receita', 'Doação recorrente', 1500.00, '2025-01-15', '2026-06-08 17:00:43'),
(258, 29, 'despesa', 'Manutenção sede', 480.00, '2025-02-10', '2026-06-08 17:00:43'),
(259, 29, 'receita', 'Patrocínio empresa', 2000.00, '2025-03-01', '2026-06-08 17:00:43'),
(260, 29, 'despesa', 'Figurinos e adereços', 350.00, '2025-03-20', '2026-06-08 17:00:43'),
(261, 30, 'receita', 'Doação empresa esportiva', 2800.00, '2024-02-10', '2026-06-08 17:00:43'),
(262, 30, 'receita', 'Apoio Prefeitura', 3500.00, '2024-04-05', '2026-06-08 17:00:43'),
(263, 30, 'receita', 'Campeonato beneficente', 1800.00, '2024-07-20', '2026-06-08 17:00:43'),
(264, 30, 'despesa', 'Uniforme e chuteiras', 1600.00, '2024-03-01', '2026-06-08 17:00:43'),
(265, 30, 'despesa', 'Aluguel quadra', 900.00, '2024-05-10', '2026-06-08 17:00:43'),
(266, 30, 'despesa', 'Transporte atletas', 450.00, '2024-06-15', '2026-06-08 17:00:43'),
(267, 30, 'receita', 'Doação padrinhos', 1200.00, '2025-01-20', '2026-06-08 17:00:43'),
(268, 30, 'despesa', 'Material esportivo', 780.00, '2025-02-10', '2026-06-08 17:00:43'),
(269, 30, 'receita', 'Evento esportivo', 2200.00, '2025-03-05', '2026-06-08 17:00:43'),
(270, 30, 'despesa', 'Árbitros e premiações', 520.00, '2025-03-25', '2026-06-08 17:00:43'),
(271, 31, 'receita', 'Doação campanha castração', 3500.00, '2024-01-15', '2026-06-08 17:00:43'),
(272, 31, 'receita', 'Bazar beneficente', 1200.00, '2024-03-22', '2026-06-08 17:00:43'),
(273, 31, 'receita', 'Doação empresa parceira', 2800.00, '2024-05-10', '2026-06-08 17:00:43'),
(274, 31, 'despesa', 'Castração em massa', 4200.00, '2024-02-01', '2026-06-08 17:00:43'),
(275, 31, 'despesa', 'Ração e medicamentos', 2300.00, '2024-04-15', '2026-06-08 17:00:43'),
(276, 31, 'despesa', 'Cirurgias emergenciais', 1500.00, '2024-06-20', '2026-06-08 17:00:43'),
(277, 31, 'receita', 'Doação padrinhos', 1800.00, '2025-01-10', '2026-06-08 17:00:43'),
(278, 31, 'receita', 'Vaquinha online', 2100.00, '2025-02-25', '2026-06-08 17:00:43'),
(279, 31, 'despesa', 'Manutenção abrigo', 680.00, '2025-02-05', '2026-06-08 17:00:43'),
(280, 31, 'despesa', 'Tratamento veterinário', 2800.00, '2025-03-15', '2026-06-08 17:00:43'),
(281, 24, 'receita', 'Convênio Prefeitura Municipal', 8000.00, '2026-02-01', '2026-06-08 17:03:43'),
(282, 24, 'receita', 'Doação campanha matrículas', 3500.00, '2026-03-15', '2026-06-08 17:03:43'),
(283, 24, 'receita', 'Evento Dia das Crianças', 2800.00, '2026-10-12', '2026-06-08 17:03:43'),
(284, 24, 'despesa', 'Aluguel novo núcleo', 1500.00, '2026-02-10', '2026-06-08 17:03:43'),
(285, 24, 'despesa', 'Reforma sala de aula', 4200.00, '2026-03-01', '2026-06-08 17:03:43'),
(286, 24, 'despesa', 'Salário novos professores', 5600.00, '2026-04-05', '2026-06-08 17:03:43'),
(287, 24, 'despesa', 'Material didático ampliado', 1200.00, '2026-04-15', '2026-06-08 17:03:43'),
(288, 24, 'despesa', 'Frota transporte alunos', 2800.00, '2026-08-20', '2026-06-08 17:03:43'),
(289, 25, 'receita', 'Subvenção estadual saúde', 6000.00, '2026-01-15', '2026-06-08 17:03:43'),
(290, 25, 'receita', 'Doação laboratório parceiro', 4000.00, '2026-03-10', '2026-06-08 17:03:43'),
(291, 25, 'receita', 'Campanha empresarial', 2200.00, '2026-05-20', '2026-06-08 17:03:43'),
(292, 25, 'despesa', 'Compra repelentes e insumos', 3500.00, '2026-02-01', '2026-06-08 17:03:43'),
(293, 25, 'despesa', 'Carro de som e divulgação', 1800.00, '2026-02-20', '2026-06-08 17:03:43'),
(294, 25, 'despesa', 'Mutirão exames', 4200.00, '2026-04-10', '2026-06-08 17:03:43'),
(295, 25, 'despesa', 'Equipamentos nebulização', 2900.00, '2026-04-25', '2026-06-08 17:03:43'),
(296, 25, 'despesa', 'Agentes comunitários', 2600.00, '2026-06-01', '2026-06-08 17:03:43'),
(297, 26, 'receita', 'Parceria clínicas veterinárias', 4500.00, '2026-01-20', '2026-06-08 17:03:43'),
(298, 26, 'receita', 'Doação pet shops parceiros', 2800.00, '2026-04-10', '2026-06-08 17:03:43'),
(299, 26, 'receita', 'Rifas e eventos', 1800.00, '2026-07-05', '2026-06-08 17:03:43'),
(300, 26, 'receita', 'Campanha apadrinhamento', 3200.00, '2026-09-15', '2026-06-08 17:03:43'),
(301, 26, 'despesa', 'Compra ração atacado', 3800.00, '2026-02-01', '2026-06-08 17:03:43'),
(302, 26, 'despesa', 'Ampliação canil', 5000.00, '2026-05-10', '2026-06-08 17:03:43'),
(303, 26, 'despesa', 'Vacinas em massa', 2100.00, '2026-06-20', '2026-06-08 17:03:43'),
(304, 26, 'despesa', 'Medicamentos contínuos', 1400.00, '2026-08-01', '2026-06-08 17:03:43'),
(305, 27, 'receita', 'Edital Estadual Meio Ambiente', 6500.00, '2026-02-10', '2026-06-08 17:03:44'),
(306, 27, 'receita', 'Doação empresa reflorestamento', 3500.00, '2026-04-05', '2026-06-08 17:03:44'),
(307, 27, 'receita', 'Curso educação ambiental', 1800.00, '2026-08-20', '2026-06-08 17:03:44'),
(308, 27, 'despesa', 'Material educativo escolas', 1200.00, '2026-03-01', '2026-06-08 17:03:44'),
(309, 27, 'despesa', 'Mudas nativas', 2800.00, '2026-05-15', '2026-06-08 17:03:44'),
(310, 27, 'despesa', 'Transporte voluntariado', 1800.00, '2026-06-10', '2026-06-08 17:03:44'),
(311, 27, 'despesa', 'Trator e preparo solo', 3500.00, '2026-07-01', '2026-06-08 17:03:44'),
(312, 27, 'despesa', 'Manutenção viveiro anual', 950.00, '2026-09-05', '2026-06-08 17:03:44'),
(313, 28, 'receita', 'Campanha emergencial enchentes', 7000.00, '2026-01-10', '2026-06-08 17:03:44'),
(314, 28, 'receita', 'Doação Defesa Civil', 5200.00, '2026-01-25', '2026-06-08 17:03:44'),
(315, 28, 'receita', 'Empresas parceiras', 3000.00, '2026-03-15', '2026-06-08 17:03:44'),
(316, 28, 'despesa', 'Cestas básicas emergenciais', 5800.00, '2026-02-01', '2026-06-08 17:03:44'),
(317, 28, 'despesa', 'Colchões e cobertores', 2800.00, '2026-02-10', '2026-06-08 17:03:44'),
(318, 28, 'despesa', 'Kit higiene e limpeza', 2000.00, '2026-02-20', '2026-06-08 17:03:44'),
(319, 28, 'despesa', 'Aluguel caminhão entrega', 1200.00, '2026-03-01', '2026-06-08 17:03:44'),
(320, 28, 'despesa', 'Equipe voluntária alimentação', 2400.00, '2026-03-10', '2026-06-08 17:03:44'),
(321, 29, 'receita', 'Patrocínio Festival Cultural', 6000.00, '2026-04-10', '2026-06-08 17:03:44'),
(322, 29, 'receita', 'Venda ingressos festival', 4800.00, '2026-05-20', '2026-06-08 17:03:44'),
(323, 29, 'receita', 'Edital de circulação', 3500.00, '2026-08-01', '2026-06-08 17:03:44'),
(324, 29, 'despesa', 'Aluguel som e luz', 3200.00, '2026-05-01', '2026-06-08 17:03:44'),
(325, 29, 'despesa', 'Cachet artistas', 5000.00, '2026-05-15', '2026-06-08 17:03:44'),
(326, 29, 'despesa', 'Divulgação e marketing', 1800.00, '2026-04-25', '2026-06-08 17:03:44'),
(327, 29, 'despesa', 'Transporte equipamentos', 1200.00, '2026-05-10', '2026-06-08 17:03:44'),
(328, 29, 'despesa', 'Seguro evento', 600.00, '2026-05-18', '2026-06-08 17:03:44'),
(329, 30, 'receita', 'Patrocínio loja esportiva', 5000.00, '2026-02-10', '2026-06-08 17:03:44'),
(330, 30, 'receita', 'Inscrição campeonatos', 1800.00, '2026-04-05', '2026-06-08 17:03:44'),
(331, 30, 'receita', 'Prefeitura — Lei de Incentivo', 4200.00, '2026-06-15', '2026-06-08 17:03:44'),
(332, 30, 'receita', 'Venda uniformes', 2200.00, '2026-08-20', '2026-06-08 17:03:44'),
(333, 30, 'despesa', 'Inscrição federação', 1500.00, '2026-03-01', '2026-06-08 17:03:44'),
(334, 30, 'despesa', 'Transporte delegação', 3500.00, '2026-05-10', '2026-06-08 17:03:44'),
(335, 30, 'despesa', 'Equipamentos competição', 2800.00, '2026-06-01', '2026-06-08 17:03:44'),
(336, 30, 'despesa', 'Medalhas e premiação', 600.00, '2026-09-15', '2026-06-08 17:03:44'),
(337, 31, 'receita', 'Campanha Reforma Abrigo', 6500.00, '2026-01-10', '2026-06-08 17:03:44'),
(338, 31, 'receita', 'Doação clínica parceira', 3500.00, '2026-03-20', '2026-06-08 17:03:44'),
(339, 31, 'receita', 'Evento beneficente anual', 4200.00, '2026-06-15', '2026-06-08 17:03:44'),
(340, 31, 'receita', 'Empresa ração — parceria', 2800.00, '2026-09-01', '2026-06-08 17:03:44'),
(341, 31, 'despesa', 'Material construção reforma', 5500.00, '2026-02-01', '2026-06-08 17:03:44'),
(342, 31, 'despesa', 'Mão de obra reforma', 4000.00, '2026-02-15', '2026-06-08 17:03:44'),
(343, 31, 'despesa', 'Compra de canis novos', 3200.00, '2026-03-01', '2026-06-08 17:03:44'),
(344, 31, 'despesa', 'Castração campanha', 2800.00, '2026-07-10', '2026-06-08 17:03:44'),
(345, 31, 'despesa', 'Ração e medicamentos', 2500.00, '2026-08-05', '2026-06-08 17:03:44'),
(346, 31, 'despesa', 'Taxas e licenças', 600.00, '2026-09-20', '2026-06-08 17:03:44');

-- --------------------------------------------------------

--
-- Estrutura para tabela `projetos`
--

CREATE TABLE `projetos` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(160) NOT NULL,
  `indicador` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `unidade` varchar(80) NOT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `projetos`
--

INSERT INTO `projetos` (`id`, `usuario_id`, `nome`, `indicador`, `unidade`, `criado_em`) VALUES
(19, 24, 'Reforço Escolar', 120, 'crianças atendidas', '2026-06-08 17:00:42'),
(20, 24, 'Biblioteca Comunitária', 800, 'livros arrecadados', '2026-06-08 17:00:42'),
(21, 25, 'Mutirão de Vacinação', 450, 'doses aplicadas', '2026-06-08 17:00:42'),
(22, 25, 'Consultas Gratuitas', 280, 'atendimentos realizados', '2026-06-08 17:00:42'),
(23, 26, 'Castração Móvel', 180, 'animais castrados', '2026-06-08 17:00:42'),
(24, 26, 'Feira de Adoção', 42, 'animais adotados', '2026-06-08 17:00:42'),
(25, 27, 'Reflorestamento Urbano', 850, 'mudas plantadas', '2026-06-08 17:00:42'),
(26, 27, 'Preservação de Nascentes', 6, 'nascentes monitoradas', '2026-06-08 17:00:42'),
(27, 28, 'Cestas Básicas', 320, 'famílias atendidas', '2026-06-08 17:00:42'),
(28, 28, 'Café da Manhã Solidário', 1800, 'refeições servidas', '2026-06-08 17:00:42'),
(29, 29, 'Orquestra Jovem', 60, 'alunos matriculados', '2026-06-08 17:00:42'),
(30, 29, 'Oficina de Teatro', 35, 'alunos matriculados', '2026-06-08 17:00:42'),
(31, 30, 'Futebol de Base', 90, 'crianças atendidas', '2026-06-08 17:00:42'),
(32, 30, 'Atletismo', 45, 'crianças atendidas', '2026-06-08 17:00:42'),
(33, 31, 'Resgate e Recuperação', 120, 'animais resgatados', '2026-06-08 17:00:42'),
(34, 31, 'Campanha de Castração', 300, 'animais castrados', '2026-06-08 17:00:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(160) NOT NULL,
  `cnpj` varchar(20) NOT NULL,
  `email` varchar(160) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `area` varchar(60) NOT NULL,
  `emoji` varchar(8) NOT NULL DEFAULT '?',
  `cidade` varchar(100) NOT NULL DEFAULT 'Brasil',
  `estado` char(2) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `pix_chave` varchar(160) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `cnpj`, `email`, `senha`, `area`, `emoji`, `cidade`, `estado`, `descricao`, `pix_chave`, `ativo`, `criado_em`) VALUES
(24, 'Instituto Educar Taquaritinga', '12.345.678/0001-90', 'contato@institutoeducar.org', '$2y$10$B6dN7mxeDx1bkmJ.QJKO.OMWmYVpS4r.Xyd39K1iwamyVAt27lt2C', 'Educação', '🏢', 'Taquaritinga', 'SP', 'Reforço escolar e educação complementar para crianças e adolescentes em situação de vulnerabilidade.', 'contato@institutoeducar.org', 1, '2026-06-08 16:58:07'),
(25, 'Saúde Sem Fronteiras', '23.456.789/0001-01', 'contato@saudesemfronteiras.org', '$2y$10$B6dN7mxeDx1bkmJ.QJKO.OMWmYVpS4r.Xyd39K1iwamyVAt27lt2C', 'Saúde', '🏢', 'Taquaritinga', 'SP', 'Promoção de campanhas de saúde preventiva e atendimento ambulatorial gratuito.', 'contato@saudesemfronteiras.org', 1, '2026-06-08 16:58:07'),
(26, 'Patas Solidárias', '34.567.890/0001-12', 'contato@patassolidarias.org', '$2y$10$B6dN7mxeDx1bkmJ.QJKO.OMWmYVpS4r.Xyd39K1iwamyVAt27lt2C', 'Animais', '🏢', 'Taquaritinga', 'SP', 'Resgate, castração e adoção responsável de cães e gatos abandonados.', 'contato@patassolidarias.org', 1, '2026-06-08 16:58:07'),
(27, 'Verde Vida', '45.678.901/0001-23', 'contato@verdevida.org', '$2y$10$B6dN7mxeDx1bkmJ.QJKO.OMWmYVpS4r.Xyd39K1iwamyVAt27lt2C', 'Meio Ambiente', '🏢', 'Taquaritinga', 'SP', 'Preservação de nascentes e reflorestamento de áreas degradadas na região de Taquaritinga.', 'contato@verdevida.org', 1, '2026-06-08 16:58:07'),
(28, 'Ação Fraterna', '56.789.012/0001-34', 'contato@acaofraterna.org', '$2y$10$B6dN7mxeDx1bkmJ.QJKO.OMWmYVpS4r.Xyd39K1iwamyVAt27lt2C', 'Assistência Social', '🏢', 'Ribeirão Preto', 'SP', 'Distribuição de cestas básicas e apoio a famílias em situação de insegurança alimentar.', 'contato@acaofraterna.org', 1, '2026-06-08 16:58:07'),
(29, 'Instituto Cultural de Ribeirão Preto', '67.890.123/0001-45', 'contato@institutoculturalrp.org', '$2y$10$B6dN7mxeDx1bkmJ.QJKO.OMWmYVpS4r.Xyd39K1iwamyVAt27lt2C', 'Cultura', '🏢', 'Ribeirão Preto', 'SP', 'Oficinas gratuitas de música, teatro e artes plásticas para jovens da periferia.', 'contato@institutoculturalrp.org', 1, '2026-06-08 16:58:07'),
(30, 'Esporte Cidadão', '78.901.234/0001-56', 'contato@esportecidadao.org', '$2y$10$B6dN7mxeDx1bkmJ.QJKO.OMWmYVpS4r.Xyd39K1iwamyVAt27lt2C', 'Esporte', '🏢', 'Taquaritinga', 'SP', 'Escolinhas gratuitas de futebol, vôlei e atletismo para crianças e adolescentes.', 'contato@esportecidadao.org', 1, '2026-06-08 16:58:07'),
(31, 'GAMA — Grupo de Apoio ao Meio Animal', '89.012.345/0001-67', 'gama@grupoapoioanimal.org', '$2y$10$B6dN7mxeDx1bkmJ.QJKO.OMWmYVpS4r.Xyd39K1iwamyVAt27lt2C', 'Animais', '🏢', 'Taquaritinga', 'SP', 'Resgate, tratamento veterinário e castração de animais abandonados nas ruas de Taquaritinga. Mantém mais de 60 animais sob cuidados temporários.', 'gama@grupoapoioanimal.org', 1, '2026-06-08 16:58:08'),
(33, 'Mundo Verde', '21.448.144/0001-67', 'mundoverde@gmail.com', '$2y$10$qpcUuD2r2TUhbLvWUereteYxMPOwVqvYoSlTuZcz/sPt1yN1oxJF6', 'Meio Ambiente', '🏢', 'Taquaritinga', 'SP', '', '4325325345', 1, '2026-06-08 18:18:49');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `projetos`
--
ALTER TABLE `projetos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cnpj` (`cnpj`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=347;

--
-- AUTO_INCREMENT de tabela `projetos`
--
ALTER TABLE `projetos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD CONSTRAINT `movimentacoes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `projetos`
--
ALTER TABLE `projetos`
  ADD CONSTRAINT `projetos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
