-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 6.2.0                                       //
-- // Date : 2017-03-10                                    //
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}type` ADD `priority` int(3) unsigned DEFAULT NULL;

CREATE TABLE `${prefix}subscription` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idAffectable` int(12) unsigned DEFAULT NULL,
  `refType` varchar(100) DEFAULT NULL,
  `refId` int(12) unsigned DEFAULT NULL,
  `creationDateTime` datetime DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `comment` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX `subscriptionAffectable` ON `${prefix}subscription` (`idAffectable`);
CREATE INDEX `subscriptionReference` ON `${prefix}subscription` (`refType`,`refId`);

INSERT INTO `${prefix}checklistable` (`id`, `name`, `idle`) VALUES 
(17, 'ProductVersion', '0'),
(18, 'ComponentVersion', '0'),
(19, 'Product', '0'),
(20, 'Component', '0'),
(21, 'Incoming', '0'), 
(22, 'Deliverable', '0');

INSERT INTO `${prefix}linkable` (`id`, `name`, `idle`, `idDefaultLinkable`) VALUES 
(23, 'Bill', '0', '18');

INSERT INTO `${prefix}linkable` (`id`, `name`, `idle`, `idDefaultLinkable`) VALUES 
(24, 'Term', '0', '23');

INSERT INTO `${prefix}habilitationother` (idProfile,scope,rightAccess) VALUES 
(1,'subscription','4'),
(3,'subscription','3');

ALTER TABLE `${prefix}accessscope` ADD `isSpecific` int(1) unsigned DEFAULT 1;
ALTER TABLE `${prefix}accessscope` ADD `nameSpecific` varchar(100) DEFAULT NULL;
UPDATE `${prefix}accessscope` set `isSpecific`=0 WHERE accessCode='RES';
UPDATE `${prefix}accessscope` set `nameSpecific`=replace(`name`,'accessScope','accessScopeSpecific') WHERE `isSpecific`=1;
UPDATE `${prefix}habilitationother` set `rightAccess`='2' WHERE `rightAccess`='5' and `scope` in ('imputation','workValid','diary');

ALTER TABLE `${prefix}statusmail` ADD `mailToSubscribers` int(1) unsigned DEFAULT 0;

ALTER TABLE `${prefix}indicatordefinition` ADD `mailToSubscribers` int(1) unsigned DEFAULT 0,
ADD `alertToSubscribers` int(1) unsigned DEFAULT 0;

CREATE TABLE `${prefix}catalog` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idCatalogType` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(400) DEFAULT NULL,
  `detail` varchar(400)  DEFAULT NULL,
  `nomenclature` varchar(100)  DEFAULT NULL,
  `specification` mediumtext  DEFAULT NULL,
  `unitCost` decimal(10,2)  DEFAULT NULL,
  `idMeasureUnit` int(12) unsigned DEFAULT NULL,
  `idProduct` int(12) unsigned DEFAULT NULL,
  `idProductVersion` int(12) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `quantity` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `${prefix}billline`
ADD idCatalog int(12) unsigned DEFAULT NULL;

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES 
(174, 'menuCatalog', 152, 'object', 286, 'Project', 0, 'Financial'),
(175, 'menuCatalogType', 79, 'object', 858, 'ReadWriteType', 0, 'Type');

INSERT INTO ${prefix}habilitation (idProfile, idMenu, allowAccess) VALUES 
(1, 174, 1),
(1, 175, 1),
(2, 175, 0),
(3, 175, 0),
(4, 175, 0),
(5, 175, 0),
(6, 175, 0),
(7, 175, 0);

INSERT INTO `${prefix}type` (`scope`, `name`, `idle`) VALUES 
('Catalog', 'Product',0),
('Catalog', 'Service',0);

ALTER TABLE `${prefix}quotation`
ADD `idRecipient` int(12) unsigned DEFAULT NULL;

UPDATE `${prefix}menu` SET `idle`=1 WHERE id=173;

CREATE TABLE `${prefix}extrareadonlyfield` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `scope` varchar(100),
  `idType` int(12) unsigned DEFAULT NULL,
  `field` varchar(100),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE `${prefix}extrarequiredfield` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `scope` varchar(100),
  `idType` int(12) unsigned DEFAULT NULL,
  `field` varchar(100),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}mailable` (`id`, `name`, `idle`) VALUES 
(27,'Incoming', '0'), 
(28,'Deliverable', '0');

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES
(62, 'showIdle', 'boolean', 70, '0');

--- ADD BY Marc TABARY - 2017-03-03 - SET VALUE OF XXX, YYY, ZZZ IN 'alertOverXXXwarningOverYYYokUnderYYY'
ALTER TABLE `${prefix}organization` ADD COLUMN `alertOverPct` INT(3) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `${prefix}organization` ADD COLUMN `warningOverPct` INT(3) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `${prefix}organization` ADD COLUMN `okUnderPct` INT(3) unsigned NOT NULL DEFAULT '0';
--- END ADD BY Marc TABARY - 2017-03-03 - SET VALUE OF XXX, YYY, ZZZ IN 'alertOverXXXwarningOverYYYokUnderYYY'

--- ADD BY Marc TABARY - 2017-03-08 - PERIODIC YEAR BUDGET ELEMENT
ALTER TABLE `${prefix}organization` ADD COLUMN `idleDateTime` DATETIME DEFAULT NULL;
ALTER TABLE `${prefix}budgetelement` ADD COLUMN `idleDateTime` DATETIME DEFAULT NULL;
--- END ADD BY Marc TABARY - 2017-03-08 - PERIODIC YEAR BUDGET ELEMENT

-- ADD BY Marc TABARY - 2017-03-21 - IMPORT ORGANIZATION AND BUDGET ELEMENT
INSERT INTO `${prefix}importable` ( `name`,`idle`) VALUES ('Organization',0);
-- END ADD BY Marc TABARY - 2017-03-21 - IMPORT ORGANIZATION AND BUDGET ELEMENT

--- ADD BY Marc TABARY - 2017-02-21 - ORGANIZATION & RESOURCE VISIBILITY
INSERT INTO `${prefix}list` (`id`, `list`, `name`, `code`, `sortOrder`, `idle`) VALUES
(1001, 'orgaSubOrga', 'displayAll', 'all', 10, 0),
(1002, 'orgaSubOrga', 'displaySubOrga', 'subOrga', 20, 0),
(1003, 'orgaSubOrga', 'displayOrga', 'orga', 30, 0),
(104,'teamOrga','displaySubOrga','subOrga',20,0);

 UPDATE `${prefix}list` SET `sortOrder`=30 WHERE `id`=102;
 UPDATE `${prefix}list` SET `sortOrder`=40 WHERE `id`=103;
--- END ADD BY Marc TABARY - 2017-02-21 - ORGANIZATION & RESOURCE VISIBILITY

ALTER TABLE `${prefix}assignment` ADD `optional` int(1) unsigned DEFAULT '0';

ALTER TABLE `${prefix}project` CHANGE `creationDate` `creationDate` date;
ALTER TABLE `${prefix}callfortender` CHANGE `creationDate` `creationDate` date;
ALTER TABLE `${prefix}tender` CHANGE `creationDate` `creationDate` date;
ALTER TABLE `${prefix}organization` CHANGE `creationDate` `creationDate` date;

ALTER TABLE `${prefix}planningelement` CHANGE `totalAssignedCost` `totalAssignedCost` DECIMAL(12,2);
ALTER TABLE `${prefix}planningelement` CHANGE `totalPlannedCost` `totalPlannedCost` DECIMAL(12,2);
ALTER TABLE `${prefix}planningelement` CHANGE `totalRealCost` `totalRealCost` DECIMAL(12,2);
ALTER TABLE `${prefix}planningelement` CHANGE `totalLeftCost` `totalLeftCost` DECIMAL(12,2);
ALTER TABLE `${prefix}planningelement` CHANGE `totalValidatedCost` `totalValidatedCost` DECIMAL(12,2);
ALTER TABLE `${prefix}planningelement` CHANGE `reserveAmount` `reserveAmount` DECIMAL(12,2);

ALTER TABLE `${prefix}budgetelement` CHANGE `totalAssignedCost` `totalAssignedCost` DECIMAL(12,2);
ALTER TABLE `${prefix}budgetelement` CHANGE `totalPlannedCost` `totalPlannedCost` DECIMAL(12,2);
ALTER TABLE `${prefix}budgetelement` CHANGE `totalRealCost` `totalRealCost` DECIMAL(12,2);
ALTER TABLE `${prefix}budgetelement` CHANGE `totalLeftCost` `totalLeftCost` DECIMAL(12,2);
ALTER TABLE `${prefix}budgetelement` CHANGE `totalValidatedCost` `totalValidatedCost` DECIMAL(12,2);
ALTER TABLE `${prefix}budgetelement` CHANGE `reserveAmount` `reserveAmount` DECIMAL(12,2);