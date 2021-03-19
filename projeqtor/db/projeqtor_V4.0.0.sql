
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V3.5.0                                      //
-- // Date : 2013-09-02                                     //
-- ///////////////////////////////////////////////////////////
--
--

CREATE TABLE `${prefix}command` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idCommandType` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `creationDate` datetime DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `additionalInfo` varchar(4000) DEFAULT NULL,
  `externalReference` varchar(100) DEFAULT NULL,
  `idActivity` int(12) unsigned DEFAULT NULL,
  `initialStartDate` date DEFAULT NULL,
  `initialEndDate` date DEFAULT NULL,
  `validatedEndDate` date DEFAULT NULL,
  `initialWork` decimal(12,2) DEFAULT '0.00',
  `initialPricePerDayAmount` decimal(12,2) DEFAULT '0.00',
  `initialAmount` decimal(12,2) DEFAULT '0.00',
  `addWork` decimal(12,2) DEFAULT '0.00',
  `addPricePerDayAmount` decimal(12,2) DEFAULT '0.00',
  `addAmount` decimal(12,2) DEFAULT '0.00',
  `validatedWork` decimal(12,2) DEFAULT '0.00',
  `validatedPricePerDayAmount` decimal(12,2) DEFAULT '0.00',
  `validatedAmount` decimal(12,2) DEFAULT '0.00',
  `comment` varchar(4000) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `done` int(1) unsigned DEFAULT '0',
  `cancelled` int(1) unsigned DEFAULT '0',
  `idleDate` date DEFAULT NULL,
  `doneDate` date DEFAULT NULL,
  `handled` int(1) unsigned DEFAULT '0',
  `handledDate` date DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX commandProject ON `${prefix}command` (idProject);
CREATE INDEX commandUser ON `${prefix}command` (idUser);
CREATE INDEX commandResource ON `${prefix}command` (idResource);
CREATE INDEX commandStatus ON `${prefix}command` (idStatus);
CREATE INDEX commandType ON `${prefix}command` (idCommandType);

INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `idWorkflow`, `mandatoryDescription`, `mandatoryResultOnDone`, `mandatoryResourceOnHandled`, `lockHandled`, `lockDone`, `lockIdle`, `code`) VALUES 
('Command', 'Fixed Price', '10', '0', '1', '0', '0', '0', '0', '1', '1', '');
INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `idWorkflow`, `mandatoryDescription`, `mandatoryResultOnDone`, `mandatoryResourceOnHandled`, `lockHandled`, `lockDone`, `lockIdle`, `code`) VALUES 
('Command', 'Per day', '20', '0', '1', '0', '0', '0', '0', '1', '1', '');

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES 
(125,'menuCommand', '74', 'object', '352', 'Project', 0);
INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `idle`) VALUES 
(126, 'menuCommandType', '79', 'object', '835', 0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 125, 1),
(2, 125, 1),
(3, 125, 1),
(4, 125, 0),
(5, 125, 0),
(6, 125, 0),
(7, 125, 0);
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 126, 1),
(2, 126, 0),
(3, 126, 0),
(4, 126, 0),
(5, 126, 0),
(6, 126, 0),
(7, 126, 0);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) 
SELECT `idProfile`, 125, `idAccessProfile` FROM `${prefix}accessright` WHERE `idMenu`=97;  

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) 
SELECT `idProfile`, 126, `idAccessProfile` FROM `${prefix}accessright` WHERE `idMenu`=100;  

INSERT INTO `${prefix}originable` (`id`,`name`, `idle`) VALUES (17,'Command', 0);
INSERT INTO `${prefix}mailable` (`id`,`name`, `idle`) VALUES (21,'Command', '0');
INSERT INTO `${prefix}linkable` (`id`,`name`, `idle`, `idDefaultLinkable`) VALUES (18,'Command', 0, 14);
INSERT INTO `${prefix}referencable` (`id`,`name`, `idle`) VALUES (16,'Command', 0);
INSERT INTO `${prefix}indicatorable` (`id`,`name`, `idle`) VALUES (10,'Command', '0');
INSERT INTO `${prefix}importable` (`id`,`name`, `idle`) VALUES (27,'Command', '0');
INSERT INTO `${prefix}copyable` (`id`,`name`, `idle`, `sortOrder`) VALUES (13,'Command', '0', '36');

INSERT INTO `${prefix}indicatorableindicator` (`idIndicatorable`, `nameIndicatorable`, `idIndicator`, `idle`) VALUES 
('10', 'Command', '8', '0');
INSERT INTO `${prefix}indicatorableindicator` (`idIndicatorable`, `nameIndicatorable`, `idIndicator`, `idle`) VALUES 
('10', 'Command', '5', '0');
INSERT INTO `${prefix}indicatorableindicator` (`idIndicatorable`, `nameIndicatorable`, `idIndicator`, `idle`) VALUES 
('10', 'Command', '6', '0');

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `idle`) VALUES 
(50, 'reportProject', '9', 'projectDashboard.php', '920', '0');

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(50, 'idProject', 'projectList', 10, 0, 'currentProject');

INSERT INTO `${prefix}habilitationreport` (`idProfile`,`idReport`,`allowAccess`) VALUES
(1,50,1),
(2,50,1),
(3,50,1);

INSERT INTO `${prefix}planningmode` (`id`, `applyTo`, `name`, `code`, `sortOrder`, `idle`, `mandatoryStartDate`, `mandatoryEndDate`) VALUES
(17, 'Activity', 'PlanningModeGROUP', 'GROUP', 150, 0 , 0, 0);

ALTER TABLE `${prefix}project` ADD COLUMN `clientCode` varchar(25),
ADD COLUMN `idOverallProgress`  int(12) unsigned DEFAULT NULL;

ALTER TABLE `${prefix}document` ADD COLUMN `externalReference` varchar(100);

ALTER TABLE `${prefix}statusmail` ADD COLUMN `idType` int(12) unsigned DEFAULT NULL;

CREATE TABLE `${prefix}overallprogress` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}overallprogress` (`id`, `name`, `sortOrder`, `idle`) VALUES
(1, '0%', 100, 0),
(2, '10%', 200, 0),
(3, '25%', 300, 0),
(4, '50%', 400, 0),
(5, '75%', 500, 0),
(6, '90%', 600, 0),
(7, '100%', 700, 0);

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`) VALUES 
(127,'menuOverallProgress',36,'object',708,NULL,0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 127, 1),
(2, 127, 0),
(3, 127, 0),
(4, 127, 0),
(5, 127, 0),
(6, 127, 0),
(7, 127, 0);

UPDATE `${prefix}planningelement` SET idle=1
WHERE refType='Meeting' AND EXISTS (select 'x' FROM `${prefix}meeting` M WHERE M.id=refId and M.idle=1);
UPDATE `${prefix}planningelement` SET done=1
WHERE refType='Meeting' AND EXISTS (select 'x' FROM `${prefix}meeting` M WHERE M.id=refId and M.done=1);

UPDATE `${prefix}planningelement` SET idle=1
WHERE refType='PeriodicMeeting' AND EXISTS (select 'x' FROM `${prefix}periodicmeeting` M WHERE M.id=refId and M.idle=1);

UPDATE `${prefix}document` Doc SET version = (select max(version) from `${prefix}documentversion` Ver where Ver.idDocument=Doc.id)
WHERE version is null;

CREATE TABLE `${prefix}columnselector` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `scope` varchar (10) DEFAULT NULL,
  `objectClass` varchar(50) DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `field` varchar(100) DEFAULT NULL,
  `attribute` varchar(100) DEFAULT NULL,
  `hidden` int(1) unsigned DEFAULT 0,
  `sortOrder` int(3) unsigned default 0,
  `widthPct` int(3) unsigned default 0,
  `name` varchar(100) DEFAULT NULL,
  `subItem` varchar(100) DEFAULT NULL,
  `formatter` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
CREATE INDEX scopeColumnSelector ON `${prefix}columnselector` (scope, objectClass, idUser);

ALTER TABLE `${prefix}audit` ADD COLUMN `requestRefreshProject` int(1) unsigned DEFAULT '0';

UPDATE `${prefix}parameter` SET parameterValue='ProjeQtOr' 
where parameterValue='ProjectOrRia' and parameterCode IN ('theme','defaultTheme');
UPDATE `${prefix}parameter` SET parameterValue='ProjeQtOrLight' 
where parameterValue='ProjectOrRiaLight' and parameterCode IN ('theme','defaultTheme');
UPDATE `${prefix}parameter` SET parameterValue='ProjeQtOrDark' 
where parameterValue='ProjectOrRiaContrasted' and parameterCode IN ('theme','defaultTheme');
UPDATE `${prefix}parameter` SET parameterValue='projeqtor' 
where parameterValue='projector' and parameterCode='paramDefaultPassword';

ALTER TABLE `${prefix}resource` ADD COLUMN `loginTry` int(5) unsigned DEFAULT '0',
ADD COLUMN `salt` varchar(100) default null,
ADD COLUMN `crypto` varchar(100) default 'md5';

UPDATE `${prefix}resource` SET crypto='md5' WHERE isLdap=0;

-- CANCELLED
ALTER TABLE `${prefix}status` ADD COLUMN `setCancelledStatus` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}status` SET setCancelledStatus=1 WHERE id=9;

ALTER TABLE `${prefix}type` ADD COLUMN `lockCancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}type` SET lockCancelled=lockIdle;

ALTER TABLE `${prefix}planningelement` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';

ALTER TABLE `${prefix}project` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}project` SET cancelled=1 WHERE idStatus=9;
UPDATE `${prefix}planningelement` PE SET cancelled=(select cancelled from `${prefix}project` XXX where PE.refId=XXX.id)
where refType='Project';

ALTER TABLE `${prefix}ticket` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}ticket` SET cancelled=1 WHERE idStatus=9;

ALTER TABLE `${prefix}activity` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}activity` SET cancelled=1 WHERE idStatus=9;
UPDATE `${prefix}planningelement` PE SET cancelled=(select cancelled from `${prefix}activity` XXX where PE.refId=XXX.id)
where refType='Activity';

ALTER TABLE `${prefix}milestone` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}milestone` SET cancelled=1 WHERE idStatus=9;
UPDATE `${prefix}planningelement` PE SET cancelled=(select cancelled from `${prefix}milestone` XXX where PE.refId=XXX.id)
where refType='Milestone';

ALTER TABLE `${prefix}action` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}milestone` SET cancelled=1 WHERE idStatus=9;

ALTER TABLE `${prefix}risk` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}risk` SET cancelled=1 WHERE idStatus=9;

ALTER TABLE `${prefix}opportunity` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}opportunity` SET cancelled=1 WHERE idStatus=9;

ALTER TABLE `${prefix}document` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}document` SET cancelled=1 WHERE idStatus=9;

ALTER TABLE `${prefix}issue` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}issue` SET cancelled=1 WHERE idStatus=9;

ALTER TABLE `${prefix}requirement` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}requirement` SET cancelled=1 WHERE idStatus=9;

ALTER TABLE `${prefix}testcase` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}testcase` SET cancelled=1 WHERE idStatus=9;

ALTER TABLE `${prefix}question` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}question` SET cancelled=1 WHERE idStatus=9;

ALTER TABLE `${prefix}decision` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}decision` SET cancelled=1 WHERE idStatus=9;

ALTER TABLE `${prefix}expense` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}expense` SET cancelled=1 WHERE idStatus=9;

ALTER TABLE `${prefix}bill` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}bill` SET cancelled=1 WHERE idStatus=9;

ALTER TABLE `${prefix}testsession` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}testsession` SET cancelled=1 WHERE idStatus=9;
UPDATE `${prefix}planningelement` PE SET cancelled=(select cancelled from `${prefix}testsession` XXX where PE.refId=XXX.id)
where refType='TestSession';

ALTER TABLE `${prefix}meeting` ADD COLUMN `cancelled` int(1) unsigned DEFAULT '0';
UPDATE `${prefix}meeting` SET cancelled=1 WHERE idStatus=9;
UPDATE `${prefix}planningelement` PE SET cancelled=(select cancelled from `${prefix}meeting` XXX where PE.refId=XXX.id)
where refType='Meeting';