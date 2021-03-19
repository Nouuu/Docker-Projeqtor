
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V1.1.0                           //
-- // Date : 2010-07-13                                     //
-- ///////////////////////////////////////////////////////////
--
--

CREATE TABLE `${prefix}report` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `idReportCategory` int(12) unsigned NOT NULL,
  `file` varchar(100),
  `sortOrder` int(5),
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `${prefix}reportparameter` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idReport` int(12) unsigned NOT NULL,
  `name` varchar(100),
  `paramType` varchar(100),
  `sortOrder` int(5),
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `${prefix}reportcategory` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `sortOrder` int(5),
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES
(61, 'menuReports', 7, 'item', 230, NULL, 0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 61, 1),
(2, 61, 1),
(3, 61, 1);

INSERT INTO `${prefix}reportcategory` (`id`, `name`, `sortOrder`) VALUES
(1, 'reportCategoryWork', 10),
(2, 'reportCategoryPlan', 20);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`) VALUES
(1, 'reportWorkWeekly', 1, 'work.php', 10),
(2, 'reportWorkMonthly', 1, 'work.php', 20),
(3, 'reportWorkYearly', 1, 'work.php', 30),
(4, 'reportPlanColoredMonthly', 2, 'colorPlan.php', 10),
(5, 'reportPlanResourceMonthly', 2, 'resourcePlan.php', 20),
(6, 'reportPlanProjectMonthly', 2, 'projectPlan.php', 30);

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`) VALUES 
(1, 1, 'week', 'week', 10),
(2, 2, 'month', 'month', 10),
(3, 3, 'year', 'year', 10),
(4, 4, 'month', 'month', 10),
(5, 5, 'month', 'month', 10),
(6, 6, 'month', 'month', 10);
