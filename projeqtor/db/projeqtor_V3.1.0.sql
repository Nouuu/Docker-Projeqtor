
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V3.1.0                               //
-- // Date : 2012-12-06                                     //
-- ///////////////////////////////////////////////////////////
--
--
ALTER TABLE `${prefix}requirement` ADD COLUMN `locked` int(1) unsigned default '0',
ADD COLUMN `idLocker` int(12) unsigned,
ADD COLUMN `lockedDate` datetime;

ALTER TABLE  `${prefix}habilitationother` 
CHANGE scope scope varchar(20);

INSERT INTO `${prefix}habilitationother` (`idProfile`, `scope`, `rightAccess`) VALUES
(1, 'requirement', 1),
(2, 'requirement', 2),
(3, 'requirement', 1),
(4, 'requirement', 2),
(6, 'requirement', 2),
(7, 'requirement', 2),
(5, 'requirement', 2);  

INSERT INTO `${prefix}habilitationother` (idProfile,scope,rightAccess) VALUES 
(1,'workValid','4'),
(2,'workValid','2'),
(3,'workValid','3'),
(4,'workValid','1'),
(6,'workValid','1'),
(7,'workValid','1'),
(5,'workValid','1');

CREATE TABLE `${prefix}workperiod` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idResource` int(12) unsigned,
  `periodRange` varchar(10),
  `periodValue` varchar(10),
  `submitted` int(1) unsigned default '0',
  `submittedDate` datetime,
  `validated` int(1) unsigned default '0',
  `validatedDate` datetime,
  `idLocker` int(12) unsigned,
  `comment` varchar(4000),
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX workperiodResource ON `${prefix}workperiod` (idResource);
CREATE INDEX workperiodPeriod ON `${prefix}workperiod` (periodRange, periodValue);

CREATE TABLE `${prefix}efficiency` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}efficiency` (`id`, `name`, `color`, `sortOrder`, `idle`) VALUES
(1,'fully efficient','#99FF99',100,0),
(2,'partially efficient','#87ceeb',200,0),
(3,'not efficient','#FF0000',300,0);

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`) VALUES 
(117,'menuEfficiency',36,'object',745,NULL,0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 117, 1),
(2, 117, 0),
(3, 117, 0),
(4, 117, 0),
(5, 117, 0),
(6, 117, 0),
(7, 117, 0);

ALTER TABLE `${prefix}action` ADD COLUMN `idEfficiency` int(12) unsigned default null;

UPDATE `${prefix}menu` SET sortOrder=705 WHERE name='menuStatus';
UPDATE `${prefix}menu` SET sortOrder=710 WHERE name='menuLikelihood';
UPDATE `${prefix}menu` SET sortOrder=715 WHERE name='menuCriticality';
UPDATE `${prefix}menu` SET sortOrder=720 WHERE name='menuSeverity';
UPDATE `${prefix}menu` SET sortOrder=725 WHERE name='menuUrgency';
UPDATE `${prefix}menu` SET sortOrder=730 WHERE name='menuPriority';
UPDATE `${prefix}menu` SET sortOrder=735 WHERE name='menuRiskLevel';
UPDATE `${prefix}menu` SET sortOrder=740 WHERE name='menuFeasibility';
UPDATE `${prefix}menu` SET sortOrder=760 WHERE name='menuPredefinedNote'; 

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `idle`) VALUES 
(45, 'reportTermMonthly', 7, 'term.php', 720, 0),
(46, 'reportTermWeekly', '7', 'term.php', '730', '0');

INSERT INTO `${prefix}reportparameter` (`idReport`,`name`,`paramType`,`sortOrder`,`idle`,`defaultValue`) VALUES
(45,'idProject','projectList',10,0,'currentProject'),
(45,'month','month',20,0,'currentMonth'),
(46,'idProject','projectList',10,0,'currentProject'),
(46,'week','week',20,0,'currentWeek');

INSERT INTO `${prefix}habilitationreport` (`idProfile`,`idReport`,`allowAccess`) VALUES
(1,45,1),
(2,45,1),
(3,45,0),
(4,45,0),
(1,46,1),
(2,46,1),
(3,46,0),
(4,46,0);

INSERT INTO `${prefix}parameter` (idUser, idProject, parameterCode, parameterValue) VALUES
(null,null, 'maxItemsInTodayLists', '100');

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`) VALUES 
(118,'menuTicketSimple',2,'object',125,'Project',0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 118, 0),
(2, 118, 0),
(3, 118, 0),
(4, 118, 0),
(5, 118, 1),
(6, 118, 0),
(7, 118, 0);

UPDATE `${prefix}habilitation` SET allowAccess='0' WHERE idProfile=5 and idMenu='22';

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) 
SELECT `idProfile`, 118, `idAccessProfile` FROM `${prefix}accessright` WHERE `idMenu`=22;  

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES 
(119, 'menuOpportunity', '43', 'object', '420', 'Project', '0'), 
(120, 'menuOpportunityType', '79', 'object', '885', NULL, '0');

CREATE TABLE `${prefix}opportunity` ( 
 `id` int(12) unsigned NOT NULL AUTO_INCREMENT ,
 `idProject` int(12) unsigned DEFAULT NULL ,
 `name` varchar(100) DEFAULT NULL ,
 `description` varchar(4000) DEFAULT NULL ,
 `idOpportunityType` int(12) unsigned DEFAULT NULL ,
 `cause` varchar(4000) DEFAULT NULL ,
 `impact` varchar(4000) DEFAULT NULL ,
 `idSeverity` int(12) unsigned DEFAULT NULL ,
 `idLikelihood` int(12) unsigned DEFAULT NULL ,
 `idCriticality` int(12) unsigned DEFAULT NULL ,
 `creationDate` date DEFAULT NULL ,
 `idUser` int(12) unsigned DEFAULT NULL ,
 `idStatus` int(12) unsigned DEFAULT NULL ,
 `idResource` int(12) unsigned DEFAULT NULL ,
 `initialEndDate` date DEFAULT NULL ,
 `actualEndDate` date DEFAULT NULL ,
 `idleDate` date DEFAULT NULL ,
 `result` varchar(4000) DEFAULT NULL ,
 `comment` varchar(4000) DEFAULT NULL ,
 `idle` int(1) unsigned DEFAULT '0',
 `done` int(1) unsigned DEFAULT '0',
 `doneDate` date DEFAULT NULL ,
 `handled` int(1) unsigned DEFAULT '0',
 `handledDate` date DEFAULT NULL ,
 `reference` varchar(100) DEFAULT NULL ,
 `externalReference` varchar(100) DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX opportunityProject ON `${prefix}opportunity` (idProject);
CREATE INDEX opportunityUser ON `${prefix}opportunity` (idUser);
CREATE INDEX opportunityResource ON `${prefix}opportunity` (idResource);
CREATE INDEX opportunityStatus ON `${prefix}opportunity` (idStatus);
CREATE INDEX opportunityType ON `${prefix}opportunity` (idOpportunityType);
CREATE INDEX opportunitySeverity ON `${prefix}opportunity` (idSeverity);
CREATE INDEX opportunityLikelihood ON `${prefix}opportunity` (idLikelihood);
CREATE INDEX opportunityCriticality ON `${prefix}opportunity` (idCriticality);

INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `color`, `idWorkflow`) VALUES
('Opportunity', 'Contractual', 10, 0, NULL, 1),
('Opportunity', 'Operational', 20, 0, NULL, 1),
('Opportunity', 'Technical', 30, 0, NULL, 1);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 119, 1),
(2, 119, 1),
(3, 119, 1),
(4, 119, 0),
(6, 119, 1),
(7, 119, 0),
(5, 119, 0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 120, 1),
(2, 120, 0),
(3, 120, 0),
(4, 120, 0),
(6, 120, 0),
(7, 120, 0),
(5, 120, 0);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) 
SELECT `idProfile`, 119, `idAccessProfile` FROM `${prefix}accessright` WHERE `idMenu`=3;  

ALTER TABLE `${prefix}planningelement` ADD COLUMN `expectedProgress` int(3) unsigned default '0';
UPDATE `${prefix}planningelement` SET expectedProgress=round(realWork/validatedWork*100) where validatedWork>0;

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES 
(19, 'idTeam', 'teamList', 20, null),
(19, 'week', 'week', 30, 'currentYear'),
(20, 'idTeam', 'teamList', 20, null),
(20, 'month', 'month', 30, 'currentYear');

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES 
(27, 'idTeam', 'teamList', 20, null),
(27, 'month', 'month', 30, 'currentYear');