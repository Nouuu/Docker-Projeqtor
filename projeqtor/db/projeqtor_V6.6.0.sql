-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 7.0.0                                       //
-- // Date : 2017-12-22                                     //
-- ///////////////////////////////////////////////////////////


-- ===========================================================
-- Email Template
-- ===========================================================
 
CREATE TABLE `${prefix}emailtemplate` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `title` varchar(500) DEFAULT NULL,
  `template` mediumtext DEFAULT NULL,
  `idMailable` int(12) DEFAULT NULL,
  `idType` int(12) UNSIGNED DEFAULT NULL,
  `idle` int(1) UNSIGNED DEFAULT 0,
  PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
  
CREATE INDEX `emailtemplateMailable` ON `${prefix}emailtemplate` (`idMailable`);

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(184,'menuEmailTemplate', 88, 'object', 585, 'ReadWriteEnvironment', 0, 'Automation');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 184, 1);

ALTER TABLE `${prefix}statusmail`
ADD `idEmailTemplate` int(12) UNSIGNED DEFAULT NULL;

-- end add gmartin 

-- ======================================================== --
-- Notification System                                      --
-- ======================================================== --

-- -------------------------------------------------------- --
--                   notificationSystemActiv                --
-- Indicates if the notification system is activ or not     --
-- -------------------------------------------------------- --
INSERT INTO `${prefix}parameter` (`idUser`,`idProject`,`parameterCode`,`parameterValue`) 
VALUES (NULL,NULL,'notificationSystemActiv','NO');

-- -------------------------------------------------------- --
--                   cronCheckNotification                  --
-- Interval in hours of notifications generation            --
-- -------------------------------------------------------- --
INSERT INTO `${prefix}parameter` (`idUser`,`idProject`,`parameterCode`,`parameterValue`)
VALUES (NULL,NULL,'cronCheckNotifications',3600);

-- -------------------------------------------------------- --
--                   NOTIFICATIONSTATUS                     --
-- This table contents the status for the notifications     --
-- -------------------------------------------------------- --
CREATE TABLE `${prefix}statusnotification` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) DEFAULT NULL,
  `color` VARCHAR(7) DEFAULT NULL,
  PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `${prefix}statusnotification` (`id`, `name`, `color`) VALUES(1, 'unread', '#ff7f50');
INSERT INTO `${prefix}statusnotification` (`id`, `name`, `color`) VALUES(2, 'read',   '#32CD32');

-- -------------------------------------------------------- --
--                   NOTIFICATION                           --
-- This table contents the notifications generated          --
-- by the CRON or created by the user                       --
-- -------------------------------------------------------- --
CREATE TABLE `${prefix}notification` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) DEFAULT NULL,
  `idNotificationDefinition` INT(12) UNSIGNED DEFAULT NULL,
  `idNotifiable` INT(12) UNSIGNED DEFAULT NULL,
  `idMenu` INT(12) UNSIGNED DEFAULT NULL,
  `idNotificationType` INT(12) UNSIGNED DEFAULT NULL,
  `idUser` INT(12) UNSIGNED DEFAULT NULL,
  `idResource` INT(12) UNSIGNED DEFAULT NULL,
  `idStatusNotification` INT(12) UNSIGNED DEFAULT NULL,  
  `title` VARCHAR(4000) DEFAULT NULL,
  `content` MEDIUMTEXT DEFAULT NULL,
  `creationDateTime` DATETIME DEFAULT NULL,
  `notificationDate` DATE DEFAULT NULL,
  `notificationTime` TIME DEFAULT NULL,
  `notifiedObjectId` INT(12) UNSIGNED DEFAULT NULL,
  `sendEmail` INT(1) NOT NULL DEFAULT 0,
  `emailSent` INT(1) NOT NULL DEFAULT 0,
  `idle` INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX `notificationHasNotificationDefinition_idx` ON `${prefix}notification` (`idNotificationDefinition`);
CREATE INDEX `notificationNotifiable_idx` ON `${prefix}notification` (`idNotifiable`);
CREATE INDEX `notificationStatusNotification_idx` ON `${prefix}notification` (`idStatusNotification`);
CREATE INDEX `notificationNotificationType_idx` ON `${prefix}notification` (`idNotificationType`);
CREATE INDEX `notificationMenu_idx` ON `${prefix}notification` (`idMenu`);
CREATE INDEX `notificationResource_idx` ON `${prefix}notification` (`idResource`);

-- -------------------------------------------------------- --
--                   NOTIFICATIONDEFINITION                 --
-- This table contents the rules for the generation of      --
-- notifications by the Cron or when something change on    --
-- NotificationDefinition                                   --
-- -------------------------------------------------------- --
CREATE TABLE `${prefix}notificationdefinition` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) DEFAULT NULL,
  `idNotifiable` INT(12) UNSIGNED DEFAULT NULL,
  `idMenu` INT(12) UNSIGNED DEFAULT NULL,
  `idNotificationType` INT(12) UNSIGNED DEFAULT NULL,
  `title` VARCHAR(100) DEFAULT NULL,
  `content` MEDIUMTEXT DEFAULT NULL,
  `notificationRule` VARCHAR(400) DEFAULT NULL,
  `notificationReceivers` VARCHAR(400) DEFAULT NULL,
  `sendEmail` INT(1) NOT NULL DEFAULT 0,
  `targetDateNotifiableField` VARCHAR(100) DEFAULT NULL,
  `everyDay` INT(1) NOT NULL DEFAULT 0,
  `everyWeek` INT(1) NOT NULL DEFAULT 0,
  `everyMonth` INT(1) NOT NULL DEFAULT 0,
  `everyYear` INT(1) NOT NULL DEFAULT 0,
  `fixedDay` INT(5) DEFAULT NULL,
  `fixedMonth` INT(5) DEFAULT NULL,
  `notificationNbRepeatsBefore` INT(5) DEFAULT NULL,  
  `notificationGenerateBefore` INT(5) DEFAULT NULL,
  `notificationGenerateBeforeInMin` INT(5) DEFAULT NULL,  
  `idle` INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX `notificationdefinitionNotifiable_idx` ON `${prefix}notificationdefinition` (`idNotifiable`);
CREATE INDEX `notificationdefinitionNotificationType_idx` ON `${prefix}notificationdefinition` (`idNotificationType`);
CREATE INDEX `notificationdefinitionMenu_idx` ON `${prefix}notificationdefinition` (`idMenu`);

-- -------------------------------------------------------- --
--                          NOTIFIABLE                      --
-- This table contents the classes of Projeqtor those can   --
-- be selected for define generation notification rules     --
-- -------------------------------------------------------- --
CREATE TABLE `${prefix}notifiable` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `notifiableItem` VARCHAR(100) DEFAULT NULL,
  `name` VARCHAR(100) DEFAULT NULL,
  `idle` INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `${prefix}notifiable` (`notifiableItem`,`name`) VALUES
 ('Action','Action'),
 ('Activity','Activity'),
 ('Bill','Bill'),
 ('Command','Command'),
 ('Deliverable','Deliverable'),
 ('Incoming','Incoming'),
 ('Issue','Issue'),
 ('Meeting','Meeting'),
 ('Milestone','Milestone'),
 ('Opportunity','Opportunity'),
 ('ProjectExpense','ProjectExpense'),
 ('Quotation','Quotation'),
 ('Requirement','Requirement'),
 ('Risk','Risk'),
 ('Ticket','Ticket'),
 ('Term','Term'),
 ('Delivery','Delivery');

-- -------------------------------------------------------- --
-- MENU                  For notification                   --
-- -------------------------------------------------------- --
INSERT INTO `${prefix}menu` (`id`, `name`,           `idMenu`,`type`,  `sortOrder`,`level`,  `idle`,`menuClass`) 
                      VALUES(185, 'menuNotification', 11,     'object', 431,       'Project', 0,    'Admin Notification');
-- -------------------------------------------------------- --
-- MENU               For notificationDefinition            --
-- -------------------------------------------------------- --
INSERT INTO `${prefix}menu` (`id`,`name`,                     `idMenu`,`type`,  `sortOrder`,`level`,               `idle`,`menuClass`) 
                      VALUES(186, 'menuNotificationDefinition',88,    'object', 672,       'ReadWriteEnvironment', 0,     'Automation Notification');

-- -------------------------------------------------------- --
--                     HABILITATION                         --
-- -------------------------------------------------------- --
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`,`allowAccess`) 
                             VALUES( 1,           185,    1);
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`,`allowAccess`) 
                             VALUES( 1,           186,    1);
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`,`allowAccess`) 
                             VALUES( 2,           185,    1);
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`,`allowAccess`) 
                             VALUES( 3,           185,    1);
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`,`allowAccess`) 
                             VALUES( 4,           185,    1);
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`,`allowAccess`) 
                             VALUES( 5,           185,    1);
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`,`allowAccess`) 
                             VALUES( 6,           185,    1);
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`,`allowAccess`) 
                             VALUES( 7,           185,    1);

-- -------------------------------------------------------- --
--                     ACCESS RIGHT                         --
-- -------------------------------------------------------- --
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`,`idAccessProfile`) 
                             VALUES( 1,           185,    8);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`,`idAccessProfile`) 
                             VALUES( 2,           185,    3);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`,`idAccessProfile`) 
                             VALUES( 3,           185,    3);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`,`idAccessProfile`) 
                             VALUES( 4,           185,    3);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`,`idAccessProfile`) 
                             VALUES( 5,           185,    3);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`,`idAccessProfile`) 
                             VALUES( 6,           185,    3);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`,`idAccessProfile`) 
                             VALUES( 7,           185,    3);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`,`idAccessProfile`) 
                             VALUES( 1,           186,    1000001);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`,`idAccessProfile`) 
                             VALUES( 2,           186,    1000002);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`,`idAccessProfile`) 
                             VALUES( 3,           186,    1000002);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`,`idAccessProfile`) 
                             VALUES( 4,           186,    1000002);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`,`idAccessProfile`) 
                             VALUES( 5,           186,    1000002);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`,`idAccessProfile`) 
                             VALUES( 6,           186,    1000002);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`,`idAccessProfile`) 
                             VALUES( 7,           186,    1000002);

-- -------------------------------------------------------- --
-- PBE - Change Meeting structure so that we can have       -- 
--       notification "minutes" before start of meeting     --
-- -------------------------------------------------------- --
ALTER TABLE `${prefix}meeting` ADD `meetingStartDateTime` DATETIME DEFAULT NULL,
ADD `meetingEndDateTime` DATETIME DEFAULT NULL;

UPDATE `${prefix}meeting` SET meetingStartDateTime=STR_TO_DATE(concat(meetingDate,' ',meetingStartTime),'%Y-%m-%d %H:%i:%s'),
meetingEndDateTime=STR_TO_DATE(concat(meetingDate,' ',meetingEndTime),'%Y-%m-%d %H:%i:%s');

-- ===========================================================
-- LifeCycle on Products, Components, Versions
-- ===========================================================

INSERT INTO `${prefix}type` (`name`, `scope`, `color`, `sortOrder`) VALUES 
 ('ALERT', 'Notification', '#ff0000', '10'),
 ('WARNING', 'Notification', '#ffa500', '20'),
 ('INFO', 'Notification', '#0000ff', '30');

-- --------------------------------------------------------
--ADD qCazelles - Ticket #53
-- --------------------------------------------------------
ALTER TABLE `${prefix}product` ADD `idStatus` int(12) UNSIGNED DEFAULT NULL;
ALTER TABLE `${prefix}version` ADD `idStatus` int(12) UNSIGNED DEFAULT NULL;
ALTER TABLE `${prefix}status` ADD `setIntoserviceStatus` int(1) UNSIGNED DEFAULT 0;
ALTER TABLE `${prefix}type` ADD `lockIntoservice` int(1) UNSIGNED DEFAULT 0;
--END ADD qCazelles - Ticket #53

-- --------------------------------------------------------
-- ADD PBE complement to Ticket #53
-- --------------------------------------------------------
-- define workflow for Product Type, Product Version Type, Component Type, Component Version Type
UPDATE `${prefix}type` set idWorkflow=(SELECT id FROM `${prefix}workflow` order by sortOrder, id LIMIT 1)
WHERE idWorkflow is null and `scope` in ('Product','ProductVersion','Component','ComponentVersion');

-- define status for Product, Product Version, Component, Component Version
UPDATE `${prefix}product` set idStatus=(SELECT id FROM `${prefix}status` ORDER BY sortOrder, id LIMIT 1)
WHERE idStatus is null;
UPDATE `${prefix}version` set idStatus=(SELECT id FROM `${prefix}status` ORDER BY sortOrder, id LIMIT 1)
WHERE idStatus is null;

-- define type for Product, Product Version, Component, Component Version
UPDATE `${prefix}product` set idProductType=(SELECT id FROM `${prefix}type` WHERE scope='Product' ORDER BY sortOrder, id LIMIT 1)
WHERE idProductType is null and scope='Product';
UPDATE `${prefix}product` set idComponentType=(SELECT id FROM `${prefix}type` WHERE scope='Component' ORDER BY sortOrder, id LIMIT 1)
WHERE idComponentType is null and scope='Component';
UPDATE `${prefix}version` set idVersionType=(SELECT id FROM `${prefix}type` WHERE scope='ProductVersion' ORDER BY sortOrder, id LIMIT 1)
WHERE idVersionType is null and scope='Product';
UPDATE `${prefix}version` set idVersionType=(SELECT id FROM `${prefix}type` WHERE scope='ComponentVersion' ORDER BY sortOrder, id LIMIT 1)
WHERE idVersionType is null and scope='Component';

-- ===========================================================
-- Component Version on Requirements 
-- ===========================================================

--ADD qCazelles - Add Component to Requirement - Ticket 171
ALTER TABLE `${prefix}requirement` ADD `idComponent` INT(12) UNSIGNED DEFAULT NULL;
ALTER TABLE `${prefix}requirement` ADD `idTargetComponentVersion` INT(12) UNSIGNED DEFAULT NULL;
ALTER TABLE `${prefix}requirement` CHANGE `idTargetVersion` `idTargetProductVersion` INT(12) UNSIGNED DEFAULT NULL;

-- ===========================================================
-- Manage milestone on Requirement
-- ===========================================================

ALTER TABLE `${prefix}requirement`
ADD `idMilestone` int(12) UNSIGNED DEFAULT NULL;

ALTER TABLE `${prefix}ticket`
ADD `idMilestone` int(12) UNSIGNED DEFAULT NULL;

ALTER TABLE `${prefix}version`
ADD `idMilestone` int(12) UNSIGNED DEFAULT NULL;

ALTER TABLE `${prefix}deliverable`
ADD `idMilestone` int(12) UNSIGNED DEFAULT NULL;

ALTER TABLE `${prefix}delivery`
ADD `idMilestone` int(12) UNSIGNED DEFAULT NULL;

-- ===========================================================
-- FIXINGS
-- ===========================================================

--ADD qCazelles - bug scope Delivery
ALTER TABLE `${prefix}delivery` DROP COLUMN `scope`;

-- ADD PBE : missing items in linkable, mailable, copyable, importable, referencable, textable
INSERT INTO `${prefix}checklistable` (`id`,`name`, `idle`) VALUES 
(24,'Bill', '0'),
(25,'CallForTender', '0'),
(26,'Client', '0'),
(27,'Contact', '0'),
(28,'IndividualExpense', '0'),
(29,'Payment', '0'),
(30,'ProjectExpense', '0'),
(31,'Provider', '0'),
(32,'Quotation', '0'),
(33,'Tender', '0');
INSERT INTO `${prefix}copyable` (`id`,`name`, `idle`, `sortOrder`) VALUES 
(20,'Delivery', '0', '800'),
(21,'Deliverable', '0', '800'),
(22,'Incoming', '0', '800');
INSERT INTO `${prefix}importable` (`id`,`name`, `idle`) VALUES 
(52,'ActivityPrice', '0'),
(53,'Term', '0');
INSERT INTO `${prefix}linkable` (`id`,`name`, `idle`, `idDefaultLinkable`) VALUES 
(29,'Delivery', null,0),
(30,'Tender', null,0);
INSERT INTO `${prefix}mailable` (`id`,`name`, `idle`) VALUES 
(30,'Delivery', '0'),
(31,'Client', '0'),
(32,'Payment', '0'),
(33,'Provider', '0'),
(34,'Team', '0'),
(35,'Tender', '0');
INSERT INTO `${prefix}referencable` (`id`,`name`, `idle`) VALUES 
(20,'CallForTender', '0'),
(21,'Deliverable', '0'),
(22,'Delivery', '0'),
(23,'Incoming', '0'),
(24,'Payment', '0'),
(25,'Tender', '0');
INSERT INTO `${prefix}originable` (`id`,`name`, `idle`) VALUES 
(19,'Bill', '0'),
(20,'Deliverable', '0'),
(21,'Delivery', '0'),
(22,'Incoming', '0'),
(23,'Opportunity', '0'),
(24,'Tender', '0');
INSERT INTO `${prefix}textable` (`id`,`name`, `idle`) VALUES
(39,'ActivityPrice', '0'),
(21,'CallForTender', '0'),
(22,'Client', '0'),
(23,'Command', '0'),
(24,'Component', '0'),
(25,'ComponentVersion', '0'),
(26,'Deliverable', '0'),
(27,'Delivery', '0'),
(28,'Document', '0'),
(29,'Incoming', '0'),
(30,'Notification', '0'),
(31,'Opportunity', '0'),
(32,'Organization', '0'),
(33,'Payment', '0'),
(34,'Periodic Meeting', '0'),
(35,'ProductVersion', '0'),
(36,'Provider', '0'),
(37,'Team', '0'),
(38,'Tender', '0');
DELETE FROM `${prefix}linkable` WHERE `name` IN ('Organization');
DELETE FROM `${prefix}textable` WHERE `name` IN ('Version');