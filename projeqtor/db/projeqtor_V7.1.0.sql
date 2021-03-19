-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 7.1.0                                       //
-- // Date : 2018-04-23                                     //
-- ///////////////////////////////////////////////////////////

-- ===========================================================
-- #3343 - Multi Client
-- ===========================================================

CREATE TABLE `${prefix}otherclient` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `refType` varchar(100) DEFAULT NULL,
  `refId` int(12) unsigned NOT NULL,
  `idClient` int(12) unsigned NOT NULL,
  `comment` varchar(4000), 
  `creationDate` datetime, 
  `idUser` int(12) unsigned default null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE INDEX otherclientRef ON `${prefix}otherclient` (refType, refId);
CREATE INDEX otherclientVersion ON `${prefix}otherclient` (idClient);
CREATE INDEX otherclientUser ON `${prefix}otherclient` (idUser);

-- ===========================================================
-- #3344 - New Report : Ticket by Customer
-- ===========================================================

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`,`hasCsv`) VALUES 
(83, 'clientsForVersion', 3, 'clientsForVersion.php', 399, 1);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`, `multiple`) VALUES
(83,'idProduct','productList',10,0,null,0),
(83,'idProductVersion','productVersionList',20,0,null,0),
(83,'listTickets','boolean',30,0,null,0),
(83,'idStatus','statusList',40,0,null,1);

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES 
(1, 83, 1);

-- ===========================================================
-- #3339 Resource Team - gautier 
-- ===========================================================
INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(188,'menuResourceTeam', 14, 'object', 505, 'ReadWriteEnvironment', 0, 'Work EnvironmentalParameter');
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES 
(1,188,1);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES 
(1,188, 1000001);

ALTER TABLE `${prefix}resource` ADD `isResourceTeam` int(1) UNSIGNED DEFAULT 0;

ALTER TABLE `${prefix}assignment` ADD `isResourceTeam` int(1) UNSIGNED DEFAULT 0;
ALTER TABLE `${prefix}assignment` ADD `capacity` decimal(5,2) UNSIGNED DEFAULT NULL;

CREATE TABLE `${prefix}resourceteamaffectation` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idResourceTeam` int(12) unsigned NOT NULL,
  `idResource` int(12) unsigned NOT NULL,
  `rate` int(3) unsigned default null,
  `description`  mediumtext,
  `idle` int(1) unsigned DEFAULT 0,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- =============================================================
-- Default Copy To
-- =============================================================

ALTER TABLE `${prefix}copyable` ADD `idDefaultCopyable` int(12) unsigned DEFAULT NULL;

UPDATE `${prefix}copyable` SET idDefaultCopyable=13 WHERE id=14;
UPDATE `${prefix}copyable` SET idDefaultCopyable=15 WHERE id=13;
UPDATE `${prefix}copyable` SET idDefaultCopyable=8 WHERE id=6;
UPDATE `${prefix}copyable` SET idDefaultCopyable=5 WHERE id=16;

-- ==============================================================
-- IGE
-- ==============================================================
UPDATE ${prefix}productlanguage set scope = 'Component' where idProduct in (select id from ${prefix}product where scope = 'Component');
UPDATE ${prefix}productlanguage set scope = 'Product' where idProduct in (select id from ${prefix}product where scope = 'Product');

-- ==============================================================
-- Gautier
-- ==============================================================

ALTER TABLE `${prefix}deliverable` ADD `initialDate` DATE NULL DEFAULT NULL;
ALTER TABLE `${prefix}delivery` ADD `initialDate` DATE NULL DEFAULT NULL;

--- ================================================================
--  FIX
--- ================================================================
UPDATE `${prefix}dependency` SET predecessorRefType='Project' where predecessorRefType='Replan';
UPDATE `${prefix}dependency` SET successorRefType='Project' where successorRefType='Replan';

--- ================================================================
--  Dashboard of Requirements
--- ================================================================
-- BEGIN - ADD qCazelles - Requirements dashboard - Ticket 90

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(189, 'menuDashboardRequirement', 110, 'item', 165, NULL, 0, 'RequirementTest');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowaccess`) VALUES
(1, 189, 1);

--END - ADD qCazelles - Requirements dashboard - Ticket 90

-- =================================================================
-- IGE Reports
-- =================================================================

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `idle`, `orientation`, `hasCsv`, `hasView`, `hasPrint`, `hasPdf`, `hasToday`, `hasFavorite`, `hasWord`, `hasExcel`) VALUES 
(84,'reportRequirementOpenQuestion',8,'requirementOpenQuestion.php',880,0,'L',0,1,1,1,1,1,0,0);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`, `multiple`) VALUES 
(84,'idProject','projectList',10,0,'currentProject',0),
(84,'idProduct','productList',20,0,null,0),
(84,'idVersion','versionList',30,0,null,0);

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES
(1, 84, 1),
(2, 84, 1),
(3, 84, 1);

ALTER TABLE `${prefix}type` ADD `lockUseOnlyForCC` int(1) unsigned default '0';
