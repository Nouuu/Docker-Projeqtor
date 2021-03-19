-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.5.0                                       //
-- // Date : 2016-07-28                                     //
-- ///////////////////////////////////////////////////////////

-- INSERT INTO `${prefix}accessscope` (`id`,`name`,`accessCode`,`sortOrder`,`idle`) VALUES (6,'accessScopeClient','CLI',375,0);
-- INSERT INTO `${prefix}accessprofile` (`id`,`name`,`description`,`idAccessScopeRead`,`idAccessScopeCreate`,`idAccessScopeUpdate`,`idAccessScopeDelete`,`sortOrder`,`idle`) VALUES (11,'accessProfileClientCreator','Read his client''s projects Can create his own project',6,3,6,2,325,0);

CREATE TABLE `${prefix}productproject` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idProduct` int(12) unsigned DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE INDEX productprojectProject ON `${prefix}productproject` (idProject);
CREATE INDEX productprojectProduct ON `${prefix}productproject` (idProduct);

INSERT INTO `${prefix}textable` (`id`,`name`,`idle`) VALUES 
(20,'Quotation',0);

ALTER TABLE `${prefix}project` ADD `lastUpdateDateTime` datetime DEFAULT NULL;
ALTER TABLE `${prefix}activity` ADD `lastUpdateDateTime` datetime DEFAULT NULL;

INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(151,'menuExpenses', 74, 'menu', 202, NULL, 0, ''),
(152,'menuIncomings', 74, 'menu', 225, NULL, 0, ''),
(153,'menuCallForTender', 151, 'object', 204, 'Project', 0, 'Financial '),
(154,'menuTender', 151, 'object', 206, 'Project', 0, 'Financial '),
(155,'menuCallForTenderType', 79, 'object', 825, 'Project', 0, 'Type '),
(156,'menuTenderType', 79, 'object', 829, 'Project', 0, 'Type '),
(157,'menuTenderStatus', 36, 'object', 790, null, 0, 'ListOfValues ');

UPDATE `${prefix}menu` SET `idMenu`=151 WHERE `id` IN (75,76);
UPDATE `${prefix}menu` SET `idMenu`=152 WHERE `id` IN (131,125,96,97,78,94,146);
UPDATE `${prefix}menu` SET `sortOrder`=833 WHERE `id`=80;
UPDATE `${prefix}menu` SET `sortOrder`=837 WHERE `id`=81;
UPDATE `${prefix}menu` SET `sortOrder`=841 WHERE `id`=84;
UPDATE `${prefix}menu` SET `sortOrder`=845 WHERE `id`=132;
UPDATE `${prefix}menu` SET `sortOrder`=849 WHERE `id`=126;
UPDATE `${prefix}menu` SET `sortOrder`=853 WHERE `id`=100;
UPDATE `${prefix}menu` SET `sortOrder`=857 WHERE `id`=83;

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`)
SELECT `idProfile`, 153, `allowAccess` FROM `${prefix}habilitation` WHERE `idMenu`=131;
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`)
SELECT `idProfile`, 154, `allowAccess` FROM `${prefix}habilitation` WHERE `idMenu`=131;
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`)
SELECT `idProfile`, 155, `allowAccess` FROM `${prefix}habilitation` WHERE `idMenu`=132;
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`)
SELECT `idProfile`, 156, `allowAccess` FROM `${prefix}habilitation` WHERE `idMenu`=132;
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`)
SELECT `idProfile`, 157, `allowAccess` FROM `${prefix}habilitation` WHERE `idMenu`=34;

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) 
SELECT `idProfile`, 153, `idAccessProfile` FROM `${prefix}accessright` WHERE `idMenu`=131;  
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) 
SELECT `idProfile`, 154, `idAccessProfile` FROM `${prefix}accessright` WHERE `idMenu`=131;

CREATE TABLE `${prefix}tender` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(100) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `idTenderType` int(12) unsigned DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idCallForTender` int(12) unsigned DEFAULT NULL,
  `idTenderStatus` int(12) unsigned DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `creationDate` datetime DEFAULT NULL,
  `idProvider` int(12) unsigned DEFAULT NULL,
  `externalReference` varchar(100) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `idContact` int(12) unsigned DEFAULT NULL,
  `requestDateTime` datetime DEFAULT NULL,
  `expectedTenderDateTime` datetime DEFAULT NULL,
  `receptionDateTime` datetime DEFAULT NULL,
  `evaluationValue` decimal(7,2) DEFAULT NULL,
  `evaluationRank` int(3) DEFAULT NULL,
  `offerValidityEndDate` date DEFAULT NULL,
  `plannedAmount` decimal(11,2) UNSIGNED,
  `taxPct` decimal(5,2) DEFAULT NULL,
  `plannedTaxAmount` decimal(11,2) UNSIGNED,
  `plannedFullAmount` decimal(11,2) UNSIGNED,
  `initialAmount` decimal(11,2) UNSIGNED,
  `initialTaxAmount` decimal(11,2) UNSIGNED,
  `initialFullAmount` decimal(11,2) UNSIGNED,
  `deliveryDelay` varchar(100) DEFAULT NULL,
  `deliveryDate` date DEFAULT NULL,
  `paymentCondition` varchar(100) DEFAULT NULL,
  `evaluation` decimal(7,2) DEFAULT NULL,
  `result` mediumtext DEFAULT NULL,
  `handled` int(1) unsigned DEFAULT '0',
  `done` int(1) unsigned DEFAULT '0',
  `idle` int(1) unsigned DEFAULT '0',
  `cancelled` int(1) unsigned DEFAULT '0',
  `handledDate` date DEFAULT NULL,
  `doneDate` date DEFAULT NULL,
  `idleDate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX tenderProject ON `${prefix}tender` (idProject);
CREATE INDEX tenderType ON `${prefix}tender` (idTenderType);
CREATE INDEX tenderProvider ON `${prefix}tender` (idProvider);
CREATE INDEX tenderStatusIndex ON `${prefix}tender` (idStatus);
CREATE INDEX tenderTenderStatus ON `${prefix}tender` (idTenderStatus);
CREATE INDEX tenderCallForTender ON `${prefix}tender` (idCallForTender);

CREATE TABLE `${prefix}callfortender` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `idCallForTenderType` int(12) unsigned DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `technicalRequirements` mediumtext DEFAULT NULL,
  `businessRequirements` mediumtext DEFAULT NULL,
  `otherRequirements` mediumtext DEFAULT NULL,
  `creationDate` datetime DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `sendDateTime` datetime DEFAULT NULL,
  `expectedTenderDateTime` datetime DEFAULT NULL,
  `maxAmount` decimal(11,2) UNSIGNED,
  `deliveryDate` date DEFAULT NULL,
  `evaluationMaxValue` decimal(7,2) DEFAULT NULL,
  `fixValue` int(1) unsigned DEFAULT '0',
  `idProduct` int(12) unsigned DEFAULT NULL,
  `idProductVersion` int(12) unsigned DEFAULT NULL,
  `idComponent` int(12) unsigned DEFAULT NULL,
  `idComponentVersion` int(12) unsigned DEFAULT NULL,
  `result` mediumtext DEFAULT NULL,
  `handled` int(1) unsigned DEFAULT '0',
  `done` int(1) unsigned DEFAULT '0',
  `idle` int(1) unsigned DEFAULT '0',
  `cancelled` int(1) unsigned DEFAULT '0',
  `handledDate` date DEFAULT NULL,
  `doneDate` date DEFAULT NULL,
  `idleDate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX callfortenderProject ON `${prefix}callfortender` (idProject);
CREATE INDEX callfortenderType ON `${prefix}callfortender` (idCallForTenderType);
CREATE INDEX callfortenderStatus ON `${prefix}callfortender` (idStatus);
CREATE INDEX callfortenderResource ON `${prefix}callfortender` (idResource);

CREATE TABLE `${prefix}tenderevaluationcriteria` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idCallForTender` int(12) unsigned DEFAULT NULL,
  `criteriaName` varchar(100) DEFAULT NULL,
  `criteriaMaxValue` int(3) unsigned DEFAULT 10,
  `criteriaCoef` int(3) unsigned DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX tenderevaluationcriteriaCallForTender ON `${prefix}tenderevaluationcriteria` (idCallForTender);

CREATE TABLE `${prefix}tenderevaluation` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idTenderEvaluationCriteria` int(12) unsigned DEFAULT NULL,
  `idTender` int(12) unsigned DEFAULT NULL,
  `evaluationValue` decimal(5,2) DEFAULT NULL,
  `comment` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX tenderevaluationTenderEvaluationCriteria ON `${prefix}tenderevaluation` (idTenderEvaluationCriteria);
CREATE INDEX tenderevaluationTender ON `${prefix}tenderevaluation` (idTender);

INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `idWorkflow`, `mandatoryDescription`, `mandatoryResultOnDone`, `mandatoryResourceOnHandled`, `lockHandled`, `lockDone`, `lockIdle`, `code`) VALUES 
('CallForTender', 'by mutual agreement', '10', '0', '1', '0', '0', '0', '0', '0', '0', ''),
('CallForTender', 'adapted procedure', '20', '0', '1', '0', '0', '0', '0', '0', '0', ''),
('CallForTender', 'open call for tender', '30', '0', '1', '0', '0', '0', '0', '0', '0', ''),
('CallForTender', 'restricted call for tender', '40', '0', '1', '0', '0', '0', '0', '0', '0', '');
INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `idWorkflow`, `mandatoryDescription`, `mandatoryResultOnDone`, `mandatoryResourceOnHandled`, `lockHandled`, `lockDone`, `lockIdle`, `code`) VALUES 
('Tender', 'by mutual agreement', '10', '0', '1', '0', '0', '0', '0', '0', '0', ''),
('Tender', 'adapted procedure', '20', '0', '1', '0', '0', '0', '0', '0', '0', ''),
('Tender', 'open call for tender', '30', '0', '1', '0', '0', '0', '0', '0', '0', ''),
('Tender', 'restricted call for tender', '40', '0', '1', '0', '0', '0', '0', '0', '0', '');

CREATE TABLE `${prefix}tenderstatus` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `isWaiting` int(1) unsigned DEFAULT '0',
  `isReceived` int(1) unsigned DEFAULT '0',
  `isNotSelect` int(1) unsigned DEFAULT '0',
  `isSelected` int(1) unsigned DEFAULT '0',
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}tenderstatus` (`name`, `color`, `sortOrder`, `idle`, `isWaiting`, `isReceived`, `isNotSelect`, `isSelected`) VALUES 
('request to send',       '#ffa500', '10', '0', '0', '0', '0', '0'),
('waiting for reply',     '#f08080', '20', '0', '1', '0', '0', '0'),
('out of date answer',    '#c0c0c0', '30', '0', '0', '1', '1', '0'),
('incomplete file',       '#c0c0c0', '40', '0', '0', '1', '1', '0'),
('admissible',            '#87ceeb', '50', '0', '0', '1', '0', '0'),
('short list',            '#4169e1', '60', '0', '0', '1', '0', '0'),
('not selected',          '#c0c0c0', '70', '0', '0', '1', '1', '0'),
('selected',              '#98fb98', '80', '0', '0', '1', '0', '1');

ALTER TABLE `${prefix}resource` ADD `idProvider` int(12) unsigned DEFAULT NULL;

INSERT INTO `${prefix}copyable` (`id`,`name`, `idle`, `sortOrder`) VALUES (16,'Tender', '0', '120');

ALTER TABLE `${prefix}expense` ADD `idContact` int(12) unsigned DEFAULT NULL;

ALTER TABLE `${prefix}indicator` ADD `targetDateColumnName` varchar(100) DEFAULT NULL;
ALTER TABLE `${prefix}indicatorvalue` ADD `targetDateColumnName` varchar(100) DEFAULT NULL;

INSERT INTO `${prefix}indicatorable` (`id`,`name`, `idle`) VALUES (14,'Tender', '0');
INSERT INTO `${prefix}indicatorable` (`id`,`name`, `idle`) VALUES (15,'IndividualExpense', '0');
INSERT INTO `${prefix}indicatorable` (`id`,`name`, `idle`) VALUES (16,'ProjectExpense', '0');
INSERT INTO `${prefix}indicatorable` (`id`,`name`, `idle`) VALUES (17,'Quotation', '0');
INSERT INTO `${prefix}indicatorable` (`id`,`name`, `idle`) VALUES (18,'Command', '0');
INSERT INTO `${prefix}indicatorable` (`id`,`name`, `idle`) VALUES (19,'Term', '0');
INSERT INTO `${prefix}indicatorable` (`id`,`name`, `idle`) VALUES (20,'Bill', '0');
INSERT INTO `${prefix}indicatorable` (`id`,`name`, `idle`) VALUES (21,'Meeting', '0');

INSERT INTO `${prefix}indicator` (`id`, `code`, `type`, `name`, `sortOrder`, `idle`, `targetDateColumnName`) VALUES
(17, 'DELAY', 'delay', 'expectedTenderDateTime', 310, 0, 'receptionDateTime'),
(18, 'DELAY', 'delay', 'expensePlannedDate', 320, 0, 'expenseRealDate'),
(19, 'DELAY', 'delay', 'deliveryDate', 330, 0, 'receptionDate');
INSERT INTO `${prefix}indicator` (`id`, `code`, `type`, `name`, `sortOrder`, `idle`, `targetDateColumnName`) VALUES
(20, 'DELAY', 'delay', 'validityEndDate', 340, 0, 'idleDate'),
(21, 'DELAY', 'delay', 'validatedDate', 350, 0, 'idBill'),
(22, 'DELAY', 'delay', 'plannedDate', 360, 0, 'idBill'),
(23, 'DELAY', 'delay', 'date', 370, 0, 'idBill'),
(24, 'DELAY', 'delay', 'paymentDueDate', 380, 0, 'paymentDate'),
(25, 'DELAY', 'delay', 'meetingDate', 390, 0, 'done'),
(26, 'DELAY', 'delay', 'date', 400, 0, 'sendDate');

INSERT INTO `${prefix}indicatorableindicator` (`idIndicatorable`, `nameIndicatorable`, `idIndicator`, `idle`) VALUES 
('14', 'Tender', '17', '0'),
('15', 'IndividualExpense', '18', '0'),
('16', 'ProjectExpense', '18', '0'),
('16', 'ProjectExpense', '19', '0');
INSERT INTO `${prefix}indicatorableindicator` (`idIndicatorable`, `nameIndicatorable`, `idIndicator`, `idle`) VALUES 
('17', 'Quotation', '20', '0'),
('18', 'Command', '5', '0'),
('18', 'Command', '6', '0'),
('19', 'Term', '21', '0'),
('19', 'Term', '22', '0'),
('19', 'Term', '23', '0'),
('20', 'Bill', '24', '0'),
('21', 'Meeting', '25', '0'),
('20', 'Bill', '26', '0');

ALTER TABLE `${prefix}activity` ADD `idComponentVersion` int(12) unsigned DEFAULT NULL;

ALTER TABLE `${prefix}risk` ADD `mitigationPlan` mediumtext DEFAULT NULL;

ALTER TABLE `${prefix}team` ADD `idResource` int(12) unsigned DEFAULT NULL;

INSERT INTO `${prefix}parameter` (idUser,idProject, parameterCode, parameterValue) VALUES 
(null, null, 'messageAlertImputationResource', 'Your real work allocation is not complete up to ${DAY}'),
(null, null, 'messageAlertImputationProjectLeader', 'Some of your resources did not enter real work up to ${DAY}'),
(null, null, 'imputationAlertGenerationDay', '5'),
(null, null, 'imputationAlertGenerationHour', '17:00'),
(null, null, 'imputationAlertControlDay', 'current'),
(null, null, 'imputationAlertControlNumberOfDays', '7'),
(null, null, 'imputationAlertSendToResource', 'MAIL'),
(null, null, 'imputationAlertSendToProjectLeader', 'MAIL'),
(null, null, 'imputationAlertSendToTeamManager', 'MAIL');

ALTER TABLE `${prefix}billline` ADD `billingType` varchar(10) default null;

CREATE INDEX workReference ON `${prefix}work` (idAssignment, workDate, idWorkElement);


