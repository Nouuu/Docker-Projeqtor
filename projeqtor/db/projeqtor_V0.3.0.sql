
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR EXPORT                                      //
-- //-------------------------------------------------------//
-- // Version : V0.3.0                                      //
-- // Date : 2009-10-02                                     //
-- ///////////////////////////////////////////////////////////

-- -----------------------------------------------------------
-- Database is expedted to exist. If not, these lines may help
-- -----------------------------------------------------------
--SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
--CREATE DATABASE ${database} DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
--USE ${database};
-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}accessprofile`
--

CREATE TABLE `${prefix}accessprofile` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `idAccessScopeRead` int(12) DEFAULT NULL,
  `idAccessScopeCreate` int(12) DEFAULT NULL,
  `idAccessScopeUpdate` int(12) DEFAULT NULL,
  `idAccessScopeDelete` int(12) DEFAULT NULL,
  `sortOrder` int(3) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}accessprofile`
--

INSERT INTO `${prefix}accessprofile` (`id`, `name`, `description`, `idAccessScopeRead`, `idAccessScopeCreate`, `idAccessScopeUpdate`, `idAccessScopeDelete`, `sortOrder`, `idle`) VALUES
(1, 'accessProfileRestrictiedReader', 'Read only his projects', 3, 1, 1, 1, 100, 0),
(2, 'accessProfileGlobalReader', 'Read all projects', 4, 1, 1, 1, 150, 0),
(3, 'accessProfileRestrictedUpdater', 'Read and Update only his projects', 3, 1, 3, 1, 200, 0),
(4, 'accessProfileGlobalUpdater', 'Read and Update all projects', 4, 1, 4, 1, 250, 0),
(5, 'accessProfileRestricedCreator', 'Read only his projects\nCan create\nUpdate and delete his own elements', 3, 3, 2, 2, 300, 0),
(6, 'accessProfileGlobalCreator', 'Read all projects\nCan create\nUpdate and delete his own elements', 4, 4, 2, 2, 350, 0),
(7, 'accessProfileRestrictedManager', 'Read only his projects\nCan create\nUpdate and delete his projects', 3, 3, 3, 3, 400, 0),
(8, 'accessProfileGlobalManager', 'Read all projects\nCan create\nUpdate and delete his projects', 4, 4, 4, 4, 450, 0),
(9, 'accessProfileNoAccess', 'no access allowed', 1, 1, 1, 1, 999, 0);

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}accessright`
--

CREATE TABLE `${prefix}accessright` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProfile` int(12) unsigned DEFAULT NULL,
  `idMenu` int(12) unsigned DEFAULT NULL,
  `idAccessProfile` int(12) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}accessright`
--

INSERT INTO `${prefix}accessright` (`id`, `idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1, 1, 3, 8),
(2, 2, 3, 2),
(3, 3, 3, 7),
(4, 4, 3, 1),
(5, 6, 3, 1),
(6, 7, 3, 1),
(7, 5, 3, 1),
(8, 1, 4, 8),
(9, 2, 4, 4),
(10, 3, 4, 7),
(11, 4, 4, 3),
(12, 6, 4, 3),
(13, 7, 4, 1),
(14, 5, 4, 1),
(15, 1, 5, 8),
(16, 2, 5, 2),
(17, 3, 5, 7),
(18, 4, 5, 1),
(19, 6, 5, 1),
(20, 7, 5, 1),
(21, 5, 5, 1),
(22, 1, 50, 8),
(23, 2, 50, 2),
(24, 3, 50, 7),
(25, 4, 50, 1),
(26, 6, 50, 9),
(27, 7, 50, 9),
(28, 5, 50, 9),
(29, 1, 22, 8),
(30, 2, 22, 2),
(31, 3, 22, 7),
(32, 4, 22, 7),
(33, 6, 22, 7),
(34, 7, 22, 5),
(35, 5, 22, 1),
(36, 1, 51, 8),
(37, 2, 51, 9),
(38, 3, 51, 7),
(39, 4, 51, 9),
(40, 6, 51, 9),
(41, 7, 51, 9),
(42, 5, 51, 9),
(43, 1, 25, 8),
(44, 2, 25, 2),
(45, 3, 25, 7),
(46, 4, 25, 3),
(47, 6, 25, 1),
(48, 7, 25, 1),
(49, 5, 25, 1),
(50, 1, 26, 8),
(51, 2, 26, 2),
(52, 3, 26, 7),
(53, 4, 26, 3),
(54, 6, 26, 1),
(55, 7, 26, 1),
(56, 5, 26, 1);

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}accessscope`
--

CREATE TABLE `${prefix}accessscope` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `accessCode` varchar(3) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}accessscope`
--

INSERT INTO `${prefix}accessscope` (`id`, `name`, `accessCode`, `sortOrder`, `idle`) VALUES
(1, 'accessScopeNo', 'NO', 100, 0),
(2, 'accessScopeOwn', 'OWN', 200, 0),
(3, 'accessScopeProject', 'PRO', 300, 0),
(4, 'accessScopeAll', 'ALL', 400, 0);

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}action`
--

CREATE TABLE `${prefix}action` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `creationDate` date DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `initialDueDate` date DEFAULT NULL,
  `actualDueDate` date DEFAULT NULL,
  `closureDate` date DEFAULT NULL,
  `result` varchar(4000) DEFAULT NULL,
  `comment` varchar(4000) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}action`
--


-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}activity`
--

CREATE TABLE `${prefix}activity` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idActivityType` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `creationDate` date DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `result` varchar(4000) DEFAULT NULL,
  `comment` varchar(4000) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}activity`
--


-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}affectation`
--

CREATE TABLE `${prefix}affectation` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idResource` int(12) unsigned DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `rate` int(3) unsigned DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}affectation`
--


-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}attachement`
--

CREATE TABLE `${prefix}attachement` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `refType` varchar(100) NOT NULL,
  `refId` int(12) unsigned NOT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `creationDate` datetime DEFAULT NULL,
  `fileName` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `subDirectory` varchar(100) DEFAULT NULL,
  `mimeType` varchar(100) DEFAULT NULL,
  `fileSize` int(12) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}attachement`
--

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}client`
--

CREATE TABLE `${prefix}client` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `clientCode` varchar(25) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}client`
--


-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}criticality`
--

CREATE TABLE `${prefix}criticality` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `value` int(3) unsigned DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}criticality`
--

INSERT INTO `${prefix}criticality` (`id`, `name`, `value`, `color`, `sortOrder`, `idle`) VALUES
(1, 'Low', 0, '#32cd32', 10, 0),
(2, 'Medium', 1, '#ffd700', 20, 0),
(3, 'High', 3, '#ff0000', 30, 0),
(4, 'Critical', 5, '#000000', 40, 0);

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}habilitation`
--

CREATE TABLE `${prefix}habilitation` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProfile` int(12) unsigned DEFAULT NULL,
  `idMenu` int(12) unsigned DEFAULT NULL,
  `allowAccess` int(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}habilitation`
--

INSERT INTO `${prefix}habilitation` (`id`, `idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 1, 14, 1),
(2, 1, 13, 1),
(3, 1, 21, 1),
(4, 1, 17, 1),
(5, 2, 20, 1),
(6, 1, 1, 1),
(7, 2, 1, 1),
(8, 3, 1, 1),
(9, 4, 1, 1),
(10, 6, 1, 1),
(11, 7, 1, 1),
(12, 5, 1, 1),
(13, 1, 2, 1),
(14, 2, 2, 0),
(15, 3, 2, 1),
(16, 4, 2, 1),
(17, 6, 2, 1),
(18, 7, 2, 1),
(19, 5, 2, 0),
(20, 1, 3, 1),
(21, 2, 3, 1),
(22, 3, 3, 1),
(23, 4, 3, 0),
(24, 6, 3, 1),
(25, 7, 3, 0),
(26, 5, 3, 0),
(27, 1, 4, 1),
(28, 2, 4, 1),
(29, 3, 4, 1),
(30, 4, 4, 1),
(31, 6, 4, 1),
(32, 7, 4, 1),
(33, 5, 4, 1),
(34, 1, 5, 1),
(35, 2, 5, 1),
(36, 3, 5, 1),
(37, 4, 5, 0),
(38, 6, 5, 0),
(39, 7, 5, 0),
(40, 5, 5, 0),
(41, 1, 6, 0),
(42, 2, 6, 0),
(43, 3, 6, 0),
(44, 4, 6, 0),
(45, 6, 6, 0),
(46, 7, 6, 0),
(47, 5, 6, 0),
(48, 1, 7, 1),
(49, 2, 7, 1),
(50, 3, 7, 1),
(51, 4, 7, 1),
(52, 6, 7, 1),
(53, 7, 7, 1),
(54, 5, 7, 1),
(55, 1, 8, 0),
(56, 2, 8, 0),
(57, 3, 8, 0),
(58, 4, 8, 0),
(59, 6, 8, 0),
(60, 7, 8, 0),
(61, 5, 8, 0),
(62, 1, 9, 1),
(63, 2, 9, 1),
(64, 3, 9, 1),
(65, 4, 9, 1),
(66, 6, 9, 1),
(67, 7, 9, 1),
(68, 5, 9, 1),
(69, 1, 10, 0),
(70, 2, 10, 0),
(71, 3, 10, 0),
(72, 4, 10, 0),
(73, 6, 10, 0),
(74, 7, 10, 0),
(75, 5, 10, 0),
(76, 1, 11, 1),
(77, 2, 11, 0),
(78, 3, 11, 1),
(79, 4, 11, 0),
(80, 6, 11, 0),
(81, 7, 11, 0),
(82, 5, 11, 0),
(83, 1, 12, 0),
(84, 2, 12, 0),
(85, 3, 12, 0),
(86, 4, 12, 0),
(87, 6, 12, 0),
(88, 7, 12, 0),
(89, 5, 12, 0),
(90, 2, 13, 1),
(91, 3, 13, 1),
(92, 4, 13, 1),
(93, 6, 13, 1),
(94, 7, 13, 1),
(95, 5, 13, 1),
(96, 2, 14, 1),
(97, 3, 14, 1),
(98, 4, 14, 0),
(99, 6, 14, 0),
(100, 7, 14, 0),
(101, 5, 14, 0),
(102, 1, 15, 1),
(103, 2, 15, 0),
(104, 3, 15, 0),
(105, 4, 15, 0),
(106, 6, 15, 0),
(107, 7, 15, 0),
(108, 5, 15, 0),
(109, 1, 16, 1),
(110, 2, 16, 1),
(111, 3, 16, 1),
(112, 4, 16, 0),
(113, 6, 16, 0),
(114, 7, 16, 0),
(115, 5, 16, 0),
(116, 2, 17, 0),
(117, 3, 17, 0),
(118, 4, 17, 0),
(119, 6, 17, 0),
(120, 7, 17, 0),
(121, 5, 17, 0),
(122, 2, 21, 0),
(123, 3, 21, 0),
(124, 4, 21, 0),
(125, 6, 21, 0),
(126, 7, 21, 0),
(127, 5, 21, 0),
(128, 1, 18, 0),
(129, 2, 18, 0),
(130, 3, 18, 0),
(131, 4, 18, 0),
(132, 6, 18, 0),
(133, 7, 18, 0),
(134, 5, 18, 0),
(135, 1, 19, 0),
(136, 2, 19, 0),
(137, 3, 19, 0),
(138, 4, 19, 0),
(139, 6, 19, 0),
(140, 7, 19, 0),
(141, 5, 19, 0),
(142, 1, 20, 1),
(143, 3, 20, 1),
(144, 4, 20, 1),
(145, 6, 20, 1),
(146, 7, 20, 1),
(147, 5, 20, 1),
(148, 1, 22, 1),
(149, 2, 22, 0),
(150, 3, 22, 1),
(151, 4, 22, 1),
(152, 6, 22, 1),
(153, 7, 22, 1),
(154, 5, 22, 0),
(155, 1, 23, 0),
(156, 2, 23, 0),
(157, 3, 23, 0),
(158, 4, 23, 0),
(159, 6, 23, 0),
(160, 7, 23, 0),
(161, 5, 23, 0),
(162, 1, 24, 0),
(163, 2, 24, 0),
(164, 3, 24, 0),
(165, 4, 24, 0),
(166, 6, 24, 0),
(167, 7, 24, 0),
(168, 5, 24, 0),
(169, 1, 25, 1),
(170, 2, 25, 0),
(171, 3, 25, 1),
(172, 4, 25, 1),
(173, 6, 25, 1),
(174, 7, 25, 1),
(175, 5, 25, 0),
(176, 1, 26, 1),
(177, 2, 26, 0),
(178, 3, 26, 1),
(179, 4, 26, 1),
(180, 6, 26, 1),
(181, 7, 26, 1),
(182, 5, 26, 0),
(183, 1, 32, 0),
(184, 2, 32, 0),
(185, 3, 32, 0),
(186, 4, 32, 0),
(187, 6, 32, 0),
(188, 7, 32, 0),
(189, 5, 32, 0),
(190, 1, 33, 0),
(191, 2, 33, 0),
(192, 3, 33, 0),
(193, 4, 33, 0),
(194, 6, 33, 0),
(195, 7, 33, 0),
(196, 5, 33, 0),
(197, 1, 34, 1),
(198, 2, 34, 0),
(199, 3, 34, 0),
(200, 4, 34, 0),
(201, 6, 34, 0),
(202, 7, 34, 0),
(203, 5, 34, 0),
(204, 1, 36, 1),
(205, 2, 36, 0),
(206, 3, 36, 0),
(207, 4, 36, 0),
(208, 6, 36, 0),
(209, 7, 36, 0),
(210, 5, 36, 0),
(211, 1, 37, 1),
(212, 2, 37, 0),
(213, 3, 37, 0),
(214, 4, 37, 0),
(215, 6, 37, 0),
(216, 7, 37, 0),
(217, 5, 37, 0),
(218, 1, 38, 1),
(219, 2, 38, 0),
(220, 3, 38, 0),
(221, 4, 38, 0),
(222, 6, 38, 0),
(223, 7, 38, 0),
(224, 5, 38, 0),
(225, 1, 39, 1),
(226, 2, 39, 0),
(227, 3, 39, 0),
(228, 4, 39, 0),
(229, 6, 39, 0),
(230, 7, 39, 0),
(231, 5, 39, 0),
(232, 1, 40, 1),
(233, 2, 40, 0),
(234, 3, 40, 0),
(235, 4, 40, 0),
(236, 6, 40, 0),
(237, 7, 40, 0),
(238, 5, 40, 0),
(239, 1, 42, 1),
(240, 2, 42, 0),
(241, 3, 42, 0),
(242, 4, 42, 0),
(243, 6, 42, 0),
(244, 7, 42, 0),
(245, 5, 42, 0),
(246, 1, 41, 1),
(247, 2, 41, 0),
(248, 3, 41, 0),
(249, 4, 41, 0),
(250, 6, 41, 0),
(251, 7, 41, 0),
(252, 5, 41, 0),
(253, 1, 43, 1),
(254, 2, 43, 1),
(255, 3, 43, 1),
(256, 4, 43, 1),
(257, 6, 43, 1),
(258, 7, 43, 1),
(259, 5, 43, 1),
(260, 1, 44, 1),
(261, 2, 44, 0),
(262, 3, 44, 1),
(263, 4, 44, 0),
(264, 6, 44, 0),
(265, 7, 44, 0),
(266, 5, 44, 0),
(267, 1, 45, 1),
(268, 2, 45, 0),
(269, 3, 45, 0),
(270, 4, 45, 0),
(271, 6, 45, 0),
(272, 7, 45, 0),
(273, 5, 45, 0),
(274, 1, 46, 1),
(275, 2, 46, 0),
(276, 3, 46, 0),
(277, 4, 46, 0),
(278, 6, 46, 0),
(279, 7, 46, 0),
(280, 5, 46, 0),
(281, 1, 50, 1),
(282, 2, 50, 0),
(283, 3, 50, 1),
(284, 4, 50, 0),
(285, 6, 50, 0),
(286, 7, 50, 0),
(287, 5, 50, 0),
(288, 1, 49, 1),
(289, 2, 49, 0),
(290, 3, 49, 0),
(291, 4, 49, 0),
(292, 6, 49, 0),
(293, 7, 49, 0),
(294, 5, 49, 0),
(295, 1, 47, 1),
(296, 2, 47, 0),
(297, 3, 47, 0),
(298, 4, 47, 0),
(299, 6, 47, 0),
(300, 7, 47, 0),
(301, 5, 47, 0),
(302, 1, 48, 1),
(303, 2, 48, 0),
(304, 3, 48, 0),
(305, 4, 48, 0),
(306, 6, 48, 0),
(307, 7, 48, 0),
(308, 5, 48, 0),
(309, 1, 51, 1),
(310, 2, 51, 0),
(311, 3, 51, 1),
(312, 4, 51, 0),
(313, 6, 51, 0),
(314, 7, 51, 0),
(315, 5, 51, 0),
(316, 1, 52, 1),
(317, 2, 52, 0),
(318, 3, 52, 0),
(319, 4, 52, 0),
(320, 6, 52, 0),
(321, 7, 52, 0),
(322, 5, 52, 0),
(323, 1, 53, 1),
(324, 2, 53, 0),
(325, 3, 53, 0),
(326, 4, 53, 0),
(327, 6, 53, 0),
(328, 7, 53, 0),
(329, 5, 53, 0),
(330, 1, 55, 1),
(331, 2, 55, 0),
(332, 3, 55, 0),
(333, 4, 55, 0),
(334, 6, 55, 0),
(335, 7, 55, 0),
(336, 5, 55, 0),
(337, 1, 56, 1),
(338, 2, 56, 0),
(339, 3, 56, 0),
(340, 4, 56, 0),
(341, 6, 56, 0),
(342, 7, 56, 0),
(343, 5, 56, 0);

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}history`
--

CREATE TABLE `${prefix}history` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `refType` varchar(100) NOT NULL,
  `refId` int(12) unsigned NOT NULL,
  `operation` varchar(10) DEFAULT NULL,
  `colName` varchar(200) DEFAULT NULL,
  `oldValue` varchar(4000) DEFAULT NULL,
  `newValue` varchar(4000) DEFAULT NULL,
  `operationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idUser` int(12) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}issue`
--

CREATE TABLE `${prefix}issue` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `idIssueType` int(12) unsigned DEFAULT NULL,
  `cause` varchar(4000) DEFAULT NULL,
  `impact` varchar(4000) DEFAULT NULL,
  `idPriority` int(12) unsigned DEFAULT NULL,
  `creationDate` date DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `initialEndDate` date DEFAULT NULL,
  `actualEndDate` date DEFAULT NULL,
  `closureDate` date DEFAULT NULL,
  `result` varchar(4000) DEFAULT NULL,
  `comment` varchar(4000) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}issue`
--


-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}likelihood`
--

CREATE TABLE `${prefix}likelihood` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `value` int(3) unsigned DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}likelihood`
--

INSERT INTO `${prefix}likelihood` (`id`, `name`, `value`, `color`, `sortOrder`, `idle`) VALUES
(1, 'Low (10%)', 10, '#32cd32', 10, 0),
(2, 'Medium (50%)', 50, '#ffd700', 20, 0),
(3, 'High (90%)', 90, '#ff0000', 30, 0);

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}menu`
--

CREATE TABLE `${prefix}menu` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `idMenu` int(12) unsigned DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `level` varchar(100) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}menu`
--

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES
(1, 'menuToday', 0, 'item', 100, NULL, 0),
(2, 'menuWork', 0, 'menu', 110, 'Project', 1),
(3, 'menuRisk', 43, 'object', 310, 'Project', 0),
(4, 'menuAction', 43, 'object', 320, 'Project', 0),
(5, 'menuIssue', 43, 'object', 330, 'Project', 0),
(6, 'menuMeeting', 0, 'class', 260, 'Project', 1),
(7, 'menuFollowup', 0, 'menu', 200, NULL, 1),
(8, 'menuImputation', 7, 'item', 210, NULL, 1),
(9, 'menuPlanning', 7, 'item', 220, NULL, 0),
(10, 'menuComponent', 0, 'class', 400, NULL, 1),
(11, 'menuTool', 0, 'menu', 500, NULL, 1),
(12, 'menuRequestor', 11, 'item', 501, NULL, 1),
(13, 'menuParameter', 0, 'menu', 900, NULL, 1),
(14, 'menuEnvironmentalParameter', 13, 'menu', 910, NULL, 1),
(15, 'menuClient', 14, 'object', 912, NULL, 0),
(16, 'menuProject', 14, 'object', 914, NULL, 0),
(17, 'menuUser', 14, 'object', 916, NULL, 0),
(18, 'menuGlobalParameter', 13, 'item', 980, NULL, 1),
(19, 'menuProjectParameter', 13, 'item', 985, NULL, 1),
(20, 'menuUserParameter', 13, 'item', 990, '', 0),
(21, 'menuHabilitation', 37, 'item', 966, NULL, 0),
(22, 'menuTicket', 2, 'object', 120, 'Project', 0),
(25, 'menuActivity', 2, 'object', 135, 'Project', 0),
(26, 'menuMilestone', 2, 'object', 145, 'Project', 0),
(34, 'menuStatus', 36, 'object', 932, NULL, 0),
(36, 'menuListOfValues', 13, 'menu', 930, NULL, 1),
(37, 'menuHabilitationParameter', 13, 'menu', 960, NULL, 1),
(38, 'menuSeverity', 36, 'object', 934, NULL, 0),
(39, 'menuLikelihood', 36, 'object', 936, NULL, 0),
(40, 'menuCriticality', 36, 'object', 938, NULL, 0),
(41, 'menuPriority', 36, 'object', 942, NULL, 0),
(42, 'menuUrgency', 36, 'object', 940, NULL, 0),
(43, 'menuRiskManagementPlan', 0, 'menu', 300, '', 1),
(44, 'menuResource', 14, 'object', 918, NULL, 0),
(45, 'menuRiskType', 36, 'object', 950, NULL, 0),
(46, 'menuIssueType', 36, 'object', 952, NULL, 0),
(47, 'menuAccessProfile', 37, 'object', 964, NULL, 0),
(48, 'menuAccessRight', 37, 'item', 968, NULL, 0),
(49, 'menuProfile', 37, 'object', 962, NULL, 0),
(50, 'menuAffectation', 14, 'object', 920, 'Project', 0),
(51, 'menuMessage', 11, 'object', 510, 'Project', 0),
(52, 'menuMessageType', 36, 'object', 954, NULL, 0),
(53, 'menuTicketType', 36, 'object', 944, NULL, 0),
(55, 'menuActivityType', 36, 'object', 946, NULL, 0),
(56, 'menuMilestoneType', 36, 'object', 948, NULL, 0);

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}message`
--

CREATE TABLE `${prefix}message` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `idMessageType` int(12) unsigned DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `idProfile` int(12) unsigned DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}message`
--
INSERT INTO `${prefix}message` (`idProject`, `name`, `idMessageType`, `description`, `idProfile`, `idUser`, `idle`) VALUES 
(null, 'Welcome', '15', 'Welcome to ProjeQtOr web application', null, null, 0);


-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}milestone`
--

CREATE TABLE `${prefix}milestone` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `creationDate` date DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `result` varchar(4000) DEFAULT NULL,
  `comment` varchar(4000) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `idMilestoneType` int(12) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}milestone`
--


-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}note`
--

CREATE TABLE `${prefix}note` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `refType` varchar(100) NOT NULL,
  `refId` int(12) unsigned NOT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `creationDate` datetime DEFAULT NULL,
  `updateDate` datetime DEFAULT NULL,
  `note` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}note`
--

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}parameter`
--

CREATE TABLE `${prefix}parameter` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idUser` int(12) unsigned DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `parameterCode` varchar(100) DEFAULT NULL,
  `parameterValue` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}parameter`
--

-- INSERT INTO `${prefix}parameter` (`id`, `idUser`, `idProject`, `parameterCode`, `parameterValue`) VALUES
-- (1, NULL, NULL, 'dbVersion', 'V0.0.0'),
-- (2, 1, NULL, 'destinationWidth', '847'),
-- (3, 1, NULL, 'theme', 'ProjectOrRia'),
-- (4, 1, NULL, 'lang', 'en'),
-- (5, 1, NULL, 'defaultProject', '3'),
-- (6, 1, NULL, 'displayAttachment', 'YES_CLOSED'),
-- (7, 1, NULL, 'displayNote', 'YES_CLOSED'),
-- (8, 1, NULL, 'displayHistory', 'YES_CLOSED'),
-- (9, 1, NULL, 'refreshUpdates', 'YES'),
-- (10, 3, NULL, 'destinationWidth', '719'),
-- (11, 3, NULL, 'theme', 'ProjectOrRia'),
-- (12, 3, NULL, 'lang', 'en'),
-- (13, 3, NULL, 'defaultProject', '3'),
-- (14, 3, NULL, 'displayAttachment', 'YES_CLOSED'),
-- (15, 3, NULL, 'displayNote', 'YES_CLOSED'),
-- (16, 3, NULL, 'displayHistory', 'YES_CLOSED'),
-- (17, 3, NULL, 'refreshUpdates', 'YES');

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}planningelement`
--

CREATE TABLE `${prefix}planningelement` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `refType` varchar(100) NOT NULL,
  `refId` int(12) unsigned NOT NULL,
  `refName` varchar(100) DEFAULT NULL,
  `initialStartDate` date DEFAULT NULL,
  `validatedStartDate` date DEFAULT NULL,
  `plannedStartDate` date DEFAULT NULL,
  `realStartDate` date DEFAULT NULL,
  `initialEndDate` date DEFAULT NULL,
  `validatedEndDate` date DEFAULT NULL,
  `plannedEndDate` date DEFAULT NULL,
  `realEndDate` date DEFAULT NULL,
  `initialDuration` int(5) DEFAULT NULL,
  `validatedDuration` int(5) unsigned DEFAULT NULL,
  `plannedDuration` int(5) DEFAULT NULL,
  `realDuration` int(5) DEFAULT NULL,
  `initialWork` int(5) unsigned DEFAULT NULL,
  `validatedWork` int(5) unsigned DEFAULT NULL,
  `plannedWork` int(5) unsigned DEFAULT NULL,
  `realWork` int(5) unsigned DEFAULT NULL,
  `wbs` varchar(100) DEFAULT NULL,
  `wbsSortable` varchar(400) DEFAULT NULL,
  `topId` int(12) unsigned DEFAULT NULL,
  `topRefType` varchar(100) DEFAULT NULL,
  `topRefId` int(12) unsigned DEFAULT NULL,
  `priority` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT NULL,
  `elementary` int(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE INDEX planningelementTopId ON `${prefix}planningelement` (`topId`);
CREATE INDEX planningelementRef ON `${prefix}planningelement` (`refType`,`refId`);
CREATE INDEX planningelementTopRef ON `${prefix}planningelement` (`topRefType`,`topRefId`);
  
--
-- Contenu de la TABLE `${prefix}planningelement`
--

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}priority`
--

CREATE TABLE `${prefix}priority` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `value` int(3) unsigned DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}priority`
--

INSERT INTO `${prefix}priority` (`id`, `name`, `value`, `color`, `sortOrder`, `idle`) VALUES
(1, 'Low priority', 0, '#32cd32', 40, 0),
(2, 'Medium priority', 1, '#ffd700', 30, 0),
(3, 'Hight priority', 3, '#ff0000', 20, 0),
(4, 'Critical priority (immediate action required)', 5, '#000000', 10, 0);

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}profile`
--

CREATE TABLE `${prefix}profile` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `profileCode` varchar(3) DEFAULT NULL,
  `sortOrder` int(3) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}profile`
--

INSERT INTO `${prefix}profile` (`id`, `name`, `description`, `profileCode`, `sortOrder`, `idle`) VALUES
(1, 'profileAdministrator', 'Has a visibility over all the projects', 'ADM', 100, 0),
(2, 'profileSupervisor', 'Has a visibility over all the projects', 'SUP', 200, 0),
(3, 'profileProjectLeader', 'Leads his owns project', 'PL', 310, 0),
(4, 'profileTeamMember', 'Works for a project', 'TM', 320, 0),
(5, 'profileGuest', 'Has limited visibility to a project', 'G', 500, 0),
(6, 'profileExternalProjectLeader', NULL, 'EPL', 410, 0),
(7, 'profileExternalTeamMember', NULL, 'ETM', 420, 0);

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}project`
--

CREATE TABLE `${prefix}project` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `idClient` int(12) DEFAULT NULL,
  `projectCode` varchar(25) DEFAULT NULL,
  `contractCode` varchar(25) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}project`
--

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}risk`
--

CREATE TABLE `${prefix}risk` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `idRiskType` int(12) unsigned DEFAULT NULL,
  `cause` varchar(4000) DEFAULT NULL,
  `impact` varchar(4000) DEFAULT NULL,
  `idSeverity` int(12) unsigned DEFAULT NULL,
  `idLikelihood` int(12) unsigned DEFAULT NULL,
  `idCriticality` int(12) unsigned DEFAULT NULL,
  `creationDate` date DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `initialEndDate` date DEFAULT NULL,
  `actualEndDate` date DEFAULT NULL,
  `closureDate` date DEFAULT NULL,
  `result` varchar(4000) DEFAULT NULL,
  `comment` varchar(4000) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}risk`
--


-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}severity`
--

CREATE TABLE `${prefix}severity` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `value` int(3) unsigned DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}severity`
--

INSERT INTO `${prefix}severity` (`id`, `name`, `value`, `color`, `sortOrder`, `idle`) VALUES
(1, 'Low', 1, '#32cd32', 10, 0),
(2, 'Medium', 5, '#ffd700', 20, 0),
(3, 'High', 9, '#ff0000', 30, 0);

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}sla`
--

CREATE TABLE `${prefix}sla` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idTicketType` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `durationSla` double DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Contenu de la TABLE `${prefix}sla`
--


-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}status`
--

CREATE TABLE `${prefix}status` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `setEndStatus` int(1) unsigned DEFAULT NULL,
  `setIdleStatus` int(1) unsigned DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}status`
--

INSERT INTO `${prefix}status` (`id`, `name`, `setEndStatus`, `setIdleStatus`, `color`, `sortOrder`, `idle`) VALUES
(1, 'recorded', NULL, 0, '#ffa500', 100, 0),
(2, 'qualified', NULL, 0, '#87ceeb', 200, 0),
(3, 'in progress', NULL, 0, '#d2691e', 300, 0),
(4, 'done', 1, 0, '#afeeee', 400, 0),
(5, 'verified', 1, 0, '#32cd32', 500, 0),
(6, 'delivered', 1, 0, '#4169e1', 600, 0),
(7, 'closed', 1, 1, '#c0c0c0', 700, 0),
(8, 're-opened', NULL, 0, '#ff0000', 250, 0),
(9, 'cancelled', 0, 1, '#c0c0c0', 999, 0),
(10, 'assigned', NULL, 0, '#8b4513', 275, 0),
(11, 'accepted', NULL, 0, '#a52a2a', 220, 0),
(12, 'validated', 1, 0, '#98fb98', 650, 0);

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}task`
--

CREATE TABLE `${prefix}task` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `creationDate` date DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `initialDueDate` date DEFAULT NULL,
  `actualDueDate` date DEFAULT NULL,
  `closureDate` date DEFAULT NULL,
  `result` varchar(4000) DEFAULT NULL,
  `comment` varchar(4000) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}task`
--

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}ticket`
--

CREATE TABLE `${prefix}ticket` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idTicketType` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `creationDateTime` datetime DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `initialDueDateTime` datetime DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `actualDueDateTime` datetime DEFAULT NULL,
  `closureDateTime` datetime DEFAULT NULL,
  `result` varchar(4000) DEFAULT NULL,
  `comment` varchar(4000) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}ticket`
--


-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}type`
--

CREATE TABLE `${prefix}type` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `scope` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `color` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}type`
--

INSERT INTO `${prefix}type` (`id`, `scope`, `name`, `sortOrder`, `idle`, `color`) VALUES
(1, 'Risk', 'Contractual', 10, 0, NULL),
(2, 'Risk', 'Operational', 20, 0, NULL),
(3, 'Risk', 'Technical', 30, 0, NULL),
(4, 'Issue', 'Technical issue', 10, 0, NULL),
(5, 'Issue', 'Process non conformity', 30, 0, NULL),
(6, 'Issue', 'Quality non conformity', 40, 0, NULL),
(7, 'Issue', 'Process non appliability', 20, 0, NULL),
(8, 'Issue', 'Customer complaint', 90, 0, NULL),
(9, 'Issue', 'Delay non respect', 50, 0, NULL),
(10, 'Issue', 'Resource management issue', 70, 0, NULL),
(12, 'Issue', 'Financial loss', 80, 0, NULL),
(13, 'Message', 'ERROR', 10, 0, '#ff0000'),
(14, 'Message', 'WARNING', 10, 0, '#ffa500'),
(15, 'Message', 'INFO', 30, 0, '#0000ff'),
(16, 'Ticket', 'Incident', 10, 0, NULL),
(17, 'Ticket', 'Support / Assistance', 20, 0, NULL),
(18, 'Ticket', 'Anomaly / Bug', 30, 0, NULL),
(19, 'Activity', 'Development', 10, 0, NULL),
(20, 'Activity', 'Evolution', 20, 0, NULL),
(21, 'Activity', 'Management', 30, 0, NULL),
(22, 'Activity', 'Phase', 40, 0, NULL),
(23, 'Milestone', 'Deliverable', 10, 0, NULL),
(24, 'Milestone', 'Incoming', 20, 0, NULL),
(25, 'Milestone', 'Key date', 30, 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}urgency`
--

CREATE TABLE `${prefix}urgency` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `value` int(3) unsigned DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}urgency`
--

INSERT INTO `${prefix}urgency` (`id`, `name`, `value`, `color`, `sortOrder`, `idle`) VALUES
(1, 'Blocking (highest priority)', 90, '#ff0000', 10, 0),
(2, 'Urgent', 50, '#ffd700', 20, 0),
(3, 'Not urgent', 10, '#32cd32', 30, 0);

-- --------------------------------------------------------

--
-- Structure de la TABLE `${prefix}resource` (old name = user)
--

CREATE TABLE `${prefix}resource` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `fullName` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `idProfile` int(12) DEFAULT NULL,
  `isResource` int(1) unsigned DEFAULT '0',
  `isUser` int(1) unsigned DEFAULT '0',
  `locked` int(1) unsigned DEFAULT '0',
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la TABLE `${prefix}user`
--

INSERT INTO `${prefix}resource` (`id`, `name`, `fullName`, `email`, `description`, `password`, `idProfile`, `isResource`, `isUser`, `locked`, `idle`) VALUES
(1, 'admin', NULL, NULL, NULL, '21232f297a57a5a743894a0e4a801fc3', 1, 0, 1, 0, 0),
(2, 'guest', NULL, NULL, NULL, '084e0343a0486ff05530df6c705c8bb4', 5, 0, 1, 0, 0);
