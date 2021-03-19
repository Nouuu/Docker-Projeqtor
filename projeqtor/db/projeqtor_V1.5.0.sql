
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V1.5.0                                      //
-- // Date : 2010-12-08                                     //
-- ///////////////////////////////////////////////////////////
--
--
ALTER TABLE `${prefix}affectation` ADD `idRole` int(12) unsigned DEFAULT NULL,
ADD `startDate` date DEFAULT NULL,
ADD `endDate` date DEFAULT NULL;

ALTER TABLE `${prefix}assignment` ADD `idRole` int(12) unsigned DEFAULT NULL,
ADD `dailyCost` NUMERIC(7,2) DEFAULT NULL,
ADD `newDailyCost` NUMERIC(7,2) DEFAULT NULL,
ADD `assignedCost` NUMERIC(11,2) DEFAULT NULL,
ADD `realCost` NUMERIC(11,2) DEFAULT NULL,
ADD `leftCost` NUMERIC(11,2) DEFAULT NULL,
ADD `plannedCost` NUMERIC(11,2) DEFAULT NULL;

ALTER TABLE `${prefix}work` ADD  `dailyCost` NUMERIC(7,2) DEFAULT NULL,
ADD `cost` NUMERIC(11,2) DEFAULT NULL;

ALTER TABLE `${prefix}plannedwork` ADD  `dailyCost` NUMERIC(7,2) DEFAULT NULL,
ADD `cost` NUMERIC(11,2) DEFAULT NULL;

ALTER TABLE `${prefix}resource` ADD  `idRole` int(12) unsigned DEFAULT NULL;

ALTER TABLE `${prefix}planningelement` ADD `initialCost` NUMERIC(11,2) DEFAULT NULL,
ADD `validatedCost` NUMERIC(11,2) DEFAULT NULL,
ADD `assignedCost` NUMERIC(11,2) DEFAULT NULL,
ADD `realCost` NUMERIC(11,2) DEFAULT NULL,
ADD `leftCost` NUMERIC(11,2) DEFAULT NULL,
ADD `plannedCost` NUMERIC(11,2) DEFAULT NULL;

UPDATE `${prefix}type` SET `name`='Steering Committee'
WHERE `scope`='Meeting' and `name`='Steering Comitee';

CREATE TABLE `${prefix}resourcecost` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idResource` int(12) unsigned DEFAULT NULL,
  `idRole` int(12) unsigned DEFAULT NULL,
  `cost` NUMERIC(11,2) DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE `${prefix}role` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `sortOrder` int(3) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES
(73, 'menuRole', 36, 'object', 931, Null, 0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 73, 1);

INSERT INTO `${prefix}role` (`id`, `name`, `description`,`sortOrder`, `idle`) VALUES
(1,'Manager','Leader/Manager of the project',10,0),
(2,'Analyst','Responsible of specifications',20,0),
(3,'Developer','Sowftware developer',30,0),
(4,'Expert','Technical expert',40,0),
(5,'Machine','Non human resource',50,0);

CREATE TABLE `${prefix}visibilityscope` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `accessCode` varchar(3) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}visibilityscope` (`id`, `name`, `accessCode`, `sortOrder`, `idle`) VALUES
(1, 'visibilityScopeNo', 'NO', 100, 0),
(2, 'visibilityScopeValid', 'VAL', 200, 0),
(4, 'visibilityScopeAll', 'ALL', 400, 0);

INSERT INTO `${prefix}habilitationother` (`idProfile`, `scope`, `rightAccess`) VALUES
(1, 'work', 4),
(2, 'work', 4),
(3, 'work', 4),
(4, 'work', 4),
(6, 'work', 2),
(7, 'work', 1),
(5, 'work', 1),
(1, 'cost', 4),
(2, 'cost', 4),
(3, 'cost', 4),
(4, 'cost', 1),
(6, 'cost', 2),
(7, 'cost', 1),
(5, 'cost', 1);

ALTER TABLE `${prefix}ticket` ADD  `idContact` int(12) unsigned DEFAULT NULL;

INSERT INTO `${prefix}reportcategory` (`id`, `name`, `sortOrder`) VALUES
(6, 'reportCategoryCost', 50);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`) VALUES
(26, 'reportCostDetail', 6, 'costPlan.php', 10),
(27, 'reportCostMonthly', 6, 'globalCostPlanning.php?scale=month', 20);

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES 
(63, 26, 'idProject', 'projectList', 10, 'currentProject'),
(64, 27, 'idProject', 'projectList', 10, 'currentProject');

INSERT INTO `${prefix}habilitationreport` (`idReport`, `idProfile`,  `allowAccess`) VALUES
(26, 1, 1),
(27, 1, 1),
(26, 2, 1),
(27, 2, 1),
(26, 3, 1),
(27, 3, 1);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`) VALUES
(28, 'reportWorkDetailWeekly', 1, 'workDetail.php?scale=week', 240),
(29, 'reportWorkDetailMonthly', 1, 'workDetail.php?scale=month', 250),
(30, 'reportWorkDetailYearly', 1, 'workDetail.php?scale=year', 260);

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES 
(65, 28, 'week', 'week', 10, 'currentWeek'),
(66, 29, 'month', 'month', 10, 'currentMonth'),
(67, 30, 'year', 'year', 10, 'currentYear');

INSERT INTO `${prefix}habilitationreport` (`idReport`, `idProfile`,  `allowAccess`) VALUES
(28, 1, 1),
(29, 1, 1),
(30, 1, 1),
(28, 2, 1),
(29, 2, 1),
(30, 2, 1),
(28, 3, 1),
(29, 3, 1),
(30, 3, 1);

ALTER TABLE `${prefix}statusmail` ADD  `mailToContact` int(1) unsigned DEFAULT 0,
ADD  `mailToLeader` int(1) unsigned DEFAULT 0,
ADD  `mailToOther` int(1) unsigned DEFAULT 0,
ADD `otherMail` varchar(4000) DEFAULT NULL;
