-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.2.0                                       //
-- // Date : 2019-07-01                                     //
-- ///////////////////////////////////////////////////////////

-- ----------------------------------------------------------------
-- Legal Notice
-- ----------------------------------------------------------------

CREATE TABLE `${prefix}messagelegal` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `description` mediumtext,
  `idUser` int(12) unsigned DEFAULT NULL,
  `startDate` datetime DEFAULT NULL,
  `endDate` datetime DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(223, 'menuMessageLegal', 11, 'object', 521, 'ReadWritePrincipal', 0, 'Admin');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1,223,1);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1,223,8);

CREATE TABLE `${prefix}messagelegalfollowup` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `idMessageLegal` INT(12) NOT NULL,
  `idUser` INT(12) NOT NULL,
  `firstViewDate` datetime DEFAULT NULL,
  `lastViewDate` datetime DEFAULT NULL,
  `acceptedDate` datetime DEFAULT NULL,
  `accepted` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

UPDATE `${prefix}menu` set `level`='ReadWritePrincipal' 
WHERE name in ('menuOrganization','menuBudget','menuProduct','menuProductVersion','menuComponent','menuComponentVersion');

-- ------------------------------------------------------
-- Data Cloning
-- ------------------------------------------------------

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(222, 'menuDataCloning', 11, 'item', 530, Null, 0, 'Admin');

INSERT INTO `${prefix}habilitation` (idProfile, idMenu, allowAccess) VALUES
(1,222,1),
(2,222,1),
(3,222,1),
(4,222,0),
(5,222,0),
(6,222,0),
(7,222,0);

INSERT INTO `${prefix}habilitationother` (idProfile, rightAccess, scope) VALUES
(1,4,'dataCloningRight'),
(2,2,'dataCloningRight'),
(3,6,'dataCloningRight'),
(4,1,'dataCloningRight'),
(5,1,'dataCloningRight'),
(6,1,'dataCloningRight'),
(7,1,'dataCloningRight'),
(1,10,'dataCloningTotal'),
(2,1,'dataCloningTotal'),
(3,3,'dataCloningTotal'),
(4,0,'dataCloningTotal'),
(5,0,'dataCloningTotal'),
(6,0,'dataCloningTotal'),
(7,0,'dataCloningTotal');

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1,222,8),
(2,222,2);

CREATE TABLE `${prefix}datacloning` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `nameDir` varchar(100) DEFAULT NULL,
  `idRequestor` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `idOrigin` int(12) unsigned DEFAULT NULL,
  `versionCode` varchar(100) DEFAULT NULL,
  `requestedDate` datetime DEFAULT NULL,
  `plannedDate` varchar(100) DEFAULT NULL,
  `deletedDate` datetime DEFAULT NULL,
  `requestedDeletedDate` datetime DEFAULT NULL,
  `isRequestedDelete` int(1) unsigned DEFAULT 0,
  `codeError` varchar(100) DEFAULT NULL,
  `isActive` int(1) unsigned DEFAULT 0,
  `idle` int(1) unsigned DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(224, 'menuDataCloningParameter', 13, 'item', 1070, Null, 0, 'Admin');

INSERT INTO `${prefix}habilitation` (idProfile, idMenu, allowAccess) VALUES
(1,224,1);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1,224,8);

INSERT INTO `${prefix}parameter` (`parameterCode`, `parameterValue`) VALUES 
('dataCloningCreationRequest','5'),
('dataCloningPerDay','5'),
('paramPasswordStrength','1'),
('paramAttachmentNum',''),
('paramScreen','top'),
('paramRightDiv','0'),
('contentPaneDetailDivHeight','260'),
('contentPaneDetailDivWidth','410'),
('contentPaneTopDetailDivHeight','0'),
('contentPaneTopDetailDivWidth','0'),
('paramLayoutObjectDetail','4');

INSERT INTO `${prefix}cronexecution` (`cron`, `fileExecuted`, `idle` ,`fonctionName`) VALUES
('*/5 * * * *', '../tool/cronExecutionStandard.php', 0, 'dataCloningCheckRequest');

INSERT INTO `${prefix}module` (`id`, `name`, `sortOrder`, `idModule`, `idle`, `active`) VALUES 
(17,'moduleDataCloning',1200,null,0,0);

INSERT INTO `${prefix}modulemenu` (`idModule`, `idMenu`, `hidden`, `active`) VALUES 
(17,222,0,0),
(17,224,0,0);

-- ------------------------------------------------------
-- Indivcisibility and Minimum threshold
-- ------------------------------------------------------

ALTER TABLE `${prefix}planningelement` ADD COLUMN `indivisibility` int(1) unsigned DEFAULT 0,
ADD COLUMN `minimumThreshold` decimal(7,4) unsigned DEFAULT NULL;

-- ------------------------------------------------------
-- Minor changes
-- ------------------------------------------------------

-- Cleaning unused tables

DROP TABLE `${prefix}noteflux`;

DROP TABLE `${prefix}absence`;

-- Have budget importable

INSERT INTO `${prefix}importable` (`id`, `name`, `idle`) VALUES
(54, 'Budget', 0);

-- Change precision for expenses tax

ALTER TABLE `${prefix}expense` CHANGE `realTaxAmount` `realTaxAmount` DECIMAL(14,5);

-- Add Project to payment (optional)

ALTER TABLE `${prefix}payment` ADD COLUMN `idProject` int(12) unsigned DEFAULT NULL;

ALTER TABLE `${prefix}providerpayment`  ADD COLUMN `idProject` int(12) unsigned DEFAULT NULL;

-- Move Regulated Absence parameters

UPDATE `${prefix}menu` SET `idMenu`=13, `sortOrder`=1060 WHERE id=216;
UPDATE `${prefix}menu` SET `sortOrder`=1061 WHERE id=217;
UPDATE `${prefix}menu` SET `sortOrder`=1062 WHERE id=218;
UPDATE `${prefix}menu` SET `sortOrder`=1063 WHERE id=219;
UPDATE `${prefix}menu` SET `sortOrder`=1064 WHERE id=220; 
UPDATE `${prefix}menu` SET `idMenu`=14, `sortOrder`=602 WHERE id=212; 
-- 
  UPDATE `${prefix}menu` SET `sortOrder`=205 WHERE id=154;