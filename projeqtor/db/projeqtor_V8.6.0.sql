-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.6.0                                       //
-- // Date : 2020-06-22                                     //
-- ///////////////////////////////////////////////////////////

-- START FUNCTIONAL UPDATES

INSERT INTO `${prefix}module` (`id`,`name`,`sortOrder`,`idModule`,`idle`,`active`) VALUES
(20,'moduleGestionCA','540',5,0,0);

UPDATE `${prefix}menu` SET `sortOrder`='120' WHERE `id`='252';

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`,`menuClass`) VALUES
(254, 'menuConsultationValidation', 7, 'item', 119, Null, 0, 'Work');

INSERT INTO `${prefix}modulemenu` (`idModule`,`idMenu`,`hidden`,`active`) VALUES
(3,254,0,(select `active` from `${prefix}module` where id=3));

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1,254,1),
(2,254,1),
(3,254,1);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1,254,8),
(2,254,8),
(3,254,8);

UPDATE `${prefix}report` SET hasExcel=1 WHERE id in (1,2,3);

CREATE TABLE `${prefix}consolidationvalidation` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '12',
  `idProject` int(12) DEFAULT NULL COMMENT '12',
  `idResource` int(12) DEFAULT NULL COMMENT '12',
  `revenue` decimal(11,2) unsigned DEFAULT NULL,
  `validatedWork` decimal(14,5) unsigned DEFAULT NULL,
  `realWork` decimal(14,5) unsigned DEFAULT NULL,
  `realWorkConsumed` decimal(14,5) unsigned DEFAULT NULL,
  `leftWork` decimal(14,5) unsigned DEFAULT NULL,
  `plannedWork` decimal(14,5) unsigned DEFAULT NULL,
  `margin` decimal (14,5) DEFAULT NULL,
  `validationDate` date,
  `month` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;


CREATE TABLE `${prefix}lockedImputation` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '12',
  `idProject` int(12) DEFAULT NULL COMMENT '12',
  `idResource` int(12) DEFAULT NULL COMMENT '12',
  `month` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}habilitationother` (idProfile,scope,rightAccess) VALUES 
(1,'lockedImputation','1'),
(2,'lockedImputation','1'),
(3,'lockedImputation','1'),
(4,'lockedImputation','2'),
(5,'lockedImputation','2'),
(6,'lockedImputation','2'),
(7,'lockedImputation','2'),
(1,'validationImputation','1'),
(2,'validationImputation','1'),
(3,'validationImputation','1'),
(4,'validationImputation','2'),
(5,'validationImputation','2'),
(6,'validationImputation','2'),
(7,'validationImputation','2');

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `hasExcel`) VALUES
(110, 'reportConsolidationValidation',7, 'consultationValidation.php', 760,'1'),
(111, 'reportLeftWork',1, 'leftWork.php', 199,'1'),
(112, 'reportWorkWeekDetail',1, 'workDetailed.php', 131,'1'),
(113, 'reportWorkMonthDetail',1, 'workDetailed.php', 132,'1'),
(114, 'reportWorkYearDetail',1, 'workDetailed.php', 133,'1');

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES 
(1, 110, 1),
(1, 111, 1),
(1, 112, 1),
(1, 113, 1),
(1, 114, 1);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES 
(110, 'idProject', 'projectList', 10, 'currentProject'),
(110,'idProjectType','projectTypeList',15,null),
(110, 'idOrganization', 'organizationList', 20,null),
(110,'month','month',25,'currentMonth'),
(111, 'idProject', 'projectList', 10, 'currentProject'),
(111,'idProjectType','projectTypeList',15,null),
(111, 'idOrganization', 'organizationList', 20,null),
(112, 'idProject', 'projectList', 10, 'currentProject'),
(112,'idTeam','teamList',15,null),
(112, 'idOrganization', 'organizationList', 20,null),
(112,'week','week',25,'currentWeek'),
(113, 'idProject', 'projectList', 10, 'currentProject'),
(113,'idTeam','teamList',15,null),
(113, 'idOrganization', 'organizationList', 20,null),
(113,'month','month',25,'currentMonth'),
(114, 'idProject', 'projectList', 10, 'currentProject'),
(114,'idTeam','teamList',15,null),
(114, 'idOrganization', 'organizationList', 20,null),
(114,'year','year',25,'currentYear');

INSERT INTO `${prefix}modulereport` (`id`,`idModule`,`idReport`,`hidden`,`active`) VALUES
(91,3,110,0,1),
(92,3,112,0,1),
(93,3,113,0,1),
(94,3,114,0,1);

-- Tags Management

CREATE TABLE `${prefix}tag` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '12',
  `name` varchar(100) DEFAULT NULL,
  `refType` varchar(100) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT 0  COMMENT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE UNIQUE INDEX tagName ON `${prefix}tag` (name);

ALTER TABLE `${prefix}document` ADD `tags` varchar(4000) DEFAULT NULL;

UPDATE `${prefix}menu` SET `sortOrder`=282 WHERE `id`=146;
UPDATE `${prefix}menu` SET `sortOrder`=284 WHERE `id`=174;

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`,`menuClass`) VALUES
(255,'menuCatalogUO',152,'object', 285,'Project',0,'Financial');

INSERT INTO `${prefix}modulemenu` (`idModule`,`idMenu`,`hidden`,`active`) VALUES
(20,255,0,0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1,255,1);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1,255,8);

INSERT INTO `${prefix}parameter` (`parameterCode`, `parameterValue`) VALUES 
('ComplexitiesNumber','3');

CREATE TABLE `${prefix}cataloguo` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '12',
  `name` varchar(200) DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL COMMENT '12',
  `nomemclature` varchar(200) DEFAULT NULL,
  `numberComplexities` int(5) unsigned DEFAULT '0' COMMENT '5',
  `idle` int(1) unsigned DEFAULT '0',
  `idleDate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;
CREATE INDEX cataloguoProject ON `${prefix}cataloguo` (idProject);

CREATE TABLE `${prefix}workunit` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '12',
  `idCatalogUO` int(12) unsigned DEFAULT NULL COMMENT '12',
  `idProject` int(12) unsigned DEFAULT NULL COMMENT '12',
  `reference` varchar(200) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `entering` mediumtext DEFAULT NULL,
  `deliverable` mediumtext DEFAULT NULL,
  `validityDate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE `${prefix}complexity` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '12',
  `idCatalogUO` int(12) unsigned DEFAULT NULL COMMENT '12',
  `name` varchar(200) DEFAULT NULL,
  `idZone` int(12) unsigned DEFAULT NULL COMMENT '12',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE `${prefix}complexityvalues` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '12',
  `idCatalogUO` int(12) unsigned DEFAULT NULL COMMENT '12',
  `idComplexity` int(12) unsigned DEFAULT NULL COMMENT '12',
  `idWorkUnit` int(12) unsigned DEFAULT NULL COMMENT '12',
  `charge` int(12) unsigned DEFAULT NULL COMMENT '12',
  `price` int(12) unsigned DEFAULT NULL COMMENT '12',
  `duration` int(12) unsigned DEFAULT NULL COMMENT '12',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

ALTER TABLE `${prefix}planningelement` 
ADD COLUMN `revenue` decimal(11,2) unsigned DEFAULT NULL,
ADD COLUMN `commandSum` decimal(11,2) unsigned DEFAULT NULL,
ADD COLUMN `billSum` decimal(11,2) unsigned DEFAULT NULL,
ADD COLUMN `idRevenueMode` int(12) unsigned DEFAULT NULL COMMENT '12',
ADD COLUMN `idWorkUnit` int(12) unsigned DEFAULT NULL COMMENT '12',
ADD COLUMN `idComplexity` int(12) unsigned DEFAULT NULL COMMENT '12',
ADD COLUMN `quantity` int(5) unsigned DEFAULT NULL COMMENT '5';

CREATE TABLE `${prefix}revenuemode` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '12',
  `name` varchar(100) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL COMMENT '3',
  `idle` int(1) unsigned DEFAULT '0' COMMENT '1',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}revenuemode` (`id`, `name`,  `sortOrder`, `idle`) VALUES
(1,'fixed',100,0),
(2,'variable',200,0);

INSERT INTO `${prefix}indicator` (`id`, `code`, `type`, `name`, `sortOrder`, `idle`, `targetDateColumnName`) VALUES
(29, 'CACS', 'percent', 'CaMoreThanCommandSum', 430, 0, null),
(30, 'CABS', 'percent', 'CaLessThanBillSum', 440, 0, null);

INSERT INTO `${prefix}indicatorableindicator` (`idIndicatorable`, `nameIndicatorable`, `idIndicator`, `idle`) VALUES 
('8', 'Project', '29', '0'),
('8', 'Project', '30', '0');

ALTER TABLE `${prefix}project` ADD COLUMN `commandOnValidWork` int(1) unsigned default 0 COMMENT '1';

-- New rights management for non project dependant objects

ALTER TABLE `${prefix}accessscope` ADD COLUMN `isNonProject` int(1) unsigned default 0 COMMENT '1';
ALTER TABLE `${prefix}accessscope` ADD COLUMN `isYesNo` int(1) unsigned default 0 COMMENT '1';
ALTER TABLE `${prefix}accessscope` ADD COLUMN `nameNonProject` varchar(100) DEFAULT NULL;

UPDATE `${prefix}accessscope` SET `isNonProject`=1, `nameNonProject`=`name` WHERE accessCode!='PRO';
UPDATE `${prefix}accessscope` SET `isYesNo`=1, `nameNonProject`='accessScopeNoProjectYes' WHERE accessCode ='ALL';
UPDATE `${prefix}accessscope` SET `isYesNo`=1, `nameNonProject`='accessScopeNoProjectNo' WHERE accessCode ='NO';

ALTER TABLE `${prefix}accessprofile` ADD COLUMN `isNonProject` int(1) unsigned default 0 COMMENT '1';
ALTER TABLE `${prefix}accessprofile` ADD COLUMN `isExtended` int(1) unsigned default 0 COMMENT '1';

INSERT INTO `${prefix}accessprofile` (`id`,`isNonProject`, `name`, `description`, `idAccessScopeRead`, `idAccessScopeCreate`, `idAccessScopeUpdate`, `idAccessScopeDelete`, `sortOrder`, `idle`,`isExtended`) VALUES
(1000001, 1, 'accessProfileRestrictedManager', 'Create all, update all, delete all', 4, 4, 4, 4, 100, 0, 0),
(1000002, 1, 'accessProfileRestrictedReader', 'Create none, update none, delete none', 4, 1, 1, 1, 200, 0, 0),
(1000003, 1, 'accessProfileRestrictedUpdater', 'Create none, update all, delete none', 4, 1, 4, 1, 300, 0, 0),
(1000004, 1, 'accessProfileResponsible', 'Create none, update responsible, delete none', 4, 1, 5, 1, 400, 0, 1),
(1000005, 1, 'accessProfileRestrictedCreator', 'Create all, update own, delete own', 4, 4, 2, 2, 500, 0, 1);

UPDATE `${prefix}menu` SET sortOrder=1245 where id=47;

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`,`menuClass`,`isAdminMenu`) VALUES
(256, 'menuAccessProfileNoProject', 37, 'object', 1255, Null, 0, 'HabilitationParameter', 1);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1,256,1);

UPDATE `${prefix}menu` set level='ReadWriteConfiguration' WHERE id IN (86,87,141,142);

UPDATE `${prefix}menu` set level='ReadWriteTool' WHERE id IN (223,51);

UPDATE `${prefix}menu` set level='ReadWriteAutomation' WHERE id IN (59,184,68,89,90,169,116,130,162,186);

UPDATE `${prefix}accessright` set idAccessProfile=1000001 where idMenu in (237,229,236,226,51) and idAccessProfile=8;
UPDATE `${prefix}accessright` set idAccessProfile=1000002 where idMenu in (51) and idAccessProfile=9;
UPDATE `${prefix}accessright` set idAccessProfile=1000001 where idMenu in (51) and idAccessProfile=7;

ALTER TABLE `${prefix}asset` 
ADD COLUMN `idUser` int(12) unsigned DEFAULT NULL COMMENT '12',
ADD COLUMN `creationDateTime` datetime DEFAULT NULL,
ADD COLUMN `lastUpdateDateTime` datetime DEFAULT NULL;

ALTER TABLE `${prefix}location` 
ADD COLUMN `idLocation` int(12) unsigned DEFAULT NULL COMMENT '12';

-- Fix for menu & habilitation

UPDATE `${prefix}menu` set level='ReadWriteType', idle=0, menuClass='Type' WHERE id IN (229,236,226);

ALTER TABLE `${prefix}statusmail` ADD COLUMN `mailToFinancialResponsible` int(1) unsigned default 0 COMMENT '1';

-- Add parameter for reports

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`, `multiple`) VALUES 
(19, 'showAdminProj', 'boolean', 100, 0, 0, 0),
(20, 'showAdminProj', 'boolean', 100, 0, 0, 0),
(60, 'showAdminProj', 'boolean', 100, 0, 0, 0),
(76, 'showAdminProj', 'boolean', 100, 0, 0, 0),
(77, 'showAdminProj', 'boolean', 100, 0, 0, 0);

--Add required for checklist
ALTER TABLE `${prefix}checklistdefinitionline` ADD `required` int(1) unsigned default 0 COMMENT '1';

-- ==========================================
-- Add idProject ot History
-- ==========================================

ALTER TABLE `${prefix}history` ADD `idProject` INT(12) NULL DEFAULT NULL;
ALTER TABLE `${prefix}historyarchive` ADD `idProject` INT(12) NULL DEFAULT NULL;

UPDATE ${prefix}history set idProject=(select idProject from ${prefix}ticket e where e.id=${prefix}history.refId and ${prefix}history.refType='Ticket') where refType='Ticket';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}activity e where e.id=${prefix}history.refId and ${prefix}history.refType='Activity') where refType='Activity';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}milestone e where e.id=${prefix}history.refId and ${prefix}history.refType='Milestone') where refType='Milestone';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}risk e where e.id=${prefix}history.refId and ${prefix}history.refType='Risk') where refType='Risk';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}action e where e.id=${prefix}history.refId and ${prefix}history.refType='Action') where refType='Action';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}issue e where e.id=${prefix}history.refId and ${prefix}history.refType='Issue') where refType='Issue';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}meeting e where e.id=${prefix}history.refId and ${prefix}history.refType='Meeting') where refType='Meeting';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}decision e where e.id=${prefix}history.refId and ${prefix}history.refType='Decision') where refType='Decision';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}question e where e.id=${prefix}history.refId and ${prefix}history.refType='Question') where refType='Question';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}expense e where e.id=${prefix}history.refId and ${prefix}history.refType='IndividualExpense') where refType='IndividualExpense';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}expense e where e.id=${prefix}history.refId and ${prefix}history.refType='ProjectExpense') where refType='ProjectExpense';
UPDATE ${prefix}history set idProject=id where refType='Project';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}affectation e where e.id=${prefix}history.refId and ${prefix}history.refType='Affectation') where refType='Affectation';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}assignment e where e.id=${prefix}history.refId and ${prefix}history.refType='Assignment') where refType='Assignment';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}document e where e.id=${prefix}history.refId and ${prefix}history.refType='Document') where refType='Document';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}requirement e where e.id=${prefix}history.refId and ${prefix}history.refType='Requirement') where refType='Requirement';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}testcase e where e.id=${prefix}history.refId and ${prefix}history.refType='TestCase') where refType='TestCase';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}testsession e where e.id=${prefix}history.refId and ${prefix}history.refType='TestSession') where refType='TestSession';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}opportunity e where e.id=${prefix}history.refId and ${prefix}history.refType='Opportunity') where refType='Opportunity';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}command e where e.id=${prefix}history.refId and ${prefix}history.refType='Command') where refType='Command';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}quotation e where e.id=${prefix}history.refId and ${prefix}history.refType='Quotation') where refType='Quotation';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}bill e where e.id=${prefix}history.refId and ${prefix}history.refType='Bill') where refType='Bill';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}deliverable e where e.id=${prefix}history.refId and ${prefix}history.refType='Deliverable') where refType='Deliverable';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}deliverable e where e.id=${prefix}history.refId and ${prefix}history.refType='Incoming') where refType='Incoming';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}delivery e where e.id=${prefix}history.refId and ${prefix}history.refType='Delivery') where refType='Delivery';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}tender e where e.id=${prefix}history.refId and ${prefix}history.refType='Tender') where refType='Tender';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}callfortender e where e.id=${prefix}history.refId and ${prefix}history.refType='CallForTender') where refType='CallForTender';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}term e where e.id=${prefix}history.refId and ${prefix}history.refType='Term') where refType='Term';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}changerequest e where e.id=${prefix}history.refId and ${prefix}history.refType='ChangeRequest') where refType='ChangeRequest';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}providerorder e where e.id=${prefix}history.refId and ${prefix}history.refType='ProviderOrder') where refType='ProviderOrder';
UPDATE ${prefix}history set idProject=(select idProject from ${prefix}providerbill e where e.id=${prefix}history.refId and ${prefix}history.refType='ProviderBill') where refType='ProviderBill';

-- ==========================================
-- Fix for PG
-- ==========================================
UPDATE ${prefix}planningmode set id=16 where id=15;
UPDATE ${prefix}planningelement set idPlanningMode=16 where idPlanningMode=15;

-- ==========================================
-- Patchs IGE
-- ==========================================
ALTER TABLE `${prefix}leavetype` ADD COLUMN `sortOrder` int(3) unsigned default NULL COMMENT '3';

ALTER TABLE `${prefix}delivery` ADD COLUMN `idContact` int(12) unsigned DEFAULT NULL COMMENT '12';

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES 
(30, 'limitNbMonth', 'nbMonth',15, null  );
