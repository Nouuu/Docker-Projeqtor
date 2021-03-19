
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V2.4.0                                      //
-- // Date : 2012-06-17                                     //
-- ///////////////////////////////////////////////////////////
--
--
UPDATE `${prefix}menu` set sortOrder=sortOrder+100
where sortOrder>=250 and sortOrder<=399;

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`) VALUES 
(107,'menuRequirementType',79,'object',954,NULL,0),
(108,'menuTestCaseType',79,'object',955,NULL,0),
(109,'menuTestSessionType',79,'object',956,NULL,0),
(110, 'menuRequirementTest',0,'menu',300,NULL,0),
(111, 'menuRequirement',110,'object',310,'project',0),
(112, 'menuTestCase',110,'object',320,'project',0),
(113, 'menuTestSession',110,'object',330,'project',0),
(114, 'menuRiskLevel',36,'object',761,NULL,0),
(115, 'menuFeasibility',36,'object',762,NULL,0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 107, 1),
(2, 107, 0),
(3, 107, 0),
(4, 107, 0),
(5, 107, 0),
(6, 107, 0),
(7, 107, 0),
(1, 108, 1),
(2, 108, 0),
(3, 108, 0),
(4, 108, 0),
(5, 108, 0),
(6, 108, 0),
(7, 108, 0),
(1, 109, 1),
(2, 109, 0),
(3, 109, 0),
(4, 109, 0),
(5, 109, 0),
(6, 109, 0),
(7, 109, 0),
(1, 111, 1),
(2, 111, 1),
(3, 111, 1),
(4, 111, 1),
(5, 111, 1),
(6, 111, 1),
(7, 111, 1),
(1, 112, 1),
(2, 112, 1),
(3, 112, 1),
(4, 112, 1),
(5, 112, 1),
(6, 112, 1),
(7, 112, 1),
(1, 113, 1),
(2, 113, 1),
(3, 113, 1),
(4, 113, 1),
(5, 113, 1),
(6, 113, 1),
(7, 113, 1),
(1, 114, 1),
(2, 114, 0),
(3, 114, 0),
(4, 114, 0),
(5, 114, 0),
(6, 114, 0),
(7, 114, 0),
(1, 115, 1),
(2, 115, 0),
(3, 115, 0),
(4, 115, 0),
(5, 115, 0),
(6, 115, 0),
(7, 115, 0);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1, 111, 8),
(2, 111, 2),
(3, 111, 7),
(4, 111, 7),
(6, 111, 2),
(7, 111, 2),
(5, 111, 9),
(1, 112, 8),
(2, 112, 2),
(3, 112, 7),
(4, 112, 7),
(6, 112, 2),
(7, 112, 2),
(5, 112, 9),
(1, 113, 8),
(2, 113, 2),
(3, 113, 7),
(4, 113, 7),
(6, 113, 2),
(7, 113, 2),
(5, 113, 9);

INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `color`, idWorkflow, code, mandatoryDescription, mandatoryResultOnDone, mandatoryResourceOnHandled, lockHandled, lockDone, lockIdle) VALUES
('Requirement', 'Functional', 10, 0, NULL, 1, 'FUNC', 1,0,1,1,1,1),
('Requirement', 'Technical', 20, 0, NULL, 1, 'TECH', 1,0,1,1,1,1),
('Requirement', 'Security', 30, 0, NULL, 1, 'SECU', 1,0,1,1,1,1),
('Requirement', 'Regulatory', 40, 0, NULL, 1, 'REGL', 1,0,1,1,1,1),
('TestCase', 'Requirement test', 10, 0, NULL, 1, 'REQU', 1,1,1,1,1,1),
('TestCase', 'Non regression', 30, 0, NULL, 1, 'NR', 1,1,1,1,1,1),
('TestCase', 'Unit test', 20, 0, NULL, 1 , 'UT', 1,1,1,1,1,1),
('TestSession', 'Evolution test session', 10, 0, NULL, 1, 'EVO', 1,1,1,1,1,1),
('TestSession', 'Development test session', 20, 0, NULL, 1, 'DEV', 1,1,1,1,1,1),
('TestSession', 'Non regression test session', 30, 0, NULL, 1 , 'NR', 1,1,1,1,1,1),
('TestSession', 'Unitary case test session', 40, 0, NULL, 1 , 'UT', 1,1,1,1,1,1);

CREATE TABLE `${prefix}risklevel` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}risklevel` (id, name, color, sortOrder, idle) VALUES
(1, 'Very Low', '#00FF00', 100, 0),
(2, 'Low', '#00AAAA', 200,0),
(3, 'Average', '#AAAAAA', 300,0),
(4, 'High', '#AAAA00', 400,0),
(5, 'Very High', '#FF0000', 500,0);

CREATE TABLE `${prefix}feasibility` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;
INSERT INTO `${prefix}feasibility` (id, name, color, sortOrder, idle) VALUES
(1, 'Yes', '#00FF00', 100, 0),
(2, 'Investigate', '#AAAA00', 200,0),
(3, 'No', '#FF0000', 300,0);

CREATE TABLE `${prefix}runstatus` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;
INSERT INTO `${prefix}runstatus` (id, name, color, sortOrder, idle) VALUES
(1, 'planned', '#FFFFFF', 100, 0),
(2, 'passed', '#32CD32', 200,0),
(3, 'failed', '#FF0000', 300,0),
(4, 'blocked', '#FFA500', 400,0);

CREATE TABLE `${prefix}requirement` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(100) DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idProduct` int(12) unsigned DEFAULT NULL,
  `idVersion` int(12) unsigned DEFAULT NULL,
  `idRequirementType`  int(12) unsigned DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `externalReference` varchar(100) DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `creationDateTime` datetime DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `idRequirement` int(12) unsigned DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `handled` int(1) unsigned DEFAULT '0',
  `handledDate` date DEFAULT NULL,
  `done` int(1) unsigned DEFAULT '0',
  `doneDate` date DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `idleDate` date DEFAULT NULL,
  `idTargetVersion` int(12) unsigned DEFAULT NULL,
  `plannedWork` decimal(14,5) UNSIGNED DEFAULT '0',
  `idUrgency` int(12) unsigned DEFAULT NULL,
  `idCriticality` int(12) unsigned DEFAULT NULL,
  `idFeasibility` int(12) unsigned DEFAULT NULL,
  `idRiskLevel` int(12) unsigned DEFAULT NULL,
  `result` varchar(4000) DEFAULT NULL,
  `countPassed` int(5) default 0,
  `countFailed` int(5) default 0,
  `countBlocked` int(5) default 0,
  `countPlanned` int(5) default 0,
  `countLinked` int(5) default 0,
  `countIssues` int(5) default 0,
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;
CREATE INDEX requirementProject ON `${prefix}requirement` (idProject);
CREATE INDEX requirementProduct ON `${prefix}requirement` (idProduct);
CREATE INDEX requirementVersion ON `${prefix}requirement` (idVersion);
CREATE INDEX requirementType ON `${prefix}requirement` (idRequirementType);
CREATE INDEX requirementUser ON `${prefix}requirement` (idUser);
CREATE INDEX requirementRequirement ON `${prefix}requirement` (idRequirement);
CREATE INDEX requirementStatus ON `${prefix}requirement` (idStatus);
CREATE INDEX requirementResource ON `${prefix}requirement` (idResource);
CREATE INDEX requirementTargetVersion ON `${prefix}requirement` (idTargetVersion);
CREATE INDEX requirementUrgency ON `${prefix}requirement` (idUrgency);
CREATE INDEX requirementCriticality ON `${prefix}requirement` (idCriticality);
CREATE INDEX requirementFeasibility ON `${prefix}requirement` (idFeasibility);
CREATE INDEX requiremenRiskLevel ON `${prefix}requirement` (idRiskLevel);

CREATE TABLE `${prefix}testcase` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(100) DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idProduct` int(12) unsigned DEFAULT NULL,
  `idVersion` int(12) unsigned DEFAULT NULL,
  `idTestCaseType`  int(12) unsigned DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `externalReference` varchar(100) DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `creationDateTime` datetime DEFAULT NULL,
  `idContext1` int(12) unsigned DEFAULT NULL,
  `idContext2` int(12) unsigned DEFAULT NULL,
  `idContext3` int(12) unsigned DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `idTestCase` int(12) unsigned DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `handled` int(1) unsigned DEFAULT '0',
  `handledDate` date DEFAULT NULL,
  `done` int(1) unsigned DEFAULT '0',
  `doneDate` date DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `idleDate` date DEFAULT NULL,
  `idPriority` int(12) unsigned DEFAULT NULL,
  `prerequisite` varchar(4000) DEFAULT NULL,
  `result` varchar(4000) DEFAULT NULL,
  `sessionCount` int(5) default 0,
  `runCount` int(5) default 0,
  `lastRunDate` date DEFAULT NULL,
  `idRunStatus` int(12) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;
CREATE INDEX testcaseProject ON `${prefix}testcase` (idProject);
CREATE INDEX testcaseProduct ON `${prefix}testcase` (idProduct);
CREATE INDEX testcaseVersion ON `${prefix}testcase` (idVersion);
CREATE INDEX testcaseType ON `${prefix}testcase` (idTestCaseType);
CREATE INDEX testcaseUser ON `${prefix}testcase` (idUser);
CREATE INDEX testcaseTestCase ON `${prefix}testcase` (idTestCase);
CREATE INDEX testcaseStatus ON `${prefix}testcase` (idStatus);
CREATE INDEX testcaseResource ON `${prefix}testcase` (idResource);
CREATE INDEX testcasePriority ON `${prefix}testcase` (idPriority);
CREATE INDEX testcaseRunStatus ON `${prefix}testcase` (idRunStatus);

CREATE TABLE `${prefix}testsession` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(100) DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idProduct` int(12) unsigned DEFAULT NULL,
  `idVersion` int(12) unsigned DEFAULT NULL,
  `idTestSessionType`  int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `externalReference` varchar(100) DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `creationDateTime` datetime DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `handled` int(1) unsigned DEFAULT '0',
  `handledDate` date DEFAULT NULL,
  `done` int(1) unsigned DEFAULT '0',
  `doneDate` date DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `idleDate` date DEFAULT NULL,
  `result` varchar(4000) DEFAULT NULL,
  `countPassed` int(5) default 0,
  `countFailed` int(5) default 0,
  `countBlocked` int(5) default 0,
  `countTotal` int(5) default 0,
  `countIssues` int(5) default 0,
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;
CREATE INDEX testsessionProject ON `${prefix}testsession` (idProject);
CREATE INDEX testsessionProduct ON `${prefix}testsession` (idProduct);
CREATE INDEX testsessionVersion ON `${prefix}testsession` (idVersion);
CREATE INDEX testsessionType ON `${prefix}testsession` (idTestSessionType);
CREATE INDEX testsessionUser ON `${prefix}testsession` (idUser);
CREATE INDEX testsessionStatus ON `${prefix}testsession` (idStatus);
CREATE INDEX testsessionResource ON `${prefix}testsession` (idResource);

INSERT INTO `${prefix}referencable` (`id`, `name`, `idle`) VALUES
(13, 'Requirement', 0),
(14, 'TestCase', 0),
(15, 'TestSession', 0);

ALTER TABLE `${prefix}linkable` ADD COLUMN `idDefaultLinkable`  int(12) unsigned DEFAULT NULL; 
UPDATE `${prefix}linkable` SET idDefaultLinkable='1' WHERE id in (2,3);
UPDATE `${prefix}linkable` SET idDefaultLinkable='3' WHERE id in (1);
UPDATE `${prefix}linkable` SET idDefaultLinkable='4' WHERE id in (5,6);
UPDATE `${prefix}linkable` SET idDefaultLinkable='5' WHERE id in (4);
UPDATE `${prefix}linkable` SET idDefaultLinkable='7' WHERE id in (7);
UPDATE `${prefix}linkable` SET idDefaultLinkable='8' WHERE id in (8,9);
UPDATE `${prefix}linkable` SET idDefaultLinkable='10' WHERE id in (10);
UPDATE `${prefix}linkable` SET idDefaultLinkable='10' WHERE id in (10);
INSERT INTO `${prefix}linkable` (`id`,`name`,`idle`, idDefaultLinkable) VALUES
(11,'Requirement',0,12),
(12,'TestCase',0,11),
(13,'TestSession',0,8);

CREATE TABLE `${prefix}testcaserun` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idTestCase` int(12) unsigned DEFAULT NULL,
  `idTestSession` int(12) unsigned DEFAULT NULL,
  `idRunStatus` int(12) unsigned DEFAULT NULL,
  `idTicket` int(12) unsigned DEFAULT NULL,
  `statusDateTime` datetime DEFAULT NULL,
  `comment` varchar(4000) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX testcaserunTestCase ON `${prefix}testcaserun` (idTestCase);
CREATE INDEX testcaserunTestSession ON `${prefix}testcaserun` (idTestSession);
CREATE INDEX testcaserunRunStatus ON `${prefix}testcaserun` (idRunStatus);
CREATE INDEX testcaserunTicket ON `${prefix}testcaserun` (idTicket);

UPDATE `${prefix}priority` set name='High priority'
WHERE name='Hight priority';

ALTER TABLE `${prefix}dependable` ADD COLUMN `scope`  varchar(10) DEFAULT 'PE',
ADD COLUMN `idDefaultDependable` int(12) unsigned DEFAULT NULL;
UPDATE `${prefix}dependable` SET idDefaultDependable='1' WHERE id in (1,2);
UPDATE `${prefix}dependable` SET idDefaultDependable='3' WHERE id='3';
INSERT INTO `${prefix}dependable` (`id`,`name`,`idle`, `scope`, `idDefaultDependable`) VALUES
(4,'Requirement',0,'R', '4'),
(5,'TestCase',0,'TC', '5');

INSERT INTO `${prefix}reportcategory` (`id`, `name`, `sortOrder`) VALUES
(8, 'reportCategoryRequirementTest', 70);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `idle`) VALUES 
(44,'reportRequirementTest',8,'requirementTest.php',810,0),
(41,'reportProductTest',8,'productTest.php',820,0),
(42,'reportPlanActivityMonthly',2,'activityPlan.php',252,0),
(43,'reportTestSession',8,'testSession.php',830,0);

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(119,44,'idProject','projectList',10,0,null),
(105,44,'idProduct','productList',20,0,null),
(106,44,'idVersion','versionList',30,0,null),
(107,44,'showDetail','showDetail',40,0,null),
(108,41,'idProject','projectList',10,0,null),
(109,41,'idProduct','productList',20,0,null),
(110,41,'idVersion','versionList',30,0,null),
(111,41,'showDetail','showDetail',40,0,null),
(112,42,'idProject', 'projectList', 10, 0, 'currentProject'),
(113,42,'month', 'month', 20, 0, 'currentMonth'),
(114,43,'idProject','projectList',10,0,null),
(115,43,'idProduct','productList',20,0,null),
(116,43,'idVersion','versionList',30,0,null),
(117,43,'idTestSession','testSessionList',40,0,null),
(118,43,'showDetail','showDetail',50,0,null);

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES
(1,44,1),
(2,44,1),
(3,44,1),
(4,44,0),
(5,44,0),
(6,44,0),
(7,44,0),
(1,41,1),
(2,41,1),
(3,41,1),
(4,41,0),
(5,41,0),
(6,41,0),
(7,41,0),
(1,42,1),
(2,42,1),
(3,42,1),
(4,42,0),
(5,42,0),
(6,42,0),
(7,42,0),
(1,43,1),
(2,43,1),
(3,43,1),
(4,43,0),
(5,43,0),
(6,43,0),
(7,43,0);

ALTER TABLE `${prefix}dependency` CHANGE dependencyDelay dependencyDelay INT(3);
ALTER TABLE `${prefix}dependency` ALTER dependencyDelay DROP DEFAULT;
ALTER TABLE `${prefix}dependency` ALTER dependencyDelay SET DEFAULT 0;

UPDATE `${prefix}dependency` set dependencyDelay=0;
