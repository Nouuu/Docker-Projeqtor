-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.5.0                                       //
-- // Date : 2020-03-23                                     //
-- ///////////////////////////////////////////////////////////
-- Patch on V8.4.0


INSERT INTO `${prefix}habilitationother` (idProfile,scope,rightAccess) VALUES 
(1,'validatePlanning','1'),
(2,'validatePlanning','1'),
(3,'validatePlanning','1'),
(4,'validatePlanning','2'),
(6,'validatePlanning','2'),
(7,'validatePlanning','2'),
(5,'validatePlanning','2');

DELETE FROM `${prefix}columnselector` WHERE objectClass='Recipient' and field='bank' and attribute='bank';

ALTER TABLE `${prefix}type` ADD COLUMN `icon` varchar(100);

ALTER TABLE `${prefix}globalview` ADD COLUMN `creationDate` datetime DEFAULT NULL;

ALTER TABLE `${prefix}planningelement` 
ADD COLUMN `unitToDeliver` int(6) unsigned DEFAULT NULL,
ADD COLUMN `unitToRealise` int(6) unsigned DEFAULT NULL,
ADD COLUMN `unitRealised` int(6) unsigned DEFAULT NULL,
ADD COLUMN `unitLeft` int(6) unsigned DEFAULT NULL,
ADD COLUMN `unitProgress` decimal(8,2) DEFAULT NULL,
ADD COLUMN `idProgressMode` int(12) unsigned DEFAULT NULL,
ADD COLUMN `unitWeight` decimal(8,2) DEFAULT NULL,
ADD COLUMN `idWeightMode` int(12) unsigned DEFAULT NULL;

ALTER TABLE `${prefix}planningelementbaseline` 
ADD COLUMN `unitToDeliver` int(6) unsigned DEFAULT NULL,
ADD COLUMN `unitToRealise` int(6) unsigned DEFAULT NULL,
ADD COLUMN `unitRealised` int(6) unsigned DEFAULT NULL,
ADD COLUMN `unitLeft` int(6) unsigned DEFAULT NULL,
ADD COLUMN `unitProgress` decimal(8,2) DEFAULT NULL,
ADD COLUMN `idProgressMode` int(12) unsigned DEFAULT NULL,
ADD COLUMN `unitWeight` decimal(8,2) DEFAULT NULL,
ADD COLUMN `idWeightMode` int(12) unsigned DEFAULT NULL;

CREATE TABLE `${prefix}progressmode` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}progressmode` (`id`, `name`,  `sortOrder`, `idle`) VALUES
(1,'calculated',100,0),
(2,'manual',200,0);

CREATE TABLE `${prefix}weightmode` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;


INSERT INTO `${prefix}weightmode` (`id`, `name`,  `sortOrder`, `idle`) VALUES
(1,'manual',100,0),
(2,'consolidated',200,0),
(3,'UO',300,0);

INSERT INTO `${prefix}parameter` (`parameterCode`, `parameterValue`) VALUES 
('technicalProgress','NO');

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `hasCsv`) VALUES
(108, 'reportTechnicalProgress', 2, 'technicalProgress.php', 227,'1');

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES 
(1, 108, 1);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES 
(108, 'showIdle', 'boolean', 20, 0),
(108, 'idProject', 'projectList', 10, 'currentProject');


-- ======================================
-- Email as ticket
-- ======================================

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`,`menuClass`) VALUES
(250,'menuInputMailbox',88,'object', 693,'Project',0,'Automation');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1,250,1);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1,250,8);

CREATE TABLE `${prefix}inputmailbox` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `serverImap` varchar(200) DEFAULT NULL,
  `imapUserAccount` varchar(200) DEFAULT NULL,
  `pwdImap` varchar(50) DEFAULT NULL,
  `securityConstraint` varchar(10) DEFAULT NULL,
  `allowAttach` int(1) unsigned DEFAULT '0',
  `sizeAttachment` int(6) unsigned DEFAULT '5',
  `idTicketType` int(12) unsigned DEFAULT NULL,
  `idAffectable` int(12) unsigned DEFAULT NULL,
  `idActivity` int(12) unsigned DEFAULT NULL,
  `lastInputDate` datetime DEFAULT NULL,
  `idTicket` int(12) unsigned DEFAULT NULL,
  `totalInputTicket` int(12) unsigned DEFAULT '0',
  `failedRead` int(1) unsigned DEFAULT '0',
  `failedMessage` int(1) unsigned DEFAULT '0',
  `limitOfInputPerHour` int(6) unsigned DEFAULT '0',
  `limitOfHistory` int(6) unsigned DEFAULT '0',
  `idle` int(1) unsigned DEFAULT '0',
  `idleDate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;
CREATE INDEX inputmailboxProject ON `${prefix}inputmailbox` (idProject);

CREATE TABLE `${prefix}inputmailboxhistory` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idInputMailbox` int(12) unsigned DEFAULT NULL,
  `adress` varchar(200) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `result` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

ALTER TABLE `${prefix}type` ADD `canHaveSubActivity` int(1) unsigned DEFAULT '1';

INSERT INTO `${prefix}habilitationother` (idProfile, rightAccess, scope) VALUES
(1,1,'changePriorityOther'),
(3,1,'changePriorityOther');

UPDATE `${prefix}habilitationother` SET scope='changePriorityProj'
WHERE scope='changePriority';

ALTER TABLE `${prefix}location` 
ADD `designation` varchar(200) DEFAULT NULL,
ADD `street` varchar(200) DEFAULT NULL,
ADD `complement` varchar(200) DEFAULT NULL,
ADD `zipCode` varchar(200) DEFAULT NULL,
ADD `city` varchar(200) DEFAULT NULL,
ADD `state` varchar(200) DEFAULT NULL,
ADD `country` varchar(200) DEFAULT NULL;

ALTER TABLE `${prefix}asset` 
ADD `warantyDurationM` int(12) unsigned DEFAULT NULL,
ADD `warantyEndDate` date DEFAULT NULL,
ADD `depreciationDurationY` int(4) unsigned DEFAULT NULL,
ADD `needInsurance` int(1) unsigned DEFAULT '0',
ADD `purchaseValueHTAmount` decimal(11,2) DEFAULT NULL,
ADD `purchaseValueTTCAmount` decimal(11,2) DEFAULT NULL;

ALTER TABLE `${prefix}approver` 
ADD `disapproved` int(1) unsigned DEFAULT '0',
ADD `disapprovedDate` datetime default NULL,
ADD `disapprovedComment` varchar(400) default NULL;

ALTER TABLE `${prefix}documentversion` ADD `disapproved` int(1) unsigned DEFAULT '0';

-- ======================================      
-- Planned Work Manual
-- ======================================

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`,`menuClass`) VALUES
(251,'menuInterventionMode',36,'object', 899,'ReadWriteList',0,'ListOfValues'),
(252, 'menuPlannedWorkManual', 7, 'item', 119, Null, 0, 'Work'),
(253, 'menuConsultationPlannedWorkManual', 7, 'item', 150, Null, 0, 'Work');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1,251,1),
(1, 252, 1),
(2, 252, 1),
(3, 252, 1),
(1, 253, 1),
(2, 253, 1),
(3, 253, 1),
(4, 253, 1);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1,251,8),
(1,252,8),
(2,252,8),
(3,252,8),
(1,253,8),
(2,253,8),
(3,253,8),
(4,253,8);

CREATE TABLE `${prefix}plannedworkmanual` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idResource` int(12) unsigned NOT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `refType`  varchar(100) DEFAULT NULL,
  `refId` int(12) unsigned DEFAULT NULL,
  `idAssignment` int(12) unsigned default NULL,
  `work` NUMERIC(8,5) UNSIGNED,
  `workDate` date DEFAULT NULL,
  `day`  varchar(8),
  `week` varchar(6),
  `month` varchar(6),
  `year` varchar(4),
  `dailyCost` NUMERIC(7,2) DEFAULT NULL,
  `cost` NUMERIC(11,2) DEFAULT NULL,
  `period` varchar(2),
  `inputUser` int(12) unsigned DEFAULT NULL,
  `inputDateTime` datetime DEFAULT NULL,
  `idInterventionMode` int(12) unsigned DEFAULT NULL,
  `idWork` int(12) unsigned DEFAULT NULL,
  `idPlannedWork` int(12) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE INDEX plannedworkmanualDay ON `${prefix}plannedworkmanual` (day);
CREATE INDEX plannedworkmanualWeek ON `${prefix}plannedworkmanual` (week);
CREATE INDEX plannedworkmanualMonth ON `${prefix}plannedworkmanual` (month);
CREATE INDEX plannedworkmanualYear ON `${prefix}plannedworkmanual` (year);
CREATE INDEX plannedworkmanualRef ON `${prefix}plannedworkmanual` (refType, refId);
CREATE INDEX plannedworkmanualResource ON `${prefix}plannedworkmanual` (idResource);
CREATE INDEX plannedworkmanualAssignment ON `${prefix}plannedworkmanual` (idAssignment);    
      
CREATE TABLE `${prefix}interventionmode` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `letter` varchar(3) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `idleDate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}interventionmode` (name, letter, sortOrder) VALUES
('Teleworking','T',10),
('On remote site','R',20),
('On-call duty','C',30),
('Hotline','H',40);

INSERT INTO `${prefix}planningmode` (`id`, `applyTo`, `name`, `code`, `sortOrder`, `idle`, `mandatoryStartDate`, `mandatoryEndDate`) VALUES
(23, 'Activity', 'PlanningModeManual', 'MAN', 900, 0 , 0, 0);

CREATE TABLE `${prefix}interventioncapacity` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `refType`  varchar(100) DEFAULT NULL,
  `refId` int(12) unsigned DEFAULT NULL,
  `month` varchar(6) DEFAULT NULL,
  `fte` decimal(3,1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE UNIQUE INDEX interventioncapacityRef ON `${prefix}interventioncapacity` (`refType`,`refId`,`month`);

ALTER TABLE `${prefix}assignment` ADD `manual` int(1) unsigned DEFAULT '0';
ALTER TABLE `${prefix}work` ADD `manual` int(1) unsigned DEFAULT '0';
ALTER TABLE `${prefix}plannedwork` ADD `manual` int(1) unsigned DEFAULT '0';
ALTER TABLE `${prefix}plannedworkbaseline` ADD `manual` int(1) unsigned DEFAULT '0';

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `hasPdf`) VALUES
(109, 'reportShowIntervention', 2, 'plannedWorkManual.php', 285, 0);

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES 
(1, 109, 1);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES 
(109,'idResource','resourceList',5,'currentResource'), 
(109,'idProject','projectList',10,'currentProject'), 
(109,'idTeam','teamList',20,null), 
(109,'idOrganization','organizationList',15,null),
(109,'month','month',40,'currentMonth');

-- Fix for Financial situation
ALTER TABLE `${prefix}projectsituation` CHANGE `name` `name` VARCHAR(200);

ALTER TABLE `${prefix}planningelement` 
ADD COLUMN `color` varchar(7) DEFAULT NULL;

ALTER TABLE `${prefix}planningelementbaseline` 
ADD COLUMN `color` varchar(7) DEFAULT NULL;

UPDATE `${prefix}planningelement` SET `color`=(select `color` from `${prefix}project` where id=refId) WHERE refType='Project';
UPDATE `${prefix}planningelementbaseline` SET `color`=(select `color` from `${prefix}project` where id=refId) WHERE refType='Project';

DELETE FROM `${prefix}columnselector` where scope='list' and objectClass like 'ProjectSituation%' and field='name';

ALTER TABLE `${prefix}situationable` ADD COLUMN `type` VARCHAR(100);
UPDATE `${prefix}situationable` SET `type`='Income' WHERE name in ('Quotation','Command','Bill');
UPDATE `${prefix}situationable` SET `type`='Expense' WHERE name in ('CallForTender','Tender','ProviderOrder','ProviderBill');

