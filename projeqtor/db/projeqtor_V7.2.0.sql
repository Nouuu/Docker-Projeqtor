-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 7.2.0                                       //
-- // Date : 2018-06-18                                     //
-- ///////////////////////////////////////////////////////////

-- ==================================================================
-- Financial evolutions
-- ==================================================================

CREATE TABLE `${prefix}providerorder` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `reference` VARCHAR(100) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `idProviderOrderType` int(12) unsigned DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `creationDate` date DEFAULT NULL,
  `idProvider` int(12) unsigned DEFAULT NULL,
  `externalReference` varchar(100) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL, 
  `additionalInfo` mediumtext DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `idContact` int(12) unsigned DEFAULT NULL,
  `sendDate` datetime DEFAULT NULL,
  `evaluationValue` decimal(7,2) DEFAULT NULL,
  `evaluationRank` int(3) DEFAULT NULL,
  `totalUntaxedAmount` decimal(11,2) UNSIGNED,
  `taxPct` decimal(5,2) DEFAULT NULL,
  `totalTaxAmount` decimal(11,2) UNSIGNED,
  `totalFullAmount` decimal(11,2) UNSIGNED,
  `untaxedAmount` decimal(11,2) UNSIGNED,
  `taxAmount` decimal(11,2) UNSIGNED,
  `fullAmount` decimal(11,2) UNSIGNED,
  `discountAmount` DECIMAL(11,2),
  `discountRate`   DECIMAL(5,2),
  `discountFrom`   varchar(10),
  `deliveryDelay` varchar(100) DEFAULT NULL,
  `deliveryExpectedDate` date DEFAULT NULL,
  `deliveryDoneDate` date DEFAULT NULL,
  `deliveryValidationDate` date DEFAULT NULL,
  `paymentCondition` varchar(100) DEFAULT NULL,
  `comment` mediumtext DEFAULT NULL,
  `handled` int(1) unsigned DEFAULT '0',
  `done` int(1) unsigned DEFAULT '0',
  `idle` int(1) unsigned DEFAULT '0',
  `cancelled` int(1) unsigned DEFAULT '0',
  `handledDate` date DEFAULT NULL,
  `doneDate` date DEFAULT NULL,
  `idleDate` date DEFAULT NULL,
  `idProjectExpense` int(12) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX providerorderProject ON `${prefix}providerorder` (idProject);
CREATE INDEX providerorderUser ON `${prefix}providerorder` (idUser);
CREATE INDEX providerorderResource ON `${prefix}providerorder` (idResource);
CREATE INDEX providerorderStatus ON `${prefix}providerorder` (idStatus);
CREATE INDEX providerorderType ON `${prefix}providerorder` (idProviderOrderType);

CREATE TABLE `${prefix}providerbill` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `reference` VARCHAR(100) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `idProviderBillType` int(12) unsigned DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `creationDate` date DEFAULT NULL,
  `date` date DEFAULT NULL,
  `idProvider` int(12) unsigned DEFAULT NULL,
  `externalReference` varchar(100) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `additionalInfo` mediumtext DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `idContact` int(12) unsigned DEFAULT NULL,
  `sendDate` datetime DEFAULT NULL,
  `evaluationValue` decimal(7,2) DEFAULT NULL,
  `evaluationRank` int(3) DEFAULT NULL,
  `totalUntaxedAmount` decimal(11,2) UNSIGNED,
  `taxPct` decimal(5,2) DEFAULT NULL,
  `totalTaxAmount` decimal(11,2) UNSIGNED,
  `totalFullAmount` decimal(11,2) UNSIGNED,
  `untaxedAmount` decimal(11,2) UNSIGNED,
  `taxAmount` decimal(11,2) UNSIGNED,
  `fullAmount` decimal(11,2) UNSIGNED,
  `discountAmount` DECIMAL(11,2),
  `discountRate`   DECIMAL(5,2),
  `discountFrom`   varchar(10),
  `lastPaymentDate` date DEFAULT NULL,
  `paymentAmount` DECIMAL(11,2),
  `paymentCondition` varchar(100) DEFAULT NULL,
  `paymentDate` date DEFAULT NULL,
  `idPaymentDelay` int(12) unsigned DEFAULT NULL,
  `paymentDueDate` date DEFAULT NULL,
  `paymentsCount` int(3) default 0,
  `paymentDone` int(1) unsigned DEFAULT 0,
  `comment` mediumtext DEFAULT NULL,
  `handled` int(1) unsigned DEFAULT '0',
  `done` int(1) unsigned DEFAULT '0',
  `idle` int(1) unsigned DEFAULT '0',
  `cancelled` int(1) unsigned DEFAULT '0',
  `handledDate` date DEFAULT NULL,
  `doneDate` date DEFAULT NULL,
  `idleDate` date DEFAULT NULL,
  `idProjectExpense` int(12) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX providerbillProject ON `${prefix}providerbill` (idProject);
CREATE INDEX providerbillUser ON `${prefix}providerbill` (idUser);
CREATE INDEX providerbillResource ON `${prefix}providerbill` (idResource);
CREATE INDEX providerbillStatus ON `${prefix}providerbill` (idStatus);
CREATE INDEX providerbillType ON `${prefix}providerbill` (idProviderBillType);

CREATE TABLE `${prefix}providerterm` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `creationDate` date DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `done` int(1) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT NULL,
  `idProviderOrder` int(12) unsigned DEFAULT NULL,
  `idProviderBill` int(12) unsigned DEFAULT NULL,
  `untaxedAmount` decimal(11,2) UNSIGNED,
  `taxPct` decimal(5,2) DEFAULT NULL,
  `taxAmount` decimal(11,2) UNSIGNED,
  `fullAmount` decimal(11,2) UNSIGNED,
  `date` date DEFAULT NULL,
  `idProjectExpense` int(12) UNSIGNED DEFAULT NULL,
  `isBilled` int(1) unsigned DEFAULT NULL,
  `isPaid` int(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX providertermProject ON `${prefix}providerterm` (idProject);
CREATE INDEX providertermOrder ON `${prefix}providerterm` (idProviderOrder);
CREATE INDEX providertermBill ON `${prefix}providerterm` (idProviderBill);

CREATE TABLE `${prefix}providerpayment` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `idProviderBill` int(12) unsigned DEFAULT NULL,
  `idProviderTerm` int(12) unsigned DEFAULT NULL,
  `paymentDate` date,
  `idPaymentMode` int(12) unsigned DEFAULT NULL,
  `idle` int(1) DEFAULT 0,
  `idProviderPaymentType` int(12) unsigned DEFAULT NULL,
  `paymentAmount`  DECIMAL(11,2) UNSIGNED,
  `paymentFeeAmount`  DECIMAL(11,2) UNSIGNED,
  `paymentCreditAmount` DECIMAL(11,2) UNSIGNED,
  `description` mediumtext,
  `idUser` int(12) unsigned DEFAULT NULL,
  `creationDate` date,
  `referenceProviderBill` varchar(100) DEFAULT NULL,
  `idProvider` int(12) unsigned DEFAULT NULL,
  `providerBillAmount` DECIMAL(11,2) UNSIGNED,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX providerpaymentBill ON `${prefix}providerpayment` (idProviderBill);
CREATE INDEX providerpaymentProvider ON `${prefix}providerpayment` (idProvider);

INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(190,'menuProviderOrderType', 79, 'object', 830, 'Project', 0, 'Type '),
(191,'menuProviderOrder', 151, 'object', 206, 'Project', 0, 'Financial '),
(193,'menuProviderBillType', 79, 'object', 831, 'Project', 0, 'Type '),
(194,'menuProviderBill', 151, 'object', 208, 'Project', 0, 'Financial '),
(195,'menuProviderTerm', 151, 'object', 207, 'Project', 0, 'Financial '),
(201,'menuProviderPayment', 151, 'object', 209, 'Project', 0, 'Financial '),
(202,'menuProviderPaymentType',79, 'object',832 , 'Project', 0, 'Type ');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 190, 1),
(1, 191, 1),
(1, 193, 1),
(1, 194, 1),
(1, 195, 1),
(1, 201, 1),
(1, 202, 1);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1,190,8),
(1,191,8),
(1,193,8),
(1,194,8),
(1,195,8),
(1,201,8),
(1,202,8);

ALTER TABLE `${prefix}tender`
CHANGE `initialAmount` `untaxedAmount` DECIMAL(11,2) UNSIGNED,
CHANGE `initialTaxAmount` `taxAmount` DECIMAL(11,2) UNSIGNED,
CHANGE `initialFullAmount` `fullAmount` DECIMAL(11,2) UNSIGNED,
CHANGE `plannedAmount` `totalUntaxedAmount` DECIMAL(11,2) UNSIGNED,
CHANGE `plannedTaxAmount` `totalTaxAmount` DECIMAL(11,2) UNSIGNED,
CHANGE `plannedFullAmount` `totalFullAmount` DECIMAL(11,2) UNSIGNED;
ALTER TABLE `${prefix}tender`
ADD `discountAmount` DECIMAL(11,2),
ADD `discountRate`   DECIMAL(5,2),
ADD `idProjectExpense` int(12) unsigned DEFAULT NULL,
ADD `discountFrom`   varchar(10);

INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `color`, idWorkflow, lockHandled, lockDone, lockIdle, lockCancelled) VALUES
('ProviderOrder', 'Product', 10, 0, NULL, 1, 1, 1, 1, 1),
('ProviderOrder', 'Service', 20, 0, NULL, 1, 1, 1, 1, 1),
('ProviderBill','Partial bill',10,0,NULL, 1, 1, 1, 1, 1),
('ProviderBill','Final bill',20,0,NULL, 1, 1, 1, 1, 1),
('ProviderBill','Complete bill',30,0,NULL, 1, 1, 1, 1, 1),
('ProviderPayment', 'event payment', 10, 0, NULL, 8, 1, 1, 1, 1),
('ProviderPayment', 'partial payment', 20, 0, NULL, 8, 1, 1, 1, 1),
('ProviderPayment', 'final payment', 30, 0, NULL, 8, 1, 1, 1, 1);


INSERT INTO `${prefix}copyable` (`id`,`name`, `idle`, `sortOrder`,`idDefaultCopyable`) VALUES 
(23,'ProviderOrder', '0', '121','24'),
(24,'ProviderBill', '0', '122',26),
(25,'ProviderTerm', '1', '123',26),
(26,'ProviderPayment', '0', '124',NULL);

UPDATE `${prefix}copyable` SET idDefaultCopyable=23 WHERE id=16;

ALTER TABLE `${prefix}billline`
ADD `idBillLine` int(12)  DEFAULT NULL,
ADD `rate` DECIMAL(5,2)  DEFAULT NULL;

ALTER TABLE `${prefix}expense`
ADD `plannedTaxAmount` DECIMAL(11,2) UNSIGNED NULL DEFAULT NULL,
ADD `isCalculated` int(1) unsigned DEFAULT '0',
ADD `realTaxAmount` DECIMAL(11,2) UNSIGNED NULL DEFAULT NULL;

UPDATE `${prefix}expense` SET 
plannedFullAmount=plannedAmount 
WHERE plannedFullAmount is null or plannedFullAmount<plannedAmount and plannedAmount is not null;
UPDATE `${prefix}expense` SET 
realFullAmount=realAmount 
WHERE realFullAmount is null or realFullAmount<realAmount and realAmount is not null;
UPDATE `${prefix}expense` SET 
plannedTaxAmount=plannedFullAmount-plannedAmount 
WHERE plannedFullAmount is not null and plannedAmount is not null;
UPDATE `${prefix}expense` SET 
realTaxAmount=realFullAmount-realAmount
WHERE realFullAmount is not null and realAmount is not null;

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`,`hasCsv`) VALUES 
(86, 'financialExpenseBoard', 7, 'financialExpenseBoard.php', 740, 0),
(87, 'financialExpenseSynthesis', 7, 'financialExpenseSynthesis.php', 750, 0);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`, `multiple`) VALUES
(86,'idProject','projectList',10,0,'currentProject',0),
(86,'idProjectType','projectTypeList',20,0,null,0),
(86,'idOrganization','organizationList',30,0,null,0),
(86,'showExpense','boolean',35,0,1,0),
(86,'showClosedItems','boolean',40,0,null,0),
(87,'idProject','projectList',10,0,'currentProject',0),
(87,'idProjectType','projectTypeList',20,0,null,0),
(87,'idOrganization','organizationList',30,0,null,0);

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES 
(1, 86, 1),
(2, 86, 1),
(3, 86, 1),
(1, 87, 1),
(2, 87, 1),
(3, 87, 1);

INSERT INTO `${prefix}notifiable` (`notifiableItem`,`name`) VALUES
 ('ProviderOrder','ProviderOrder'),
 ('ProviderTerm','ProviderTerm'),
 ('ProviderBill','ProviderBill'),
 ('Tender','Tender');
 
INSERT INTO `${prefix}indicatorable` (`name`,idle) VALUES
 ('ProviderOrder',0),
 ('ProviderTerm',0),
 ('ProviderBill',0);
 
INSERT INTO `${prefix}indicator` (`id`, `code`, `type`, `name`, `sortOrder`, `idle`, `targetDateColumnName`) VALUES
(27, 'DELAY', 'delay', 'deliveryExpectedDate', 410, 0, 'deliveryDoneDate'),
(28, 'DELAY', 'delay', 'date', 420, 0, 'isPaid');

INSERT INTO `${prefix}indicatorableindicator` (`idIndicatorable`, `nameIndicatorable`, `idIndicator`, `idle`) VALUES 
('22', 'ProviderOrder', '27', '0'),
('24', 'ProviderBill', '24', '0'),
('23', 'ProviderTerm', '28', '0');

-- ==================================================================
-- Budget
-- ==================================================================
CREATE TABLE `${prefix}budget` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `idBudgetType` int(12) unsigned DEFAULT NULL,
  `idBudgetOrientation` int(12) unsigned DEFAULT NULL,
  `idBudgetCategory` int(12) unsigned DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `creationDate` datetime DEFAULT NULL,
  `lastUpdateDateTime` datetime DEFAULT NULL,
  `articleNumber` VARCHAR(100) DEFAULT NULL,
  `idOrganization` int(12) unsigned DEFAULT NULL,
  `idClient` int(12) unsigned DEFAULT NULL,
  `clientCode` VARCHAR(100) DEFAULT NULL,
  `idBudget` int(12) unsigned DEFAULT NULL,
  `idSponsor` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `color` VARCHAR(7) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `isUnderConstruction` int(1) unsigned DEFAULT '1',
  `handled` int(1) unsigned DEFAULT '1',
  `handledDate` date DEFAULT NULL,
  `done` int(1) unsigned DEFAULT '0',
  `doneDate` date DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `idleDate` date DEFAULT NULL,
  `cancelled` int(1) unsigned DEFAULT '0',
  `bbs` varchar(1000) DEFAULT NULL,
  `bbsSortable` varchar(4000) DEFAULT NULL,
  `budgetStartDate` date DEFAULT NULL,
  `budgetEndDate` date DEFAULT NULL,
  `plannedAmount` decimal(14,2) UNSIGNED,
  `initialAmount` decimal(14,2) UNSIGNED,
  `update1Amount` decimal(14,2) UNSIGNED,
  `update2Amount` decimal(14,2) UNSIGNED,
  `update3Amount` decimal(14,2),
  `update4Amount` decimal(14,2),
  `actualAmount` decimal(14,2),
  `actualSubAmount` decimal(14,2),
  `usedAmount` decimal(14,2) UNSIGNED,
  `availableAmount` decimal(14,2),
  `billedAmount` decimal(14,2) UNSIGNED,
  `leftAmount` decimal(14,2),
  `plannedFullAmount` decimal(14,2) UNSIGNED,
  `initialFullAmount` decimal(14,2) UNSIGNED,
  `update1FullAmount` decimal(14,2) UNSIGNED,
  `update2FullAmount` decimal(14,2) UNSIGNED,
  `update3FullAmount` decimal(14,2),
  `update4FullAmount` decimal(14,2),
  `actualFullAmount` decimal(14,2),
  `actualSubFullAmount` decimal(14,2),
  `usedFullAmount` decimal(14,2) UNSIGNED,
  `availableFullAmount` decimal(14,2) ,
  `billedFullAmount` decimal(14,2) UNSIGNED,
  `leftFullAmount` decimal(14,2),
  `elementary` int(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX `budgetBudgetType` ON `${prefix}budget` (idBudgetType);
CREATE INDEX `budgetBudget` ON `${prefix}budget` (idBudget);

ALTER TABLE `${prefix}expense`
ADD `idBudgetItem` int(12) unsigned DEFAULT NULL;
CREATE INDEX expenseBudget ON `${prefix}expense` (idBudgetItem);

INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idWorkflow`, `idle`) VALUES 
('Budget', 'Initial',10,1, 0),
('Budget', 'Additional',20,1 ,0);

CREATE TABLE `${prefix}budgetcategory` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `${prefix}budgetcategory` (`name`, `sortOrder`, `idle`) VALUES 
('Information Technology',10,0),
('Human Resources',20,0),
('Financials',30,0),
('Management',40,0);

CREATE TABLE `${prefix}budgetorientation` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `${prefix}budgetorientation` (`name`, `sortOrder`, `idle`) VALUES 
('Operation',10,0),
('Transformation',20,0);


INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(197,'menuBudget', 151, 'object', 203, 'ReadWriteEnvironment', 0, 'Financial'),
(198,'menuBudgetType', 79, 'object', 824, 'ReadWriteType', 0, 'Type'),
(199,'menuBudgetOrientation', 36, 'object', 789, 'ReadWriteList', 0, 'ListOfValues'),
(200,'menuBudgetCategory', 36, 'object', 789, 'ReadWriteList', 0, 'ListOfValues');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 197, 1),
(1, 198, 1),
(1, 199, 1),
(1, 200, 1);

-- ==================================================================
-- Global views
-- ==================================================================

INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(192,'menuGlobalView', 2, 'object', 95, 'Project', 0, 'Work');
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES 
(1,192,1),
(2,192,1),
(3,192,1),
(4,192,1);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES 
(1,192, 8),
(2,192, 2),
(3,192, 7),
(4,192, 7);

-- Table Global View : created only to get correct formatting for fields on list : this table will always be empty
CREATE TABLE `${prefix}globalview` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `objectClass` VARCHAR(100) DEFAULT NULL,
  `objectId` int(12) unsigned,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idType` int(12) unsigned DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `result` mediumtext DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `handled` int(1) unsigned DEFAULT '0',
  `done` int(1) unsigned DEFAULT '0',
  `idle` int(1) unsigned DEFAULT '0',
  `cancelled` int(1) unsigned DEFAULT '0',
  `handledDate` date DEFAULT NULL,
  `doneDate` date DEFAULT NULL,
  `idleDate` date DEFAULT NULL,
  `validatedEndDate` date DEFAULT NULL,
  `plannedEndDate`  date DEFAULT NULL,
  `realEndDate`  date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ==================================================================
-- Global Planning
-- ==================================================================

INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(196,'menuGlobalPlanning', 7, 'item', 125, null, 0, 'Work');
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES 
(1,196,1),
(2,196,1),
(3,196,1),
(4,196,1);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES 
(1,196, 8),
(2,196, 2),
(3,196, 7),
(4,196, 7);

CREATE TABLE `${prefix}planningelementextension` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `refType` varchar(100) DEFAULT NULL,
  `refId` int(12) unsigned DEFAULT NULL,
  `topId` int(12) unsigned DEFAULT NULL,
  `topRefType` varchar(100) DEFAULT NULL,
  `topRefId` int(12) unsigned DEFAULT NULL,
  `wbs` varchar(100) DEFAULT NULL,
  `wbsSortable` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX planningelementextensionReference ON `${prefix}planningelementextension` (refType,refId);
CREATE INDEX planningelementextensionTopReference ON `${prefix}planningelementextension` (topRefType,topRefId);
CREATE INDEX planningelementextensionWbsSortable ON `${prefix}planningelementextension` (wbsSortable(255));

ALTER TABLE `${prefix}project`
ADD `excludeFromGlobalPlanning` int(1) UNSIGNED DEFAULT 0;

ALTER TABLE `${prefix}planningelementbaseline`
ADD `isGlobal` int(1) UNSIGNED DEFAULT 0,
ADD `idType` int(12) unsigned DEFAULT NULL,
ADD `idStatus` int(12) unsigned DEFAULT NULL,
ADD `idResource` int(12) unsigned DEFAULT NULL;

-- ==================================================================
-- Validation of timesheet for team
-- ==================================================================

CREATE TABLE `${prefix}accessscopespecific` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `accessCode` varchar(5) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}accessscopespecific` (`id`, `name`, `accessCode`, `sortOrder`, `idle`) VALUES
(1, 'accessScopeSpecificNo', 'NO', 100, 0),
(2, 'accessScopeSpecificOwn', 'OWN', 200, 0),
(3, 'accessScopeSpecificProject', 'PRO', 300, 0),
(4, 'accessScopeSpecificAll', 'ALL', 400, 0),
(6, 'accessScopeSpecificTeam', 'TEAM', 350, 0);

-- ==================================================================
-- Misc
-- ==================================================================

-- change caption for Meeting menu context
UPDATE `${prefix}menu` set menuClass=replace(menuClass,'Meeting','Review') WHERE menuClass LIKE '%Meeting%';

-- manage milestones on activities
ALTER TABLE `${prefix}activity`
ADD `idMilestone` int(12) UNSIGNED DEFAULT NULL;

-- manage new configuration menu context
UPDATE `${prefix}menu` SET menuClass='Work Configuration EnvironmentalParameter' WHERE id in (86,87,141,142,179);

-- remove dojo editor
UPDATE `${prefix}parameter` set parameterValue='CK' where parameterValue='Dojo';
UPDATE `${prefix}parameter` set parameterValue='CKInline' where parameterValue='DojoInline';

-- Event for any status change 
INSERT INTO `${prefix}event` (`id`, `name`, `idle`, `sortOrder`) VALUES (14,'statusChange',0,100);

--- ==================================================================
--- Imputation Cron
--- ==================================================================

INSERT INTO `${prefix}cronexecution` (`cron`, `fileExecuted`, `idle`, `fonctionName`, `nextTime`) VALUES 
('0 0 1 * *', '../tool/generateImputationAlert.php', '1', 'cronImputationAlertCronResource', NULL),
('0 0 1 * *', '../tool/generateImputationAlert.php', '1', 'cronImputationAlertCronProjectLeader', NULL),
('0 0 1 * *', '../tool/generateImputationAlert.php', '1', 'cronImputationAlertCronTeamManager', NULL),
('0 0 1 * *', '../tool/generateImputationAlert.php', '1', 'cronImputationAlertCronOrganismManager', NULL);

--- ==================================================================
--- Fix
--- ==================================================================

UPDATE `${prefix}planningelement` SET plannedStartDate=(select meetingDate from `${prefix}meeting` as meet where meet.id=refId) where refType = 'Meeting';
UPDATE `${prefix}planningelement` SET plannedEndDate=(select meetingDate from `${prefix}meeting` as meet where meet.id=refId) where refType = 'Meeting';
UPDATE `${prefix}planningelement` SET validatedStartDate=(select meetingDate from `${prefix}meeting` as meet where meet.id=refId) where refType = 'Meeting';
UPDATE `${prefix}planningelement` SET validatedEndDate=(select meetingDate from `${prefix}meeting` as meet where meet.id=refId) where refType = 'Meeting';

UPDATE `${prefix}planningelement` SET realStartDate=(select meetingDate from `${prefix}meeting` as meet where meet.id=refId and meet.handled=1) where refType = 'Meeting';
UPDATE `${prefix}planningelement` SET realEndDate=(select meetingDate from `${prefix}meeting` as meet where meet.id=refId and meet.done=1) where refType = 'Meeting';

UPDATE `${prefix}assignment` SET plannedStartDate=(select meetingDate from `${prefix}meeting` as meet where meet.id=refId) where refType = 'Meeting';
UPDATE `${prefix}assignment` SET plannedEndDate=(select meetingDate from `${prefix}meeting` as meet where meet.id=refId) where refType = 'Meeting';

DELETE FROM `${prefix}notifiable` WHERE notifiableItem='Activity' or notifiableItem='Milestone';