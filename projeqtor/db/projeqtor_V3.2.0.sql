
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V3.2.0                                      //
-- // Date : 2012-12-06                                     //
-- ///////////////////////////////////////////////////////////
--
--
ALTER TABLE `${prefix}opportunity` ADD COLUMN `idPriority` int(12) unsigned;
ALTER TABLE `${prefix}risk` ADD COLUMN `idPriority` int(12) unsigned;

INSERT INTO `${prefix}importable` (`id`, `name`, `idle`) VALUES
(26, 'Opportunity', 0);

INSERT INTO `${prefix}linkable` (`id`,`name`,`idle`, idDefaultLinkable) VALUES
(15,'IndividualExpense',0,4);

CREATE TABLE `${prefix}health` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}health` (`id`, `name`, `color`, `sortOrder`, `idle`) VALUES
(1,'secured','#32CD32',100,0),
(2,'surveyed','#ffd700',200,0),
(3,'in danger','#FF0000',300,0),
(4,'crashed','#000000',400,0),
(5,'paused','#E0E0E0',500,0);

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`) VALUES 
(121,'menuHealth',36,'object',707,NULL,0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 121, 1),
(2, 121, 0),
(3, 121, 0),
(4, 121, 0),
(5, 121, 0),
(6, 121, 0),
(7, 121, 0);

ALTER TABLE `${prefix}project` ADD COLUMN `idHealth` int(12) unsigned;

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`) VALUES
(47, 'reportOpportunityPlan', 4, 'opportunityPlan.php', 440);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES
(47, 'idProject', 'projectList', 10, 'currentProject');

CREATE TABLE `${prefix}audit` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `sessionId` varchar(100),
  `auditDay` varchar(8),
  `connection` datetime,
  `disconnection` datetime,
  `lastAccess` datetime,
  `duration` time,
  `idUser` int(12) unsigned,
  `userName` varchar(100),
  `userAgent` varchar(400),
  `platform` varchar(100),
  `browser` varchar(100),
  `browserVersion` varchar(100),
  `requestRefreshParam` int(1) default 0,
  `requestDisconnection` int(1) default 0,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX auditUser ON `${prefix}audit` (idUser);
CREATE INDEX auditSessionId ON `${prefix}audit` (sessionId);

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`) VALUES 
(122,'menuAudit',13,'object',977,NULL,0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 122, 1),
(2, 122, 0),
(3, 122, 0),
(4, 122, 0),
(5, 122, 0),
(6, 122, 0),
(7, 122, 0);

CREATE TABLE `${prefix}auditsummary` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `auditDay` varchar(8),
  `firstConnection` datetime,
  `lastConnection` datetime,
  `numberSessions` int(10),
  `minDuration` time,
  `maxDuration` time,
  `meanDuration` time,
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8;
 
CREATE INDEX auditsummaryAuditDay ON `${prefix}auditsummary` (auditDay);

INSERT INTO `${prefix}parameter` (idUser, idProject, parameterCode, parameterValue ) values 
(null, null, 'msgClosedApplication', 'Application is closed. \nOnly admin user can connect. \nPlease come back later.');

-- Purge PlanningElement for closed activities and projets
DELETE FROM `${prefix}plannedwork` WHERE (refType, refId) IN (SELECT refType, refId FROM `${prefix}planningelement` WHERE idle=1);

INSERT INTO `${prefix}reportcategory` (`id`, `name`, `sortOrder`, `idle`) VALUES 
(9, 'reportCategoryMisc', 80, 0);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `idle`) VALUES 
(48, 'reportAudit', 9, 'audit.php', 910, 0);

INSERT INTO `${prefix}reportparameter` (`idReport`,`name`,`paramType`,`sortOrder`,`idle`,`defaultValue`) VALUES
(48,'month','month',10,0,'currentMonth');

INSERT INTO `${prefix}habilitationreport` (`idProfile`,`idReport`,`allowAccess`) VALUES
(1,48,1),
(2,48,0),
(3,48,0),
(4,48,0),
(1,48,0),
(2,48,0),
(3,48,0),
(4,48,0);