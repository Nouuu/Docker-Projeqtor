
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V1.6.0                                      //
-- // Date : 2010-02-21                                     //
-- ///////////////////////////////////////////////////////////
--
--
ALTER TABLE `${prefix}work` CHANGE `work` `work` DECIMAL(5,2) UNSIGNED;

CREATE TABLE `${prefix}list` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `list` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `sortOrder` int(3) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}list` (`id`, `list`, `name`, `code`, `sortOrder`, `idle`) VALUES
(1, 'yesNo', 'displayYes', 'YES', 20, 0),
(2, 'yesNo', 'displayNo', 'NO', 10, 0);

INSERT INTO `${prefix}habilitationother` (`idProfile`, `scope`, `rightAccess`) VALUES
(1, 'combo', 1),
(2, 'combo', 2),
(3, 'combo', 1),
(4, 'combo', 2),
(6, 'combo', 2),
(7, 'combo', 2),
(5, 'combo', 2);

CREATE TABLE `${prefix}expense` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL, 
  `idResource` int(12) unsigned DEFAULT NULL, 
  `idUser` int(12) unsigned DEFAULT NULL, 
  `idExpenseType` int(12) unsigned DEFAULT NULL,  
  `scope` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL, 
  `description` varchar(4000) DEFAULT NULL,
  `expensePlannedDate` date DEFAULT NULL,
  `expenseRealDate` date DEFAULT NULL,
  `plannedAmount` decimal(11,2) DEFAULT NULL,
  `realAmount` decimal(11,2) DEFAULT NULL,
  `day`  varchar(8),
  `week` varchar(6),
  `month` varchar(6),
  `year` varchar(4),
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE `${prefix}expensedetail` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL, 
  `idExpense` int(12) unsigned DEFAULT NULL, 
  `expenseDate` date DEFAULT NULL, 
  `name` varchar(100) DEFAULT NULL,
  `idExpenseDetailType` int(12) unsigned DEFAULT NULL,
  `value01` decimal(8,2) DEFAULT NULL,
  `value02` decimal(8,2) DEFAULT NULL,
  `value03` decimal(8,2) DEFAULT NULL,
  `unit01` varchar(20) DEFAULT NULL,
  `unit02` varchar(20) DEFAULT NULL,
  `unit03` varchar(20) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `amount` NUMERIC(11,2) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE `${prefix}expensedetailtype` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `sortOrder` int(3) DEFAULT NULL,
  `value01` decimal(8,2) DEFAULT NULL,
  `value02` decimal(8,2) DEFAULT NULL,
  `value03` decimal(8,2) DEFAULT NULL,
  `unit01` varchar(20) DEFAULT NULL,
  `unit02` varchar(20) DEFAULT NULL,
  `unit03` varchar(20) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


UPDATE `${prefix}menu` SET sortOrder=600 where id=13;
UPDATE `${prefix}menu` SET sortOrder=610 where id=14;
UPDATE `${prefix}menu` SET sortOrder=620 where id=15;
UPDATE `${prefix}menu` SET sortOrder=630 where id=72;
UPDATE `${prefix}menu` SET sortOrder=640 where id=16;
UPDATE `${prefix}menu` SET sortOrder=650 where id=17;
UPDATE `${prefix}menu` SET sortOrder=660 where id=57;
UPDATE `${prefix}menu` SET sortOrder=670 where id=44;
UPDATE `${prefix}menu` SET sortOrder=680 where id=50;
UPDATE `${prefix}menu` SET sortOrder=690 where id=36;
UPDATE `${prefix}menu` SET sortOrder=700 where id=73;
UPDATE `${prefix}menu` SET sortOrder=710 where id=34;
UPDATE `${prefix}menu` SET sortOrder=720 where id=39;
UPDATE `${prefix}menu` SET sortOrder=730 where id=40;
UPDATE `${prefix}menu` SET sortOrder=740 where id=38;
UPDATE `${prefix}menu` SET sortOrder=750 where id=42;
UPDATE `${prefix}menu` SET sortOrder=760 where id=41;
UPDATE `${prefix}menu` SET sortOrder=770 where id=59;
UPDATE `${prefix}menu` SET sortOrder=780 where id=68;
UPDATE `${prefix}menu` SET sortOrder=810, idMenu=79 where id=53;
UPDATE `${prefix}menu` SET sortOrder=820, idMenu=79 where id=55;
UPDATE `${prefix}menu` SET sortOrder=830, idMenu=79 where id=56;
UPDATE `${prefix}menu` SET sortOrder=880, idMenu=79 where id=45;
UPDATE `${prefix}menu` SET sortOrder=890, idMenu=79 where id=60;
UPDATE `${prefix}menu` SET sortOrder=900, idMenu=79 where id=46;
UPDATE `${prefix}menu` SET sortOrder=910, idMenu=79 where id=65;
UPDATE `${prefix}menu` SET sortOrder=920, idMenu=79 where id=66;
UPDATE `${prefix}menu` SET sortOrder=930, idMenu=79 where id=67;
UPDATE `${prefix}menu` SET sortOrder=940, idMenu=79 where id=52;

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES
(74, 'menuFinancial', 0, 'menu', 250, Null, 1),
(75, 'menuIndividualExpense', 74, 'object', 255, 'project', 0),
(76, 'menuProjectExpense', 74, 'object', 260, 'project', 0),
(77, 'menuInvoice', 74, 'object', 265, 'project', 1),
(78, 'menuPayment', 74, 'object', 270, 'project', 1),
(79, 'menuType', 13, 'menu', 800, null, 0),
(80, 'menuIndividualExpenseType', 79, 'object', 840, null, 0),
(81, 'menuProjectExpenseType', 79, 'object', 850, null, 0),
(82, 'menuInvoiceType', 79, 'object', 860, null, 1),
(83, 'menuPaymentType', 79, 'object', 870, null, 1);

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES
(84, 'menuExpenseDetailType', 79, 'object', 855, null, 0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 74, 1),
(2, 74, 1),
(3, 74, 1),
(1, 75, 1),
(2, 75, 1),
(3, 75, 1),
(4, 75, 1),
(1, 76, 1),
(2, 76, 1),
(3, 76, 1),
(1, 77, 0),
(2, 77, 0),
(3, 77, 0),
(1, 78, 0),
(2, 78, 0),
(3, 78, 0),
(1, 79, 1),
(2, 79, 1),
(3, 79, 1),
(1, 80, 1),
(2, 80, 1),
(1, 81, 1),
(2, 81, 1),
(1, 82, 0),
(2, 82, 0),
(1, 83, 0),
(2, 83, 0);
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 84, 1),
(2, 84, 1);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1, 75, 8),
(2, 75, 2),
(3, 75, 7),
(4, 75, 5),
(6, 75, 9),
(7, 75, 9),
(5, 75, 9),
(1, 76, 8),
(2, 76, 2),
(3, 76, 7),
(4, 76, 9),
(6, 76, 9),
(7, 76, 9),
(5, 76, 9),
(1, 77, 8),
(2, 77, 2),
(3, 77, 7),
(4, 77, 9),
(6, 77, 9),
(7, 77, 9),
(5, 77, 9),
(1, 78, 8),
(2, 78, 2),
(3, 78, 7),
(4, 78, 9),
(6, 78, 9),
(7, 78, 9),
(5, 78, 9);

DELETE FROM `${prefix}type` WHERE scope in ('IndividualExpense', 'ProjectExpense', 'Invoice', 'Payment');

INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `color`, idWorkflow) VALUES
('IndividualExpense', 'Expense report', 10, 0, NULL, 8),
('ProjectExpense', 'Machine expense', 10, 0, NULL, 8),
('ProjectExpense', 'Office expense', 20, 0, NULL, 8),
('Invoice', 'event invoice', 10, 0, NULL, 8),
('Invoice', 'partial invoice', 20, 0, NULL, 8),
('Invoice', 'final invoice', 30, 0, NULL, 8),
('Payment', 'event payment', 10, 0, NULL, 8),
('Payment', 'partial payment', 20, 0, NULL, 8),
('Payment', 'final payment', 30, 0, NULL, 8);

INSERT INTO `${prefix}workflow` (id,name, description, idle, workflowUpdate) VALUES 
(8,'Simple with Project Leader validation','Simple workflow with limited status, including Project Leader validation.
Anyone can change status, except validation : only Project Leader.',0,'[     ]');

INSERT INTO `${prefix}workflowstatus` (idWorkflow,idStatusFrom,idStatusTo,idProfile,allowed) VALUES 
(8,1,3,1,1),
(8,1,3,2,1),
(8,1,3,3,1),
(8,1,3,4,1),
(8,1,3,6,1),
(8,1,3,7,1),
(8,1,3,5,1),
(8,3,4,1,1),
(8,3,4,2,1),
(8,3,4,3,1),
(8,3,4,4,1),
(8,3,4,6,1),
(8,3,4,7,1),
(8,3,4,5,1),
(8,4,3,3,1),
(8,4,12,3,1),
(8,12,7,3,1);

INSERT INTO `${prefix}expensedetailtype` (id, name, sortOrder, value01, unit01, value02, unit02, value03, unit03, idle) VALUES
(1,'travel by car', 10, null, 'km', 0.544, '€/km', null, null, 0),
(2,'regular mission car travel', 20, null, 'days', null, 'km/day', 0.544, '€/km', 0),
(3,'lunch for guests', 30, null, 'guests', null, '€/guest', null, null, 0),
(4, 'justified expense', 40, null, null, null, null, null, null, 0);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`) VALUES
(31, 'reportPlanDetail', 2, 'detailPlan.php', 455),
(32, 'reportAvailabilityPlan', 2, 'availabilityPlan.php', 480),
(33, 'reportExpenseProject', 6, 'expensePlan.php?scale=month&scope=Project', 660),
(34, 'reportExpenseResource', 6, 'expensePlan.php?scale=month&scope=Individual', 670),
(35, 'reportExpenseTotal', 6, 'expensePlan.php?scale=month', 680),
(36, 'reportExpenseCostTotal', 6, 'expenseCostTotalPlan.php?scale=month', 690);

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES 
(68, 31, 'idProject', 'projectList', 10, 'currentProject'),
(69, 31, 'month', 'month', 20, 'currentMonth'),
(70, 32, 'month', 'month', 10, 'currentMonth'),
(71, 33, 'idProject', 'projectList', 10, 'currentProject'),
(72, 34, 'idProject', 'projectList', 10, 'currentProject'),
(73, 35, 'idProject', 'projectList', 10, 'currentProject'),
(74, 36, 'idProject', 'projectList', 10, 'currentProject'),
(75, 34, 'idResource', 'resourceList', 20, null);

INSERT INTO `${prefix}habilitationreport` (`idReport`, `idProfile`,  `allowAccess`) VALUES
(31, 1, 1),
(31, 2, 1),
(31, 3, 1),
(32, 1, 1),
(32, 2, 1),
(32, 3, 1),
(33, 1, 1),
(33, 2, 1),
(33, 3, 1),
(34, 1, 1),
(34, 2, 1),
(34, 3, 1),
(35, 1, 1),
(35, 2, 1),
(35, 3, 1),
(36, 1, 1),
(36, 2, 1),
(36, 3, 1); 

ALTER TABLE `${prefix}meeting` ADD description VARCHAR(4000);

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES 
(76, 9, 'requestor', 'requestorList', 35, null),
(77, 10, 'requestor', 'requestorList', 35, null),
(78, 11, 'requestor', 'requestorList', 25, null),
(79, 12, 'requestor', 'requestorList', 25, null),
(80, 13, 'requestor', 'requestorList', 25, null),
(81, 14, 'requestor', 'requestorList', 25, null),
(82, 15, 'requestor', 'requestorList', 25, null),
(83, 16, 'requestor', 'requestorList', 25, null),
(84, 17, 'requestor', 'requestorList', 15, null),
(85, 18, 'requestor', 'requestorList', 15, null);
