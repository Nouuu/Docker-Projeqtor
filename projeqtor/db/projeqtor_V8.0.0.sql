-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 7.5.0                                       //
-- // Date : 2019-01-25                                     //
-- ///////////////////////////////////////////////////////////

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(204, 'menuImputationValidation', 7, 'item', 118, Null, 0, 'Work'),
(205, 'menuAutoSendReport', 11, 'item', 405, Null, 0, 'Work');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 204, 1),
(2, 204, 1),
(3, 204, 1),
(1, 205, 1),
(2, 205, 1),
(3, 205, 1),
(4, 205, 1),
(5, 205, 1),
(6, 205, 1),
(7, 205, 1);

INSERT INTO `${prefix}habilitationother` (idProfile, rightAccess, scope) VALUES
(1,4,'scheduledReport'),
(2,2,'scheduledReport'),
(3,2,'scheduledReport'),
(4,2,'scheduledReport'),
(5,2,'scheduledReport'),
(6,2,'scheduledReport'),
(7,2,'scheduledReport');

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1,204,8),
(2,204,2);

INSERT INTO `${prefix}parameter` (`parameterCode`, `parameterValue`) VALUES 
('paramMailBodyReport' ,'[${dbName}] Report ${report} - ${date}'),
('paramMailTitleReport' ,'[${dbName}] Report ${report} - ${date}');

-- ============================================================
-- LEAVE SYSTEM
-- ============================================================

-- to create the table leavessystemhabilitation
CREATE TABLE `${prefix}leavessystemhabilitation` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `menuName` VARCHAR(100) NOT NULL,
  `viewAccess` VARCHAR(10) DEFAULT NULL,
  `readAccess` VARCHAR(10) DEFAULT NULL,
  `createAccess` VARCHAR(10) DEFAULT NULL,
  `updateAccess` VARCHAR(10) DEFAULT NULL,
  `deleteAccess` VARCHAR(10) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE INDEX leavessystemhabilitationMenu ON `${prefix}leavessystemhabilitation` (menuName);

-- to create the table leavetype
CREATE TABLE `${prefix}leavetype` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `idActivity` INT(12) UNSIGNED DEFAULT NULL,
  `idWorkflow` INT(12) UNSIGNED DEFAULT NULL,
  `color` VARCHAR(7) DEFAULT NULL,
  `notificationOnCreate` VARCHAR(255) DEFAULT NULL,
  `notificationOnUpdate` VARCHAR(255) DEFAULT NULL,
  `notificationOnDelete` VARCHAR(255) DEFAULT NULL,
  `notificationOnTreatment` VARCHAR(255) DEFAULT NULL,
  `alertOnCreate` VARCHAR(255) DEFAULT NULL,
  `alertOnUpdate` VARCHAR(255) DEFAULT NULL,
  `alertOnDelete` VARCHAR(255) DEFAULT NULL,
  `alertOnTreatment` VARCHAR(255) DEFAULT NULL,
  `emailOnCreate` VARCHAR(255) DEFAULT NULL,
  `emailOnUpdate` VARCHAR(255) DEFAULT NULL,
  `emailOnDelete` VARCHAR(255) DEFAULT NULL,
  `emailOnTreatment` VARCHAR(255) DEFAULT NULL,
  `idle` INT(1) UNSIGNED DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- to create the table employeeleaveearned
CREATE TABLE `${prefix}employeeleaveearned` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idUser` INT(12) UNSIGNED DEFAULT NULL,
  `idEmployee` INT(12) UNSIGNED DEFAULT NULL,
  `idLeaveType` INT(12) UNSIGNED DEFAULT NULL,
  `startDate` DATE DEFAULT NULL,
  `endDate` DATE DEFAULT NULL,
  `lastUpdateDate` DATE DEFAULT NULL,
  `quantity` DECIMAL(4, 1) UNSIGNED DEFAULT NULL,
  `leftQuantity` DECIMAL(4, 1) DEFAULT NULL,
  `leftQuantityBeforeClose` DECIMAL(4, 1) DEFAULT NULL,
  `idle` INT(1) UNSIGNED DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
CREATE INDEX employeeleaveearnedEmployee ON `${prefix}employeeleaveearned` (idEmployee);

-- to create the table employeeleaveperiod
CREATE TABLE `${prefix}employeeleaveperiod` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idle` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  `comment` VARCHAR(255) DEFAULT NULL,
  `startDate` DATE DEFAULT NULL,
  `startAMPM` VARCHAR(2) DEFAULT 'AM',
  `endDate` DATE DEFAULT NULL,
  `endAMPM` VARCHAR(2) DEFAULT 'PM',
  `idLeaveType` INT(12) UNSIGNED DEFAULT NULL,
  `idStatus` INT(12) UNSIGNED DEFAULT NULL,
  `idUser` INT(12) UNSIGNED DEFAULT NULL,
  `idEmployee` INT(12) UNSIGNED DEFAULT NULL,
  `requestDateTime` DATETIME DEFAULT NULL,
  `idResource` INT(12) UNSIGNED DEFAULT NULL,
  `processingDateTime` DATETIME DEFAULT NULL,
  `nbDays` DECIMAL(4, 1) UNSIGNED DEFAULT NULL,
  `submitted` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  `rejected` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  `accepted` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  `statusOutOfWorkflow` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  `statusSetLeaveChange` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
CREATE INDEX employeeleaveperiodEmployee ON `${prefix}employeeleaveperiod` (idEmployee);

-- to create the table employmentcontracttype
CREATE TABLE `${prefix}employmentcontracttype` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idle` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  `name` VARCHAR(100) NOT NULL,
  `idRecipient` INT(12) UNSIGNED DEFAULT NULL,
  `idWorkflow` INT(12) UNSIGNED DEFAULT NULL,
  `idManagementType` INT(12) UNSIGNED DEFAULT NULL,
  `isDefault` INT(1) UNSIGNED NOT NULL DEFAULT 0,  
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- to create the table leavetypeofemploymentcontracttype
CREATE TABLE `${prefix}leavetypeofemploymentcontracttype` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idle` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  `idEmploymentContractType` INT(12) UNSIGNED DEFAULT NULL,
  `idLeaveType` INT(12) UNSIGNED DEFAULT NULL,
  `startMonthPeriod` VARCHAR(2) DEFAULT NULL,
  `startDayPeriod` VARCHAR(2) DEFAULT NULL,
  `periodDuration` INT(5) UNSIGNED DEFAULT NULL,
  `quantity` DECIMAL(4, 1) UNSIGNED DEFAULT NULL,
  `isIntegerQuotity` INT(1) UNSIGNED DEFAULT 0,
  `earnedPeriod` INT(5) UNSIGNED DEFAULT NULL,
  `isUnpayedAllowed` INT(1) UNSIGNED DEFAULT 0,
  `isJustifiable` INT(1) UNSIGNED DEFAULT 0,
  `isAnticipated` INT(1) UNSIGNED DEFAULT 0,
  `validityDuration` INT(5) UNSIGNED DEFAULT 12,
  `nbDaysAfterNowLeaveDemandIsAllowed` INT(5) UNSIGNED DEFAULT NULL,
  `nbDaysBeforeNowLeaveDemandIsAllowed` INT(5) UNSIGNED DEFAULT NULL,  
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- to create the table customEarnedRulesOfEmploymentContractType
CREATE TABLE `${prefix}customearnedrulesofemploymentcontracttype` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idle` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  `name` VARCHAR(100) DEFAULT NULL,
  `rule` VARCHAR(4000) DEFAULT NULL,
  `whereClause` VARCHAR(4000) DEFAULT NULL,
  `idEmploymentContractType` INT(12) UNSIGNED DEFAULT NULL,
  `idLeaveType` INT(12) UNSIGNED DEFAULT NULL,
  `quantity` DECIMAL(4,1) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--to create the table employmentContractEndReason
CREATE TABLE `${prefix}employmentcontractendreason` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `final` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  `idle` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--to create the table employmentContract
CREATE TABLE `${prefix}employmentcontract` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idUser` INT(12) UNSIGNED DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `startDate` DATE DEFAULT NULL,
  `endDate` DATE DEFAULT NULL,
  `mission` LONGTEXT DEFAULT NULL,
  `idEmployee` INT(12) UNSIGNED DEFAULT NULL,
  `idEmploymentContractType` INT(12) UNSIGNED DEFAULT NULL,
  `idStatus` INT(12) UNSIGNED DEFAULT NULL,
  `idEmploymentContractEndReason` INT(12) UNSIGNED DEFAULT NULL,
  `idle` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
CREATE INDEX employmentcontractEmployee ON `${prefix}employmentcontract` (idEmployee);

--to create the table employeesmanaged
CREATE TABLE `${prefix}employeesmanaged` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idEmployeeManager` INT(12) UNSIGNED DEFAULT NULL,
  `idEmployee` INT(12) UNSIGNED DEFAULT NULL,
  `startDate` DATE DEFAULT NULL,
  `endDate` DATE DEFAULT NULL,
  `idle` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
CREATE INDEX employeesManagedEmployee ON `${prefix}employeesmanaged` (idEmployee);
CREATE INDEX employeesManagedEmployeeManager ON `${prefix}employeesmanaged` (idEmployeeManager);

--to create the table rulableforempcontracttype
CREATE TABLE `${prefix}rulableforempcontracttype` (
    `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
    `rulableItem` VARCHAR(100) DEFAULT NULL,
    `name` VARCHAR(100) DEFAULT NULL,
    `idle` INT(1) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- to create the table
CREATE TABLE `${prefix}calendarbankoffdays` (
    `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idCalendarDefinition` INT(12) UNSIGNED DEFAULT NULL,
    `name` VARCHAR(100) DEFAULT NULL,
    `month` INT(2) DEFAULT NULL,
    `day` INT(2) DEFAULT NULL,
    `easterDay` INT(2) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX calendarbankoffdaysCalendar ON `${prefix}calendarbankoffdays` (idCalendarDefinition);

-- to add the dayOfWeek columns to the table calendardefinition
ALTER TABLE `${prefix}calendardefinition` ADD `dayOfWeek0` int(1) UNSIGNED DEFAULT 0;
ALTER TABLE `${prefix}calendardefinition` ADD `dayOfWeek1` int(1) UNSIGNED DEFAULT 0;
ALTER TABLE `${prefix}calendardefinition` ADD `dayOfWeek2` int(1) UNSIGNED DEFAULT 0;
ALTER TABLE `${prefix}calendardefinition` ADD `dayOfWeek3` int(1) UNSIGNED DEFAULT 0;
ALTER TABLE `${prefix}calendardefinition` ADD `dayOfWeek4` int(1) UNSIGNED DEFAULT 0;
ALTER TABLE `${prefix}calendardefinition` ADD `dayOfWeek5` int(1) UNSIGNED DEFAULT 0;
ALTER TABLE `${prefix}calendardefinition` ADD `dayOfWeek6` int(1) UNSIGNED DEFAULT 0;

-- to add the column isEmployee to the table resource
ALTER TABLE `${prefix}resource` ADD `isEmployee` int(1) UNSIGNED DEFAULT 0;
-- to add the column isEmployee to the table resource
ALTER TABLE `${prefix}resource` ADD `isLeaveManager` int(1) UNSIGNED DEFAULT 0;

-- to add the column isLeaveMngProject dans la table project
ALTER TABLE `${prefix}project` ADD `isLeaveMngProject` int(1) UNSIGNED DEFAULT 0;

--add a column idLeave in the table work
ALTER TABLE `${prefix}work` ADD `idLeave` int(12) UNSIGNED DEFAULT NULL;

--add a column idLeave in the table plannedwork
ALTER TABLE `${prefix}plannedwork` ADD `idLeave` int(12) UNSIGNED DEFAULT NULL;

--add a column isLeavesSystemMenu in the table menu
ALTER TABLE `${prefix}menu` ADD `isLeavesSystemMenu` INT(1) DEFAULT 0;

--add columns setSubmittedLeave, setValidatedLeave, setRejectedLeave in the table status
ALTER TABLE `${prefix}status` ADD `setSubmittedLeave` INT(1) DEFAULT 0;
ALTER TABLE `${prefix}status` ADD `setAcceptedLeave` INT(1) DEFAULT 0;
ALTER TABLE `${prefix}status` ADD `setRejectedLeave` INT(1) DEFAULT 0;

-- to update sortOrder of existing menus that are'nt Leave System menu
UPDATE `${prefix}menu` set `sortOrder` = (`sortOrder`+100) where `sortOrder`>=395;

--to insert HumanResource in menu
INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`, `isLeavesSystemMenu`) VALUES
(208,'menuHumanResource', 0, 'menu', 400, null, 1, 'HumanResource', 1);

--to insert LeaveCalendar in menu
INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`, `isLeavesSystemMenu`) VALUES
(209,'menuLeaveCalendar', 208, 'item', 405, null, 0, 'HumanResource', 1);

-- to insert the Leave in Menu
INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`, `isLeavesSystemMenu`) VALUES
(210, 'menuLeave', 208, 'object', 410, null, 0, 'HumanResource', 1);

--to insert employeeLeaveEarned in menu
INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`, `isLeavesSystemMenu`) VALUES
(211, 'menuEmployeeLeaveEarned', 208, 'object', 420, null, 0, 'HumanResource', 1);

-- to insert the Employee in Menu
INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`, `isLeavesSystemMenu`) VALUES
(212, 'menuEmployee', 208, 'object', 425, null, 0, 'HumanResource', 1);

--to insert employmentContract in menu
INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`, `isLeavesSystemMenu`) VALUES
(213, 'menuEmploymentContract', 208, 'object', 430, null, 0, 'HumanResource', 1);

--to insert employeeManager in menu
INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`, `isLeavesSystemMenu`) VALUES
(214, 'menuEmployeeManager', 208, 'object', 435, null, 0, 'HumanResource', 1);

--to insert dashboardEmployeeManager in menu
INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`, `isLeavesSystemMenu`) VALUES
(215, 'menuDashboardEmployeeManager', 208, 'item', 440, null, 0, 'HumanResource', 1);

--to insert HumanResourceParameters in menu
INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`, `isLeavesSystemMenu`) VALUES
(216,'menuHumanResourceParameters', 208, 'menu', 445, null, 1, 'HumanResource', 1);

--to insert LeaveType in menu
INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`, `isLeavesSystemMenu`) VALUES
(217, 'menuLeaveType', 216, 'object', 450, null, 0, 'HumanResource', 1);

--to insert contractType in menu
INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`, `isLeavesSystemMenu`) VALUES
(218, 'menuEmploymentContractType', 216, 'object', 455, null, 0, 'HumanResource', 1);

--to insert employmentContractEndReason in menu
INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`, `isLeavesSystemMenu`) VALUES
(219, 'menuEmploymentContractEndReason', 216, 'object', 460, null, 0, 'HumanResource', 1);

--to insert leavesSystemHabilitation in menu
INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`, `isLeavesSystemMenu`) VALUES
(220, 'menuLeavesSystemHabilitation', 216, 'item', 465, null, 0, 'HumanResource', 1);

-- to insert default LeavesSystemHabilitation
INSERT INTO `${prefix}leavessystemhabilitation` (`id`, `menuName`, `viewAccess`, `readAccess`, `createAccess`, `updateAccess`, `deleteAccess`) VALUES
(1, 'menuHumanResource', 'AME', NULL, NULL, NULL, NULL),
(2, 'menuHumanResourceParameters', 'AME', NULL, NULL, NULL, NULL),
(3, 'menuLeavesSystemHabilitation', 'A', 'A', 'A', 'A', 'A'),
(4, 'menuEmploymentContractType', 'AM', 'AM', 'AM', 'AM', 'AM'),
(5, 'menuLeaveCalendar', 'E', NULL, NULL, NULL, NULL),
(6, 'menuLeaveType', 'A', 'A', 'A', 'A', 'A'),
(7, 'menuLeave', 'E', 'AmO', 'E', 'AmO', 'AmO'),
(8, 'menuEmployee', 'AME', 'AmO', '', 'AmO', ''),
(9, 'menuEmploymentContract', 'AME', 'AmO', 'AM', 'AmO', 'A'),
(10, 'menuEmployeeLeaveEarned', 'E', 'AMO', 'AM', 'AM', 'AM'),
(11, 'menuEmploymentContractEndReason', 'A', 'A', 'A', 'A', 'A'),
(12, 'menuLeaveTypeOfEmploymentContractType', 'A', 'A', 'A', 'A', 'A'),
(13, 'menuEmployeeManager', 'AM', 'AMO', 'AM', 'AM', 'AO'),
(14, 'menuDelegationManager', 'AM', 'AME', 'AO', 'AO', 'AO'),
(15, 'menuDashboardEmployeeManager', 'Am', NULL, NULL, NULL, NULL);

-- to insert the parameter leavesSystemActiv in the table parameter
INSERT INTO `${prefix}parameter` (`parameterCode`,`parameterValue`) VALUES ('leavesSystemActiv', 'NO');

-- to insert the parameter leavesSystemAdmin in the table parameter
INSERT INTO `${prefix}parameter` (`parameterCode`,`parameterValue`) VALUES ('leavesSystemAdmin', 1);

-- to insert the parameter typeExportXLSorODS in the table parameter
INSERT INTO `${prefix}parameter` (`parameterCode`,`parameterValue`) VALUES ('typeExportXLSorODS', 'Excel');

--to insert the rulable classes in rulableforempcontracttype
INSERT INTO `${prefix}rulableforempcontracttype` (`rulableItem`,`name`,`idle`) VALUES
    ('Employee','Employee',0),
    ('EmploymentContract','EmploymentContract',0),
    ('EmployeeLeaveEarned','EmployeeLeaveEarned',0),
    ('Leave','Leave',0);

--to insert the notifiable
INSERT INTO `${prefix}notifiable` (`notifiableItem`,`name`,`idle`) VALUES
    ('Leave','Leave',0),
    ('EmployeeLeaveEarned','Leave Earned',0),
    ('Workflow','Workflow',0),
    ('Status', 'Status',0),
    ('LeaveType', 'Leave Type',0);
    
ALTER TABLE `${prefix}workflow` ADD `isLeaveWorkflow` int(1) UNSIGNED DEFAULT 0;

-- ======================================================
-- Shedule sending of report result by mail
-- ======================================================
CREATE TABLE `${prefix}cronautosendreport` (
    `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(100) DEFAULT NULL,
    `idReport` int(12) unsigned DEFAULT NULL,
    `idResource` int(12) unsigned DEFAULT NULL,
    `idReceiver` int(12) unsigned DEFAULT NULL,
    `idle` int(1) DEFAULT NULL,
    `sendFrequency` varchar(100) DEFAULT NULL,
    `otherReceiver` varchar(500) DEFAULT NULL,
    `cron` varchar(100) DEFAULT NULL,
    `nextTime` varchar(100) DEFAULT NULL,
    `reportParameter` varchar(500) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ======================================================
-- MODULES MANAGEMENT
-- ======================================================

CREATE TABLE `${prefix}module` (
    `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(100) DEFAULT NULL,
    `sortOrder` int(5) DEFAULT NULL,
    `idModule` int(12) unsigned DEFAULT NULL,
    `idle` int(1) DEFAULT 0,
    `active` int(1) DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `${prefix}modulemenu` (
    `id` INT(12) unsigned NOT NULL AUTO_INCREMENT,
    `idModule` int(12) unsigned DEFAULT NULL,
    `idMenu` int(12) unsigned DEFAULT NULL,
    `hidden` int(1) DEFAULT 1,
    `active` int(1) DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX moduleMenuMenu ON `${prefix}modulemenu` (idMenu);

CREATE TABLE `${prefix}modulereport` (
    `id` INT(12) unsigned NOT NULL AUTO_INCREMENT,
    `idModule` int(12) unsigned DEFAULT NULL,
    `idReport` int(12) unsigned DEFAULT NULL,
    `hidden` int(1) DEFAULT 1,
    `active` int(1) DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX moduleReportReport ON `${prefix}modulereport` (idReport);

INSERT INTO `${prefix}module` (`id`,`name`,`sortOrder`,`idModule`,`idle`,`active`) VALUES
 (1,'modulePlanning','100',null,0,1),
 (2,'moduleTicket','200',null,0,1),
 (3,'moduleImputation','300',null,0,1),
 (4,'moduleRequirement','400',null,0,1),
 (5,'moduleFinancial','500',null,0,1),
 (6,'moduleExpenses','510',5,0,1),
 (7,'moduleIncomes','520',5,0,1),
 (8,'moduleRisk','600',null,0,1),
 (9,'moduleMeeting','700',null,0,1),
 (10,'moduleReview','710',null,0,1),
 (11,'moduleConfiguration','800',null,0,1),
 (12,'moduleHumanResource','900',null,0,0),
 (13,'moduleNotification','1600',null,0,1),
 (14,'moduleOrganization','1100',null,0,1),
 (15,'moduleDocument','1000',null,0,1),
 (16,'moduleActivityStream','1500',null,0,1);
 
INSERT INTO `${prefix}modulemenu` (`id`,`idModule`,`idMenu`,`hidden`,`active`) VALUES
 (1,2,150,0,1),
 (2,14,158,0,1),
 (3,16,177,0,1),
 (4,15,102,0,1),
 (5,2,22,0,1),
 (6,2,118,0,1),
 (7,1,25,0,1),
 (8,1,26,0,1),
 (9,9,4,0,1),
 (10,8,4,0,1),
 (11,8,192,0,1),
 (12,10,192,0,1),
 (13,2,192,0,1),
 (14,3,8,0,1),
 (15,3,203,0,1),
 (16,1,196,0,1),
 (17,1,123,0,1),
 (18,1,106,0,1),
 (19,1,133,0,1),
 (20,4,189,0,1),
 (21,4,111,0,1),
 (22,4,112,0,1),
 (23,4,113,0,1),
 (24,6,197,0,1),
 (25,6,153,0,1),
 (26,6,154,0,1),
 (27,6,191,0,1),
 (28,6,195,0,1),
 (29,6,194,0,1),
 (30,6,201,0,1),
 (31,6,75,0,1),
 (32,6,76,0,1),
 (33,7,131,0,1),
 (34,7,125,0,1),
 (35,7,96,0,1),
 (36,7,97,0,1),
 (37,7,78,0,1),
 (38,7,94,0,1),
 (39,7,146,0,1),
 (40,7,174,0,1),
 (41,8,3,0,1),
 (42,8,119,0,1),
 (43,8,5,0,1),
 (44,9,62,0,1),
 (45,9,124,0,1),
 (46,10,63,0,1),
 (47,10,64,0,1),
 (48,10,168,0,1),
 (49,10,167,0,1),
 (50,10,176,0,1),
 (51,11,86,0,1),
 (52,11,87,0,1),
 (53,11,141,0,1),
 (54,11,142,0,1),
 (55,11,179,0,1),
 (56,13,185,0,1),
 (57,2,104,0,1),
 (58,6,148,0,1),
 (59,7,95,0,1),
 (60,15,103,0,1),
 (61,2,89,0,1),
 (62,2,182,0,1),
 (63,1,162,0,1),
 (64,13,186,0,1),
 (65,2,149,1,1),
 (66,8,39,1,1),
 (67,7,39,1,1),
 (68,8,40,1,1),
 (69,4,40,1,1),
 (70,2,40,1,1),
 (71,8,38,1,1),
 (72,4,42,1,1),
 (73,2,42,1,1),
 (74,8,41,1,1),
 (75,9,41,1,1),
 (76,4,41,1,1),
 (77,2,41,1,1),
 (78,4,114,1,1),
 (79,4,115,1,1),
 (80,9,117,1,1),
 (81,8,117,1,1),
 (82,6,137,1,1),
 (83,7,137,1,1),
 (84,6,138,1,1),
 (85,7,138,1,1),
 (86,6,139,1,1),
 (87,7,139,1,1),
 (88,6,140,1,1),
 (89,7,140,1,1),
 (90,6,199,1,1),
 (91,6,200,1,1),
 (92,6,157,1,1),
 (93,10,171,1,1),
 (94,10,163,1,1),
 (95,10,172,1,1),
 (96,10,164,1,1),
 (97,11,178,1,1),
 (98,14,159,1,1),
 (99,2,53,1,1),
 (100,1,55,1,1),
 (101,1,56,1,1),
 (102,6,198,1,1),
 (103,6,155,1,1),
 (104,6,156,1,1),
 (105,6,190,1,1),
 (106,6,193,1,1),
 (107,6,202,1,1),
 (108,6,80,1,1),
 (109,6,81,1,1),
 (110,6,84,1,1),
 (111,7,132,1,1),
 (112,7,126,1,1),
 (113,7,100,1,1),
 (114,7,83,1,1),
 (115,7,175,1,1),
 (116,8,45,1,1),
 (117,7,82,1,1),
 (118,8,120,1,1),
 (119,9,60,1,1),
 (120,8,60,1,1),
 (121,8,46,1,1),
 (122,9,65,1,1),
 (123,10,66,1,1),
 (124,10,67,1,1),
 (125,15,101,1,1),
 (126,2,105,1,1),
 (127,4,107,1,1),
 (128,4,108,1,1),
 (129,4,109,1,1),
 (130,6,147,1,1),
 (131,11,144,1,1),
 (132,11,145,1,1),
 (133,11,160,1,1),
 (134,11,161,1,1),
 (135,7,166,1,1),
 (136,10,165,1,1),
 (137,10,183,1,1),
 (138,1,9,0,1),
 (139,12,209,0,1),
 (140,12,210,0,1),
 (141,12,211,0,1),
 (142,12,212,0,1),
 (143,12,213,0,1),
 (144,12,214,0,1),
 (145,12,215,0,1),
 (146,12,216,1,1),
 (147,12,217,1,1),
 (148,12,218,1,1),
 (149,12,219,1,1),
 (150,12,220,1,1),
 (151,3,204,0,1);
 
INSERT INTO `${prefix}modulereport` (`id`,`idModule`,`idReport`,`hidden`,`active`) VALUES
 (1,3,1,0,1),
 (2,3,2,0,1),
 (3,3,3,0,1),
 (4,3,28,0,1),
 (5,3,29,0,1),
 (6,3,30,0,1),
 (7,3,54,0,1),
 (8,3,55,0,1),
 (9,3,56,0,1),
 (10,1,7,0,1),
 (11,1,49,0,1),
 (12,1,8,0,1),
 (13,1,78,0,1),
 (14,1,5,0,1),
 (15,1,6,0,1),
 (16,1,42,0,1),
 (17,1,31,0,1),
 (18,1,57,0,1),
 (19,1,58,0,1),
 (20,1,19,0,1),
 (21,1,20,0,1),
 (22,1,76,0,1),
 (23,1,77,0,1),
 (24,1,32,0,1),
 (25,1,52,0,1),
 (26,1,4,0,1),
 (27,1,60,0,1),
 (28,2,9,0,1),
 (29,2,10,0,1),
 (30,2,11,0,1),
 (31,2,12,0,1),
 (32,2,13,0,1),
 (33,2,17,0,1),
 (34,2,14,0,1),
 (35,2,15,0,1),
 (36,2,16,0,1),
 (37,2,18,0,1),
 (38,2,73,0,1),
 (39,2,74,0,1),
 (40,2,80,0,1),
 (41,2,83,0,1),
 (42,2,21,0,1),
 (43,1,21,0,1),
 (44,2,22,0,1),
 (45,1,22,0,1),
 (46,8,23,0,1),
 (47,8,47,0,1),
 (48,11,39,0,1),
 (49,1,63,0,1),
 (50,1,26,0,1),
 (51,6,26,0,1),
 (52,1,27,0,1),
 (53,6,27,0,1),
 (54,6,33,0,1),
 (55,6,34,0,1),
 (56,6,35,0,1),
 (57,1,36,0,1),
 (58,6,36,0,1),
 (59,7,37,0,1),
 (60,7,45,0,1),
 (61,7,46,0,1),
 (62,6,86,0,1),
 (63,6,87,0,1),
 (64,4,44,0,1),
 (65,4,41,0,1),
 (66,4,53,0,1),
 (67,4,43,0,1),
 (68,4,81,0,1),
 (69,4,82,0,1),
 (70,4,88,0,1),
 (71,4,79,0,1),
 (72,4,84,0,1),
 (73,4,89,0,1),
 (74,4,90,0,1),
 (75,4,91,0,1),
 (76,4,92,0,1),
 (77,1,59,0,1),
 (78,1,61,0,1),
 (79,1,62,0,1),
 (80,1,75,0,1),
 (81,2,75,0,1),
 (82,1,64,0,1),
 (83,1,65,0,1),
 (84,3,66,0,1),
 (85,3,67,0,1),
 (86,10,69,0,1),
 (87,10,70,0,1),
 (88,10,71,0,1),
 (89,10,72,0,1),
 (90,7,68,0,1);


INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(221, 'menuModule', 37  , 'item', 1205, Null, 0, 'Admin HabilitationParameter');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 221, 1);

-- ==============================================================================
-- Mailable 
-- ==============================================================================
INSERT INTO `${prefix}mailable` (`id`,`name`, `idle`) VALUES 
(36,'ProviderOrder', '0'),
(37,'ProviderBill', '0'),
(38,'ProviderPayment', '0'),
(39,'Budget', '0');

		
-- ==============================================================================
-- gautier #resourceCapacity 
-- ==============================================================================

CREATE TABLE `${prefix}resourcecapacity` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idResource` INT(12) NOT NULL,
  `capacity` decimal(8,5) unsigned default null,
  `description`  mediumtext,
  `idle` int(1) unsigned DEFAULT 0,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE INDEX `resourcevariablecapacity` ON `${prefix}resourcecapacity` (`idResource`);

-- ===============================================================================
-- plugin button
-- ===============================================================================
CREATE TABLE `${prefix}pluginbutton` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idPlugin` int(12) unsigned DEFAULT NULL,
  `buttonName` varchar(100),
  `className` varchar(100),
  `scriptJS` varchar(255),
  `scriptPHP` varchar(255),
  `iconClass` varchar(100),
  `scope` varchar(10),
  `sortOrder` varchar(100),
  `idle` int(1) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX pluginbuttonplugin ON `${prefix}pluginbutton` (idPlugin);
CREATE INDEX pluginbuttonclassname ON `${prefix}pluginbutton` (className);

-- ===========================
-- FIX
-- ===========================
UPDATE `${prefix}textable` SET `name`='PeriodicMeeting' WHERE `name`='Periodic Meeting';
