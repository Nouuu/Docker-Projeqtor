-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 6.3.0                                       //
-- // Date : 2017-04-21                                     //
-- ///////////////////////////////////////////////////////////

INSERT INTO `${prefix}copyable` (`id`,`name`, `idle`, `sortOrder`) VALUES 
(17,'TestCase', '0', '900'),
(18,'TestSession', '0', '910');

ALTER TABLE `${prefix}testcaserun` ADD `result` varchar(4000) DEFAULT NULL;

CREATE TABLE `${prefix}delivery` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `externalReference` varchar(100) DEFAULT NULL,
  `scope` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `idDeliverableType` int(12) unsigned DEFAULT NULL,
  `creationDateTime` datetime DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `result` mediumtext DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `plannedDate` date DEFAULT NULL,
  `realDate` date DEFAULT NULL,
  `validationDate` date DEFAULT NULL,
  `impactWork` decimal(5) DEFAULT NULL,
  `impactDuration` int(5) DEFAULT NULL,
  `impactCost` decimal(9) DEFAULT NULL,
  `idDeliverableWeight` int(12) unsigned DEFAULT NULL,
  `idDeliverableStatus` int(12) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX `deliveryDeliverableTypeIdx` ON `${prefix}delivery` (`idDeliverableType`);
CREATE INDEX `deliveryDeliverableStatusIdx` ON `${prefix}delivery` (`idDeliverableStatus`);
CREATE INDEX `deliveryProjectIdx` ON `${prefix}delivery` (`idProject`);

INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(176,'menuDelivery', 6, 'object', 375, 'Project', 0, 'Work Meeting');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES 
(1, 176, 1),
(2, 176, 1),
(3, 176, 1);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES 
(1,176,8),
(2,176,2),
(3,176,7);

INSERT INTO `${prefix}importable` ( `name`,`idle`) VALUES 
('Deliverable',0),
('Incoming',0);

INSERT INTO `${prefix}mailable` (`id`, `name`, `idle`) VALUES 
(29,'DocumentDirectory', '0');

INSERT INTO `${prefix}copyable` (`id`,`name`, `idle`, `sortOrder`) VALUES 
(19,'Opportunity', '0', '900');


--ADD by qCazelles - Business Features
CREATE TABLE `${prefix}businessfeature` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `idProduct` int(12) NOT NULL,
  `creationDate` date NOT NULL,
  `idUser` int(12) NOT NULL,
  `idle` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

ALTER TABLE `${prefix}ticket` ADD COLUMN `idBusinessFeature` int(12) DEFAULT NULL;
--END ADD qCazelles
ALTER TABLE `${prefix}requirement` ADD COLUMN `idBusinessFeature` int(12) DEFAULT NULL;

UPDATE `${prefix}reportparameter` SET defaultValue='currentProject' WHERE idReport in (41,43,44,53)  and name='idProject';

ALTER TABLE `${prefix}subscription` ADD `isAutoSub` int(1) DEFAULT '0';

--ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
INSERT INTO `${prefix}parameter` (`parameterCode`, `parameterValue`) VALUES ('useOrganizationBudgetElement', 'NO');
DELETE FROM `${prefix}columnselector` WHERE `objectClass` = 'Organization';
DELETE FROM `${prefix}filter` WHERE `refType` = 'Organization';
--END ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT

CREATE TABLE `${prefix}noteflux` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`, `menuClass`) VALUES 
(177,'menuActivityStream',0,'item',19,NULL,0,'Work Risk RequirementTest Financial Meeting');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 177, 1),
(2, 177, 1),
(3, 177, 1),
(4, 177, 1),
(5, 177, 0),
(6, 177, 0),
(7, 177, 0);

ALTER TABLE `${prefix}note` ADD `idProject` int(12);

ALTER TABLE `${prefix}note` ADD `idle` int(1) DEFAULT '0';

ALTER TABLE `${prefix}project` ADD `handled` int(1) DEFAULT '0',
ADD `handledDate` date DEFAULT NULL;

--ADD qCazelles - Lang
CREATE TABLE `${prefix}language` (
	`id` int(12) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(100) NOT NULL,
	`code` varchar(10) DEFAULT NULL,
	`sortOrder` int(3),
	`idle` int(1) DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `${prefix}resource` ADD COLUMN `idLanguage` int(12) DEFAULT NULL;

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES 
(178,'menuLanguage', 36, 'object', 795, 'ReadWriteList', 0, 'ListOfValues');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES 
(1, 178, 1);
--END ADD qCazelles - Lang

--ADD qCazelles - Lang-Context
CREATE TABLE `${prefix}productlanguage` (
	`id` int(12) unsigned NOT NULL AUTO_INCREMENT,
	`idProduct` int(12) unsigned NOT NULL,
	`idLanguage` int(12) unsigned NOT NULL,
	`creationDate` date NOT NULL,
	`idUser` int(12) unsigned NOT NULL,
	`idle` int(1) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `${prefix}productcontext` (
	`id` int(12) unsigned NOT NULL AUTO_INCREMENT,
	`idProduct` int(12) unsigned NOT NULL,
	`idContext` int(12) unsigned NOT NULL,
	`creationDate` date NOT NULL,
	`idUser` int(12) unsigned NOT NULL,
	`idle` int(1) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--END ADD qCazelles - Lang-Context

--- ADD qCazelles - dateComposition
ALTER TABLE `${prefix}version` ADD `isStarted` INT(1) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `${prefix}version` ADD `realStartDate` DATE NULL DEFAULT NULL;
ALTER TABLE `${prefix}version` ADD `plannedStartDate` DATE NULL DEFAULT NULL;
ALTER TABLE `${prefix}version` ADD `initialStartDate` DATE NULL DEFAULT NULL;

ALTER TABLE `${prefix}version` ADD `isDelivered` INT(1) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `${prefix}version` ADD `realDeliveryDate` DATE NULL DEFAULT NULL;
ALTER TABLE `${prefix}version` ADD `plannedDeliveryDate` DATE NULL DEFAULT NULL;
ALTER TABLE `${prefix}version` ADD `initialDeliveryDate` DATE NULL DEFAULT NULL;
--END ADD qCazelles - dateComposition

--ADD qCazelles - GANTT
INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES 
(179, 'menuVersionsPlanning', 173, 'item', 390, NULL, 0, 'Work');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES 
(1, 179, 1),
(2, 179, 1),
(3, 179, 1);
--END ADD qCazelles - GANTT

--ADD qCazelles - graphTickets

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`, `multiple`) VALUES 
(9, 'priority', 'priorityList', 60, 0, NULL, 0),
(10, 'priority', 'priorityList', 60, 0, NULL, 0),
(11, 'priority', 'priorityList', 50, 0, NULL, 0),
(12, 'priority', 'priorityList', 50, 0, NULL, 0),
(13, 'priority', 'priorityList', 50, 0, NULL, 0),
(14, 'priority', 'priorityList', 50, 0, NULL, 0),
(15, 'priority', 'priorityList', 50, 0, NULL, 0),
(16, 'priority', 'priorityList', 50, 0, NULL, 0),
(17, 'priority', 'priorityList', 40, 0, NULL, 0),
(18, 'priority', 'priorityList', 40, 0, NULL, 0);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `idle`, `orientation`, `hasCsv`) VALUES 
(73, 'reportTicketOpenedClosed', 3, 'ticketOpenedClosedReport.php', 392, 0, 'L', 0); 

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES 
(1, 73, 1),
(2, 73, 1),
(3, 73, 1);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`, `multiple`) VALUES 
(73, 'idProject', 'projectList', 10, 0, 'currentProject', 0),
(73, 'idTicketType', 'ticketType', 20, 0, NULL, 0),
(73, 'idProduct', 'productList', 30, 0, NULL, 0),
(73, 'nbOfDays', 'intInput', 40, 0, 30, 0),
(73, 'priority', 'priorityList', 50, 0, NULL, 0);

--REPORT FOR PRODUCT (Version)

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `idle`, `orientation`, `hasCsv`) VALUES 
(74, 'reportTicketYearlyByProduct', 3, 'ticketYearlyReportByProduct.php', 395, 0, 'L', 0);

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES 
(1, 74, 1),
(2, 74, 1),
(3, 74, 1);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`, `multiple`) VALUES 
(74, 'idProduct', 'productList', 10, 0, NULL, 0),
(74, 'idVersion', 'versionList', 20, 0, NULL, 0),
(74, 'year', 'year', 30, 0, 'currentYear', 0),
(74, 'idTicketType', 'ticketType', 40, 0, NULL, 0),
(74, 'requestor', 'requestorList', 40, 0, NULL, 0),
(74, 'issuer', 'userList', 60, 0, NULL, 0),
(74, 'responsible', 'resourceList', 70, 0, NULL, 0),
(74, 'priority', 'priorityList', 80, 0, NULL, 0);

--END ADD qCazelles - graphTickets

-- ADD by qCazelles - Predefined Actions
-- Babynus : feature disabled do to not stable feature
--INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES 
--(180,'menuPredefinedAction', 88, 'object', '625', 'ReadWriteEnvironnement', 0, 'Automation');

--INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES 
--(1, 180, 1);

CREATE TABLE `${prefix}predefinedaction` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) UNSIGNED DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `idActionType` int(12) UNSIGNED DEFAULT NULL,
  `description` mediumtext,
  `creationDate` date DEFAULT NULL,
  `idUser` int(12) UNSIGNED DEFAULT NULL,
  `idStatus` int(12) UNSIGNED DEFAULT NULL,
  `idContact` int(12) UNSIGNED DEFAULT NULL,
  `idResource` int(12) UNSIGNED DEFAULT NULL,
  `initialDueDateDelay` int(3) UNSIGNED DEFAULT NULL,
  `actualDueDateDelay` int(3) UNSIGNED DEFAULT NULL,
  `result` mediumtext,
  `idPriority` int(12) UNSIGNED DEFAULT NULL,
  `idEfficiency` int(12) UNSIGNED DEFAULT NULL,
  `isPrivate` int(1) UNSIGNED DEFAULT '0',
  `idle` int(1) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- END ADD by qCazelles - Predefined Actions