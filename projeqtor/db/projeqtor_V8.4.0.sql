-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.4.0                                       //
-- // Date : 2020-01-14                                     //
-- ///////////////////////////////////////////////////////////

UPDATE `${prefix}copyable` SET `id`=27, `sortOrder` = '127' WHERE `name`='ChangeRequest';
UPDATE `${prefix}menu` SET `sortOrder` = '201' WHERE `name` = 'menuExpenses';
UPDATE `${prefix}menu` SET `sortOrder` = '202' WHERE `name` = 'menuBudget';
UPDATE `${prefix}menu` SET `sortOrder` = '205' WHERE `name` = 'menuCallForTender';
UPDATE `${prefix}menu` SET `sortOrder` = '206' WHERE `name` = 'menuTender';
UPDATE `${prefix}menu` SET `sortOrder` = '207' WHERE `name` = 'menuProviderOrder';
UPDATE `${prefix}menu` SET `sortOrder` = '208' WHERE `name` = 'menuProviderTerm';
UPDATE `${prefix}menu` SET `sortOrder` = '209' WHERE `name` = 'menuProviderBill';
UPDATE `${prefix}menu` SET `sortOrder` = '210' WHERE `name` = 'menuProviderPayment';
UPDATE `${prefix}menu` SET `sortOrder` = '211' WHERE `name` = 'menuIndividualExpense';
UPDATE `${prefix}menu` SET `sortOrder` = '295' WHERE `name` = 'menuRiskManagementPlan';
UPDATE `${prefix}measureunit` SET `sortOrder` = '50' WHERE `name` = 'month';

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`,`menuClass`) VALUES
(228,'menuSupplierContract',151,'object', 204,'Project',0,'Financial'),
(229,'menuSupplierContractType',79,'object',926,NULL,NULL,0),
(231,'menuRenewal',36,'object',897,'ReadWriteList',0,'ListOfValues'),
(232,'menuGanttSupplierContract',151,'item', 204,'Project',0,'Financial'),
(233,'menuHierarchicalBudget',151,'item', 203,'Project',0,'Financial'),
(234,'menuClientContract',152,'object', 228,'Project',0,'Financial'),
(235,'menuGanttClientContract',152,'item', 229,'Project',0,'Financial'),
(236,'menuClientContractType',79,'object',928,NULL,NULL,0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1,228,1),
(1,229,1),
(1,231,1),
(1,232,1),
(1,233,1),
(1,234,1),
(1,235,1),
(1,236,1);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1,228,8),
(1,229,8),
(1,232,8),
(1,233,8),
(1,234,8),
(1,235,8),
(1,236,8);


INSERT INTO `${prefix}modulemenu` (`idModule`,`idMenu`,`hidden`,`active`) VALUES
 (6,228,0,1),
 (6,229,1,1),
 (6,231,1,1),
 (6,232,0,1),
 (6,233,0,1),
 (7,234,0,1),
 (7,235,0,1),
 (7,236,1,1);
 
 INSERT INTO `${prefix}checklistable` (`id`,`name`, `idle`) VALUES 
(34,'ChangeRequest', 0),
(35,'SupplierContract', 0),
(36,'ClientContract', 0),
(37,'Asset',0);

INSERT INTO `${prefix}notifiable` (`name`, `idle`) VALUES 
('SupplierContract', 0),
('ClientContract', 0),
('Asset',0);

 INSERT INTO `${prefix}mailable` (`id`,`name`, `idle`) VALUES 
(42,'SupplierContract',0),
(43,'ClientContract', 0),
(44,'Asset',0);

INSERT INTO `${prefix}importable` (`id`, `name`, `idle`) VALUES
(58, 'SupplierContract',0),
(59, 'ClientContract',0),
(60,'Asset',0),
(61,'Brand',0),
(62,'Model',0),
(63,'Location',0);

INSERT INTO `${prefix}linkable` ( `name`, `idle`) VALUES 
('SupplierContract', 0),
('ClientContract', 0),
('Asset',0);

INSERT INTO `${prefix}copyable` (`id`,`name`, `idle`, `sortOrder`) VALUES
(28,'SupplierContract', 0, 128),
(29,'ClientContract', 0, 129),
(30,'Asset',0,130);

 INSERT INTO `${prefix}originable` (`id`,`name`, `idle`) VALUES 
(28,'ChangeRequest', 0),
(29,'SupplierContract', 0),
(30,'ClientContract', 0),
(31,'Asset',0);

 INSERT INTO `${prefix}textable` (`id`,`name`, `idle`) VALUES 
(40,'ChangeRequest', 0),
(41,'SupplierContract', 0),
(42,'ClientContract', 0),
(43,'Asset',0);
 
-- ======================================
-- Supplier Contract 
-- ======================================

CREATE TABLE `${prefix}suppliercontract` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `number` varchar(100) DEFAULT NULL,
  `idSupplierContractType` int(12) unsigned DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idProvider` int(12) unsigned DEFAULT NULL,
  `tenderReference` varchar(100) DEFAULT NULL,
  `startDate`  date DEFAULT NULL,
  `initialContractTerm` int(5) unsigned DEFAULT 0,
  `idUnitDurationContract` int(12) unsigned DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `noticePeriod` int(5) unsigned DEFAULT 0,
  `idUnitDurationNotice` int(12) unsigned DEFAULT NULL,
  `noticeDate` date DEFAULT NULL,
  `deadlineDate` date DEFAULT NULL,
  `periodicityContract` int(5) unsigned DEFAULT 0,
  `periodicityBill` int(5) unsigned DEFAULT 0,
  `idRenewal` int(12) unsigned DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `idContactContract` int(12) unsigned DEFAULT NULL,
  `phoneNumber` varchar(20) DEFAULT NULL,
  `weekPeriod` time DEFAULT NULL,
  `saturdayPeriod` time DEFAULT NULL,
  `sundayAndOffDayPeriod` time DEFAULT NULL,
  `weekPeriodEnd` time DEFAULT NULL,
  `saturdayPeriodEnd` time DEFAULT NULL,
  `sundayAndOffDayPeriodEnd` time DEFAULT NULL,
  `sla` int(1) unsigned DEFAULT '0',
  `idUser` int(12) unsigned DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `handled` int(1) unsigned DEFAULT '0',
  `handledDate` date DEFAULT NULL,
  `done` int(1) unsigned DEFAULT '0',
  `doneDate` date DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `idleDate` date DEFAULT NULL,
  `cancelled` INT(1) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

-- ======================================
-- Client Contract 
-- ======================================

CREATE TABLE `${prefix}clientcontract` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `number` varchar(100) DEFAULT NULL,
  `idClientContractType` int(12) unsigned DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idClient` int(12) unsigned DEFAULT NULL,
  `tenderReference` varchar(100) DEFAULT NULL,
  `startDate`  date DEFAULT NULL,
  `initialContractTerm` int(5) unsigned DEFAULT 0,
  `idUnitDurationContract` int(12) unsigned DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `noticePeriod` int(5) unsigned DEFAULT 0,
  `idUnitDurationNotice` int(12) unsigned DEFAULT NULL,
  `noticeDate` date DEFAULT NULL,
  `deadlineDate` date DEFAULT NULL,
  `periodicityContract` int(5) unsigned DEFAULT 0,
  `periodicityBill` int(5) unsigned DEFAULT 0,
  `idRenewal` int(12) unsigned DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `idContactContract` int(12) unsigned DEFAULT NULL,
  `phoneNumber` varchar(20) DEFAULT NULL,
  `weekPeriod` time DEFAULT NULL,
  `saturdayPeriod` time DEFAULT NULL,
  `sundayAndOffDayPeriod` time DEFAULT NULL,
  `weekPeriodEnd` time DEFAULT NULL,
  `saturdayPeriodEnd` time DEFAULT NULL,
  `sundayAndOffDayPeriodEnd` time DEFAULT NULL,
  `sla` int(1) unsigned DEFAULT '0',
  `idUser` int(12) unsigned DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `handled` int(1) unsigned DEFAULT '0',
  `handledDate` date DEFAULT NULL,
  `done` int(1) unsigned DEFAULT '0',
  `doneDate` date DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `idleDate` date DEFAULT NULL,
  `cancelled` INT(1) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idWorkflow`, `idle`) VALUES 
('SupplierContract', 'management assistance',10,1, 0),
('SupplierContract', 'hosting',20,1, 0),
('SupplierContract', 'technical improvement',30,1, 0),
('SupplierContract', 'maintenance & support',40,1, 0),
('ClientContract', 'management assistance',10,1, 0),
('ClientContract', 'hosting',20,1, 0),
('ClientContract', 'technical improvement',30,1, 0),
('ClientContract', 'maintenance & support',40,1, 0);


CREATE TABLE `${prefix}renewal` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;


CREATE TABLE `${prefix}unitcontract` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}unitcontract` ( `name`, `sortOrder`, `idle`) VALUES
('day',40,0),
('month',50,0),
('year',60,0);


INSERT INTO `${prefix}renewal` (`id`, `name`,  `sortOrder`, `idle`) VALUES
(1,'never',100,0),
(2,'tacit',200,0),
(3,'express',300,0);


-- ======================================
-- Situation
-- ======================================

CREATE TABLE `${prefix}situation` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `refType` varchar(100) DEFAULT NULL,
  `refId` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `situationType` varchar(100) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `comment` mediumtext DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE `${prefix}projectsituation` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `situationNameExpense` varchar(100) DEFAULT NULL,
  `refTypeExpense` varchar(100) DEFAULT NULL,
  `refIdExpense` int(12) unsigned DEFAULT NULL,
  `situationDateExpense` datetime DEFAULT NULL,
  `idResourceExpense` int(12) unsigned DEFAULT NULL,
  `situationNameIncome` varchar(100) DEFAULT NULL,
  `refTypeIncome` varchar(100) DEFAULT NULL,
  `refIdIncome` int(12) unsigned DEFAULT NULL,
  `situationDateIncome` datetime DEFAULT NULL,
  `idResourceIncome` int(12) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE `${prefix}predefinedsituation` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `idSituationable` int(12) unsigned DEFAULT NULL,
  `idType` int(12) unsigned DEFAULT NULL,
  `situation` varchar(100) DEFAULT NULL,
  `comment` mediumtext DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE `${prefix}situationable` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) default NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}situationable` (`id`,`name`,`idle`) VALUES 
(1,'CallForTender',0),
(2,'Tender',0),
(3,'ProviderOrder',0),
(4,'ProviderBill',0),
(5,'Bill',0),
(6,'Quotation',0),
(7,'Command',0);

ALTER TABLE `${prefix}callfortender` ADD `idSituation` int(12) unsigned DEFAULT NULL;
ALTER TABLE `${prefix}tender` ADD `idSituation` int(12) unsigned DEFAULT NULL;
ALTER TABLE `${prefix}providerorder` ADD `idSituation` int(12) unsigned DEFAULT NULL;
ALTER TABLE `${prefix}providerbill` ADD `idSituation` int(12) unsigned DEFAULT NULL;
ALTER TABLE `${prefix}bill` ADD `idSituation` int(12) unsigned DEFAULT NULL;
ALTER TABLE `${prefix}quotation` ADD `idSituation` int(12) unsigned DEFAULT NULL;
ALTER TABLE `${prefix}command` ADD `idSituation` int(12) unsigned DEFAULT NULL;

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`,`menuClass`) VALUES
(244,'menuSituation',74,'menu', 287,null,1,null),
(245,'menuProjectSituation',244,'object', 288,'Project',0,'Financial'),
(246,'menuProjectSituationExpense',244,'object', 289,'Project',0,'Financial'),
(247,'menuProjectSituationIncome',244,'object', 290,'Project',0,'Financial'),
(249,'menuPredefinedSituation',36,'object',898,'ReadWriteList',0,'ListOfValues');

INSERT INTO `${prefix}module` (`id`,`name`,`sortOrder`,`idModule`,`idle`,`active`) VALUES
(19,'moduleSituation','530',5,0,0);

INSERT INTO `${prefix}modulemenu` (`idModule`,`idMenu`,`hidden`,`active`) VALUES
(19,244,0,0),
(19,245,0,0),
(19,246,0,0),
(19,247,0,0),
(19,249,0,0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1,244,1),
(1,245,1),
(1,246,1),
(1,247,1),
(1,249,1);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1,244,8),
(1,245,8),
(1,246,8),
(1,247,8);

INSERT INTO `${prefix}habilitationother` (`idProfile`, `scope`, `rightAccess`) SELECT prf.id , 'situation', 1 FROM `${prefix}profile` prf where prf.profilecode = 'ADM' or prf.profilecode = 'PL';
INSERT INTO `${prefix}habilitationother` (`idProfile`, `scope`, `rightAccess`) SELECT prf.id , 'situation', 2 FROM `${prefix}profile` prf where prf.profilecode != 'ADM' and prf.profilecode != 'PL';
-- ======================================
-- Habilitation Other
-- ======================================
INSERT INTO `${prefix}habilitationother` (`idProfile`, `scope`, `rightAccess`) SELECT prf.id , 'generateProjExpense', 1 FROM `${prefix}profile` prf;

ALTER TABLE `${prefix}term` ADD `idResource` int(12) unsigned DEFAULT NULL , ADD `done` int(1) unsigned DEFAULT '0';

-- ======================================
-- Asset
-- ======================================

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`,`menuClass`) VALUES
(243,'menuAssetManagment',0,'menu', 450,null,1,'Asset'),
(237,'menuAsset',243,'object', 455,'ReadWritePrincipal',0,'Asset'),
(238,'menuLocation',243,'object', 465,'ReadWriteList',0,'Asset'),
(239,'menuBrand',243,'object', 461,'ReadWriteList',0,'Asset'),
(240,'menuModel',243,'object', 463,'ReadWriteList',0,'Asset'),
(241,'menuAssetCategory',243,'object', 460,'ReadWriteList',0,'Asset'),
(248,'menuAssetType',243,'object',459,'ReadWriteType',0,'Asset Type');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1,243,1),
(1,237,1),
(1,238,1),
(1,239,1),
(1,240,1),
(1,248,1),
(1,241,1);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1,237,8),
(1,243,8);

INSERT INTO `${prefix}module` (`id`,`name`,`sortOrder`,`idModule`,`idle`,`active`) VALUES
(18,'moduleAssets','850',null,0,0);

INSERT INTO `${prefix}modulemenu` (`idModule`,`idMenu`,`hidden`,`active`) VALUES
(18,243,1,0),
(18,237,0,0),
(18,238,1,0),
(18,239,0,0),
(18,240,0,0),
(18,248,0,0),
(18,241,0,0);

CREATE TABLE `${prefix}asset` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `idAssetType` int(12) unsigned DEFAULT NULL,
  `idAffectable` int(12) unsigned DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `installationDate` date DEFAULT NULL,
  `decommissioningDate` date DEFAULT NULL,
  `serialNumber` varchar(100) DEFAULT NULL,
  `inventoryNumber` varchar(100) DEFAULT NULL,
  `idProvider` int(12) unsigned DEFAULT NULL,
  `idAsset` int(12) unsigned DEFAULT NULL,
  `idLocation` int(12) unsigned DEFAULT NULL,
  `complement` varchar(200) DEFAULT NULL,
  `idBrand` int(12) unsigned DEFAULT NULL,
  `idModel` int(12) unsigned DEFAULT NULL,
  `idAssetCategory` int(12) unsigned DEFAULT NULL,
  `done` int(1) unsigned DEFAULT '0',
  `doneDate` date DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `idleDate` date DEFAULT NULL,
  `cancelled` INT(1) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;
CREATE INDEX assetType ON `${prefix}asset` (idAssetType);

INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idWorkflow`, `idle`) VALUES 
('Asset', 'Software',10,1,0),
('Asset', 'Computer',20,1,0),
('Asset', 'Printer',30,1,0),
('Asset', 'Server',40,1,0);

CREATE TABLE `${prefix}location` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE `${prefix}brand` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE `${prefix}assetcategory` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}assetcategory` (`name`,`sortOrder`,`idle`) VALUES
('Individual',10,0),
('Collective',20,0);

CREATE TABLE `${prefix}model` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `idAssetType` int(12) unsigned DEFAULT NULL,
  `idBrand` int(12) unsigned DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE `${prefix}productasset` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idAsset` int(12) unsigned DEFAULT NULL,
  `idProductVersion` int(12) unsigned DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `comment` varchar(100) DEFAULT NULL,
  `creationDate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
CREATE INDEX productassetAsset ON `${prefix}productasset` (idAsset);
CREATE INDEX productassetProduct ON `${prefix}productasset` (idProductVersion);

-- IGE #88 - Report of Subscription

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `idle`, `orientation`, `hasCsv`, `hasView`, `hasPrint`, `hasPdf`, `hasToday`, `hasFavorite`, `hasWord`, `hasExcel`, `filterClass`) VALUES
(107, 'reportSubscription', 9, 'reportSubscription.php', 940, 0, 'L', 0, 1, 1, 1, 1, 1, 0, 0, NULL);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`, `multiple`) VALUES
(107, 'idUser', 'userList', 10, 0, NULL, 0),
(107, 'refType', 'objectList', 20, 0, NULL, 0);

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES
(1, 107, 1);

-- IGE #407 ( 

INSERT INTO `${prefix}report` (id, name, idReportCategory, file, sortOrder, idle, orientation, hasCsv, hasView, hasPrint, hasPdf, hasToday, hasFavorite, hasWord, hasExcel, filterClass) VALUES
(105, 'reportWorkForAResourceByActivityTypeMonthly', 1, 'workPerTypeOfActivity.php', 195, 0, 'L', 0, 1, 1, 1, 1, 1, 0, 0, NULL),
(106, 'reportWorkForAResourceByActivityTypeYearly', 1, 'workPerTypeOfActivity.php', 197, 0, 'L', 0, 1, 1, 1, 1, 1, 0, 0, NULL);

INSERT INTO `${prefix}habilitationreport` (idProfile, idReport, allowAccess) VALUES
(1, 105, 1),
(1, 106, 1);

INSERT INTO `${prefix}reportparameter` ( idReport, name, paramType, sortOrder, idle, defaultValue, multiple) VALUES
(105, 'idProject', 'ProjectList', 10, 0, NULL, 0),
(105, 'idResource', 'resourceList', 20, 0, NULL, 0),
(105, 'idActivityType', 'activityTypeList', 30, 0 ,NULL, 0),
(105, 'showDetail', 'boolean', 35, 0, NULL, 0),
(105, 'month', 'month', 40, 0, 'currentMonth', 0),
(106, 'idProject', 'ProjectList', 10, 0, NULL, 0),
(106, 'idResource', 'resourceList', 20, 0, NULL, 0),
(106, 'idActivityType', 'activityTypeList', 30, 0 ,NULL, 0),
(106, 'showDetail', 'boolean', 35, 0, NULL, 0),
(106, 'year', 'year', 20, 0, 'currentYear', 0);

-- IGE #397

ALTER TABLE `${prefix}employmentcontract` ADD `idTeam` INT(12) unsigned DEFAULT NULL;
ALTER TABLE `${prefix}employmentcontract` ADD `idOrganization` INT(12) unsigned DEFAULT NULL;

UPDATE `${prefix}employmentcontract` EC SET idTeam=(SELECT idTeam FROM `${prefix}resource` RES WHERE EC.idEmployee = RES.id);
UPDATE `${prefix}employmentcontract` EC SET idOrganization=(SELECT idOrganization FROM `${prefix}resource` RES WHERE EC.idEmployee = RES.id);

-- Perfs

CREATE INDEX projectSortOrder ON `${prefix}project` (sortOrder);

INSERT INTO `${prefix}parameter` (`parameterCode`, `parameterValue`) VALUES 
('paramAttachmentMaxSizeMail','5242880'),
('paramAttachmentNumMail','M');
