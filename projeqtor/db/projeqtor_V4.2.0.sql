-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 4.2.0                                       //
-- // Date : 2014-01-11                                     //
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}resource` ADD COLUMN `cookieHash` varchar(400) DEFAULT NULL,
ADD COLUMN `passwordChangeDate` date DEFAULT NULL;

CREATE TABLE `${prefix}checklistdefinition` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `idChecklistable` int(12) unsigned DEFAULT NULL,
  `nameChecklistable` varchar(100),
  `idType` int(12) unsigned DEFAULT NULL,
  `lineCount` int(3) DEFAULT 0,
  `idle` int(1) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX checklistdefinitionChecklistable ON `${prefix}checklistdefinition` (idChecklistable);
CREATE INDEX checklistdefinitionNameChecklistable ON `${prefix}checklistdefinition` (nameChecklistable);
CREATE INDEX checklistdefinitionType ON `${prefix}checklistdefinition` (idType);

CREATE TABLE `${prefix}checklistdefinitionline` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idChecklistDefinition` int(12) unsigned DEFAULT NULL,
  `name` varchar(100),
  `title` varchar(1000),
  `check01` varchar(100) DEFAULT NULL,
  `check02` varchar(100) DEFAULT NULL,
  `check03` varchar(100) DEFAULT NULL,
  `check04` varchar(100) DEFAULT NULL,
  `check05` varchar(100) DEFAULT NULL,
  `title01` varchar(1000) DEFAULT NULL,
  `title02` varchar(1000) DEFAULT NULL,
  `title03` varchar(1000) DEFAULT NULL,
  `title04` varchar(1000) DEFAULT NULL,
  `title05` varchar(1000) DEFAULT NULL,
  `sortOrder` int(3) DEFAULT 0,
  `exclusive` int(1) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX checklistdefinitionlineChecklistDefinition ON `${prefix}checklistdefinitionline` (idChecklistDefinition);

CREATE TABLE `${prefix}checklist` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idChecklistDefinition` int(12) unsigned DEFAULT NULL,
  `refType` varchar(100),
  `refId` int(12) unsigned DEFAULT NULL,
  `checkCount` int(3) DEFAULT 0,
  `comment` varchar(4000),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX checklistReference ON `${prefix}checklist` (refType, refId);
CREATE INDEX checklistChecklistDefinition ON `${prefix}checklist` (idChecklistDefinition);

CREATE TABLE `${prefix}checklistline` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idChecklist` int(12) unsigned DEFAULT NULL,
  `idChecklistDefinitionLine` int(12) unsigned DEFAULT NULL,
  `value01` int(1) unsigned DEFAULT '0',
  `value02` int(1) unsigned DEFAULT '0',
  `value03` int(1) unsigned DEFAULT '0',
  `value04` int(1) unsigned DEFAULT '0',
  `value05` int(1) unsigned DEFAULT '0',
  `idUser`  int(12) unsigned DEFAULT NULL,
  `checkTime` datetime DEFAULT NULL,
  `comment` varchar(4000),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX checklistlineChecklist ON `${prefix}checklistline` (idChecklist);
CREATE INDEX checklistlineChecklistDefinitionLine ON `${prefix}checklistline` (idChecklistDefinitionLine);

UPDATE `${prefix}menu` SET idMenu=0 where id=88;
UPDATE `${prefix}menu` SET idMenu=88 where id=116;

UPDATE `${prefix}menu` SET sortOrder=10 where id=1;
UPDATE `${prefix}menu` SET idMenu=20 where id=16;
UPDATE `${prefix}menu` SET idMenu=30 where id=102;

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES 
(130, 'menuChecklistDefinition', '88', 'object', '630', null, 0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 130, 1),
(2, 130, 0),
(3, 130, 0),
(4, 130, 0),
(5, 130, 0),
(6, 130, 0),
(7, 130, 0);

INSERT INTO `${prefix}referencable` (`name`, `idle`) VALUES
('Opportunity',0);

CREATE TABLE `${prefix}checklistable` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `idle` int(1) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}checklistable` (`name`, `idle`) VALUES
('Ticket',0),
('Activity',0),
('Milestone',0),
('Risk',0),
('Action',0),
('Issue',0),
('Meeting',0),
('Decision',0),
('Question',0),
('Document',0),
('Requirement',0),
('TestCase',0),
('TestSession',0),
('Command',0),
('Opportunity',0),
('Project',0);

INSERT INTO `${prefix}habilitationother` (`idProfile`, `scope`, `rightAccess`) VALUES
(1, 'checklist', 1),
(2, 'checklist', 1),
(3, 'checklist', 1),
(4, 'checklist', 1),
(6, 'checklist', 2),
(7, 'checklist', 2),
(5, 'checklist', 2);  

CREATE TABLE `${prefix}quotation` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idQuotationType` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `creationDate` datetime DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `idClient` int(12) unsigned DEFAULT NULL,
  `idContact` int(12) unsigned DEFAULT NULL,
  `additionalInfo` varchar(4000) DEFAULT NULL,
  `initialEndDate` date DEFAULT NULL,
  `initialWork` decimal(12,2) DEFAULT '0.00',
  `initialPricePerDayAmount` decimal(12,2) DEFAULT '0.00',
  `initialAmount` decimal(12,2) DEFAULT '0.00',
  `comment` varchar(4000) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `done` int(1) unsigned DEFAULT '0',
  `cancelled` int(1) unsigned DEFAULT '0',
  `idleDate` date DEFAULT NULL,
  `doneDate` date DEFAULT NULL,
  `handled` int(1) unsigned DEFAULT '0',
  `handledDate` date DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `sendDate` date DEFAULT NULL,
  `validityEndDate` date DEFAULT NULL,
  `idActivityType` int(12) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX quotationProject ON `${prefix}quotation` (idProject);
CREATE INDEX quotationUser ON `${prefix}quotation` (idUser);
CREATE INDEX quotationResource ON `${prefix}quotation` (idResource);
CREATE INDEX quotationStatus ON `${prefix}quotation` (idStatus);
CREATE INDEX quotationType ON `${prefix}quotation` (idQuotationType);
CREATE INDEX quotationClient ON `${prefix}quotation` (idClient);
CREATE INDEX quotationContact ON `${prefix}quotation` (idContact);

INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `idWorkflow`, `mandatoryDescription`, `mandatoryResultOnDone`, `mandatoryResourceOnHandled`, `lockHandled`, `lockDone`, `lockIdle`, `code`) VALUES 
('Quotation', 'Fixed Price', '10', '0', '1', '0', '0', '0', '0', '1', '1', '');
INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `idWorkflow`, `mandatoryDescription`, `mandatoryResultOnDone`, `mandatoryResourceOnHandled`, `lockHandled`, `lockDone`, `lockIdle`, `code`) VALUES 
('Quotation', 'Per day', '20', '0', '1', '0', '0', '0', '0', '1', '1', '');
INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `idWorkflow`, `mandatoryDescription`, `mandatoryResultOnDone`, `mandatoryResourceOnHandled`, `lockHandled`, `lockDone`, `lockIdle`, `code`) VALUES 
('Quotation', 'Per month', '30', '0', '1', '0', '0', '0', '0', '1', '1', '');
INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `idWorkflow`, `mandatoryDescription`, `mandatoryResultOnDone`, `mandatoryResourceOnHandled`, `lockHandled`, `lockDone`, `lockIdle`, `code`) VALUES 
('Quotation', 'Per year', '40', '0', '1', '0', '0', '0', '0', '1', '1', '');
INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `idWorkflow`, `mandatoryDescription`, `mandatoryResultOnDone`, `mandatoryResourceOnHandled`, `lockHandled`, `lockDone`, `lockIdle`, `code`) VALUES 
('Command', 'Per month', '30', '0', '1', '0', '0', '0', '0', '1', '1', '');
INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `idWorkflow`, `mandatoryDescription`, `mandatoryResultOnDone`, `mandatoryResourceOnHandled`, `lockHandled`, `lockDone`, `lockIdle`, `code`) VALUES 
('Command', 'Per year', '40', '0', '1', '0', '0', '0', '0', '1', '1', '');

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES 
(131,'menuQuotation', '74', 'object', '225', 'Project', 0);
INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `idle`) VALUES 
(132, 'menuQuotationType', '79', 'object', '824', 0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 131, 1),
(2, 131, 1),
(3, 131, 1),
(4, 131, 0),
(5, 131, 0),
(6, 131, 0),
(7, 131, 0);
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 132, 1),
(2, 132, 0),
(3, 132, 0),
(4, 132, 0),
(5, 132, 0),
(6, 132, 0),
(7, 132, 0);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) 
SELECT `idProfile`, 131, `idAccessProfile` FROM `${prefix}accessright` WHERE `idMenu`=97;  

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) 
SELECT `idProfile`, 132, `idAccessProfile` FROM `${prefix}accessright` WHERE `idMenu`=100;  

INSERT INTO `${prefix}originable` (`id`,`name`, `idle`) VALUES (18,'Quotation', 0);
INSERT INTO `${prefix}mailable` (`id`,`name`, `idle`) VALUES (22,'Quotation', '0');
INSERT INTO `${prefix}linkable` (`id`,`name`, `idle`, `idDefaultLinkable`) VALUES (19,'Quotation', 0, 18);
INSERT INTO `${prefix}referencable` (`name`, `idle`) VALUES ('Quotation', 0);
INSERT INTO `${prefix}indicatorable` (`id`,`name`, `idle`) VALUES (11,'Quotation', '0');
INSERT INTO `${prefix}importable` (`id`,`name`, `idle`) VALUES (28,'Quotation', '0');
INSERT INTO `${prefix}copyable` (`id`,`name`, `idle`, `sortOrder`) VALUES (14,'Quotation', '0', '37');

INSERT INTO `${prefix}indicatorableindicator` (`idIndicatorable`, `nameIndicatorable`, `idIndicator`, `idle`) VALUES 
('11', 'Quotation', '8', '0');
INSERT INTO `${prefix}indicatorableindicator` (`idIndicatorable`, `nameIndicatorable`, `idIndicator`, `idle`) VALUES 
('11', 'Quotation', '5', '0');
INSERT INTO `${prefix}indicatorableindicator` (`idIndicatorable`, `nameIndicatorable`, `idIndicator`, `idle`) VALUES 
('11', 'Quotation', '6', '0');

ALTER TABLE `${prefix}product` ADD COLUMN `designation` varchar(100) null DEFAULT NULL;

INSERT INTO `${prefix}parameter` (idUser,idProject, parameterCode, parameterValue)
VALUES (null, null, 'paramTopIconSize', '16'); 

ALTER TABLE `${prefix}filtercriteria` CHANGE `sqlValue` `sqlValue` VARCHAR(4000);

ALTER TABLE `${prefix}command` ADD COLUMN `idActivityType` int(12) unsigned DEFAULT NULL,
ADD COLUMN `idClient`  int(12) unsigned DEFAULT NULL,
ADD COLUMN `idContact`  int(12) unsigned DEFAULT NULL;

ALTER TABLE `${prefix}bill` ADD COLUMN `idActivityType` int(12) unsigned DEFAULT NULL;

INSERT INTO `${prefix}copyable` (`id`,`name`, `idle`, `sortOrder`) VALUES (15,'Bill', '0', '38');

DELETE FROM `${prefix}menu` where id=77;

ALTER TABLE `${prefix}report` ADD COLUMN `orientation` VARCHAR(1) DEFAULT 'L';

INSERT INTO `${prefix}habilitationother` (`idProfile`, `scope`, `rightAccess`) VALUES
(1, 'assignmentView', 1),
(2, 'assignmentView', 1),
(3, 'assignmentView', 1),
(4, 'assignmentView', 1),
(6, 'assignmentView', 2),
(7, 'assignmentView', 2),
(5, 'assignmentView', 2); 
INSERT INTO `${prefix}habilitationother` (`idProfile`, `scope`, `rightAccess`) VALUES
(1, 'assignmentEdit', 1),
(2, 'assignmentEdit', 2),
(3, 'assignmentEdit', 1),
(4, 'assignmentEdit', 2),
(6, 'assignmentEdit', 2),
(7, 'assignmentEdit', 2),
(5, 'assignmentEdit', 2); 
  
ALTER TABLE `${prefix}requirement` ADD COLUMN `initialDueDate` date DEFAULT NULL,
ADD COLUMN `actualDueDate` date DEFAULT NULL;  
  
INSERT INTO `${prefix}indicatorable` (`id`,`name`, `idle`) VALUES (12,'Requirement', '0');

INSERT INTO `${prefix}indicatorableindicator` (`idIndicatorable`, `nameIndicatorable`, `idIndicator`, `idle`) VALUES 
('12', 'Requirement', '3', '0');
INSERT INTO `${prefix}indicatorableindicator` (`idIndicatorable`, `nameIndicatorable`, `idIndicator`, `idle`) VALUES 
('12', 'Requirement', '4', '0');

INSERT INTO `${prefix}parameter` (idUser,idProject, parameterCode, parameterValue) VALUES 
(null, null, 'paramMailTitleApprover', '[${dbName}] message from ${sender} : You need to approve a document'),  
(null, null, 'paramMailBodyApprover', '[${dbName}] You are approver of <a href="${url}" > Document #${id}</a> : "${name}".<br/>Please access <a href="${url}" >this document</a> and follow approval process.');

UPDATE `${prefix}menu` SET idMenu=0 where id in (16,102);

UPDATE `${prefix}menu` SET sortOrder=10 where id=1;
UPDATE `${prefix}menu` SET sortOrder=20 where id=16;
UPDATE `${prefix}menu` SET sortOrder=30 where id=102;
UPDATE `${prefix}menu` SET sortOrder=40 where id=2;
UPDATE `${prefix}menu` SET sortOrder=50 where id=22;
UPDATE `${prefix}menu` SET sortOrder=60 where id=118;
UPDATE `${prefix}menu` SET sortOrder=70 where id=25;
UPDATE `${prefix}menu` SET sortOrder=80 where id=26;
UPDATE `${prefix}menu` SET sortOrder=90 where id=4;
UPDATE `${prefix}menu` SET sortOrder=100 where id=7;
UPDATE `${prefix}menu` SET sortOrder=110 where id=8;
UPDATE `${prefix}menu` SET sortOrder=120 where id=9;
UPDATE `${prefix}menu` SET sortOrder=130 where id=123;
UPDATE `${prefix}menu` SET sortOrder=140 where id=106;
UPDATE `${prefix}menu` SET sortOrder=150 where id=61;
UPDATE `${prefix}menu` SET sortOrder=160 where id=110;
UPDATE `${prefix}menu` SET sortOrder=170 where id=111;
UPDATE `${prefix}menu` SET sortOrder=180 where id=112;
UPDATE `${prefix}menu` SET sortOrder=190 where id=113;
UPDATE `${prefix}menu` SET sortOrder=200 where id=74;
UPDATE `${prefix}menu` SET sortOrder=210 where id=75;
UPDATE `${prefix}menu` SET sortOrder=220 where id=76;
UPDATE `${prefix}menu` SET sortOrder=230 where id=131;
UPDATE `${prefix}menu` SET sortOrder=240 where id=125;
UPDATE `${prefix}menu` SET sortOrder=250 where id=96;
UPDATE `${prefix}menu` SET sortOrder=260 where id=97;
UPDATE `${prefix}menu` SET sortOrder=270 where id=78;
UPDATE `${prefix}menu` SET sortOrder=280 where id=94;
UPDATE `${prefix}menu` SET sortOrder=290 where id=43;
UPDATE `${prefix}menu` SET sortOrder=300 where id=3;
UPDATE `${prefix}menu` SET sortOrder=310 where id=119;
UPDATE `${prefix}menu` SET sortOrder=320 where id=5;
UPDATE `${prefix}menu` SET sortOrder=330 where id=6;
UPDATE `${prefix}menu` SET sortOrder=340 where id=62;
UPDATE `${prefix}menu` SET sortOrder=350 where id=124;
UPDATE `${prefix}menu` SET sortOrder=360 where id=63;
UPDATE `${prefix}menu` SET sortOrder=370 where id=64;
UPDATE `${prefix}menu` SET sortOrder=380 where id=11;
UPDATE `${prefix}menu` SET sortOrder=390 where id=12;
UPDATE `${prefix}menu` SET sortOrder=400 where id=69;
UPDATE `${prefix}menu` SET sortOrder=410 where id=91;
UPDATE `${prefix}menu` SET sortOrder=420 where id=51;
UPDATE `${prefix}menu` SET sortOrder=430 where id=58;
UPDATE `${prefix}menu` SET sortOrder=440 where id=14;
UPDATE `${prefix}menu` SET sortOrder=450 where id=86;
UPDATE `${prefix}menu` SET sortOrder=460 where id=87;
UPDATE `${prefix}menu` SET sortOrder=470 where id=50;
UPDATE `${prefix}menu` SET sortOrder=480 where id=104;
UPDATE `${prefix}menu` SET sortOrder=490 where id=17;
UPDATE `${prefix}menu` SET sortOrder=500 where id=44;
UPDATE `${prefix}menu` SET sortOrder=510 where id=72;
UPDATE `${prefix}menu` SET sortOrder=520 where id=15;
UPDATE `${prefix}menu` SET sortOrder=530 where id=95;
UPDATE `${prefix}menu` SET sortOrder=540 where id=57;
UPDATE `${prefix}menu` SET sortOrder=550 where id=103;
UPDATE `${prefix}menu` SET sortOrder=560 where id=85;
UPDATE `${prefix}menu` SET sortOrder=570 where id=88;
UPDATE `${prefix}menu` SET sortOrder=580 where id=59;
UPDATE `${prefix}menu` SET sortOrder=590 where id=68;
UPDATE `${prefix}menu` SET sortOrder=600 where id=89;
UPDATE `${prefix}menu` SET sortOrder=610 where id=90;
UPDATE `${prefix}menu` SET sortOrder=620 where id=116;
UPDATE `${prefix}menu` SET sortOrder=630 where id=130;
UPDATE `${prefix}menu` SET sortOrder=700 where id=13;
UPDATE `${prefix}menu` SET sortOrder=705 where id=36;
UPDATE `${prefix}menu` SET sortOrder=715 where id=73;
UPDATE `${prefix}menu` SET sortOrder=720 where id=34;
UPDATE `${prefix}menu` SET sortOrder=725 where id=128;
UPDATE `${prefix}menu` SET sortOrder=730 where id=121;
UPDATE `${prefix}menu` SET sortOrder=735 where id=127;
UPDATE `${prefix}menu` SET sortOrder=740 where id=129;
UPDATE `${prefix}menu` SET sortOrder=745 where id=39;
UPDATE `${prefix}menu` SET sortOrder=750 where id=40;
UPDATE `${prefix}menu` SET sortOrder=755 where id=38;
UPDATE `${prefix}menu` SET sortOrder=760 where id=42;
UPDATE `${prefix}menu` SET sortOrder=765 where id=41;
UPDATE `${prefix}menu` SET sortOrder=770 where id=114;
UPDATE `${prefix}menu` SET sortOrder=775 where id=115;
UPDATE `${prefix}menu` SET sortOrder=780 where id=117;
UPDATE `${prefix}menu` SET sortOrder=800 where id=79;
UPDATE `${prefix}menu` SET sortOrder=805 where id=93;
UPDATE `${prefix}menu` SET sortOrder=810 where id=53;
UPDATE `${prefix}menu` SET sortOrder=815 where id=55;
UPDATE `${prefix}menu` SET sortOrder=820 where id=56;
UPDATE `${prefix}menu` SET sortOrder=825 where id=132;
UPDATE `${prefix}menu` SET sortOrder=830 where id=126;
UPDATE `${prefix}menu` SET sortOrder=835 where id=80;
UPDATE `${prefix}menu` SET sortOrder=840 where id=81;
UPDATE `${prefix}menu` SET sortOrder=845 where id=84;
UPDATE `${prefix}menu` SET sortOrder=850 where id=100;
UPDATE `${prefix}menu` SET sortOrder=855 where id=83;
UPDATE `${prefix}menu` SET sortOrder=860 where id=45;
UPDATE `${prefix}menu` SET sortOrder=865 where id=120;
UPDATE `${prefix}menu` SET sortOrder=870 where id=60;
UPDATE `${prefix}menu` SET sortOrder=875 where id=46;
UPDATE `${prefix}menu` SET sortOrder=880 where id=65;
UPDATE `${prefix}menu` SET sortOrder=885 where id=66;
UPDATE `${prefix}menu` SET sortOrder=890 where id=67;
UPDATE `${prefix}menu` SET sortOrder=895 where id=52;
UPDATE `${prefix}menu` SET sortOrder=900 where id=101;
UPDATE `${prefix}menu` SET sortOrder=905 where id=105;
UPDATE `${prefix}menu` SET sortOrder=910 where id=107;
UPDATE `${prefix}menu` SET sortOrder=915 where id=108;
UPDATE `${prefix}menu` SET sortOrder=920 where id=109;
UPDATE `${prefix}menu` SET sortOrder=940 where id=37;
UPDATE `${prefix}menu` SET sortOrder=945 where id=49;
UPDATE `${prefix}menu` SET sortOrder=950 where id=47;
UPDATE `${prefix}menu` SET sortOrder=955 where id=21;
UPDATE `${prefix}menu` SET sortOrder=960 where id=70;
UPDATE `${prefix}menu` SET sortOrder=965 where id=48;
UPDATE `${prefix}menu` SET sortOrder=970 where id=71;
UPDATE `${prefix}menu` SET sortOrder=975 where id=92;
UPDATE `${prefix}menu` SET sortOrder=980 where id=122;
UPDATE `${prefix}menu` SET sortOrder=985 where id=18;
UPDATE `${prefix}menu` SET sortOrder=990 where id=19;
UPDATE `${prefix}menu` SET sortOrder=995 where id=20;