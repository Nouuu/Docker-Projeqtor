-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 6.1.0                                       //
-- // Date : 2016-12-08                                     //
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}assignment` ADD `isNotImputable` int(1) unsigned default '0';

UPDATE `${prefix}menu` SET name='menuIncomes' WHERE name='menuIncomings';

-- ===================================
-- PAPJUL ADDITION FOR REPORTS (START)

ALTER TABLE `${prefix}reportparameter` ADD COLUMN `multiple` int(1) unsigned DEFAULT '0';

ALTER TABLE `${prefix}report` ADD COLUMN `hasCsv` int(1) unsigned DEFAULT '0';

UPDATE `${prefix}report` SET hasCsv = 1 WHERE `id` = 49;
UPDATE `${prefix}report` SET hasCsv = 1 WHERE `id` = 7;

-- PAPJUL ADDITION FOR REPORTS (END)
-- =================================

-- ================================
-- PAPJUL ADDITION FOR JOBS (START)

CREATE TABLE `${prefix}job` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idJoblistDefinition` int(12) unsigned DEFAULT NULL,
  `idJobDefinition` int(12) unsigned DEFAULT NULL,
  `value` int(1) unsigned DEFAULT '0',
  `idUser` int(12) unsigned DEFAULT NULL,
  `refType` varchar(100) DEFAULT NULL,
  `refId` int(12) unsigned DEFAULT NULL,
  `creationDate` datetime DEFAULT NULL,
  `checkTime` datetime DEFAULT NULL,
  `comment` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX `jobJobDefinition` ON `${prefix}job` (`idJobDefinition`);
CREATE INDEX `jobJoblistDefinition` ON `${prefix}job` (`idJoblistDefinition`);
CREATE INDEX `jobReference` ON `${prefix}job` (`refType`,`refId`);

CREATE TABLE `${prefix}jobdefinition` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idJoblistDefinition` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `title` varchar(1000) DEFAULT NULL,
  `sortOrder` int(3) DEFAULT '0',
  `daysBeforeWarning` int(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX `jobdefinitionJoblistDefinition` ON `${prefix}jobdefinition` (`idJoblistDefinition`);

CREATE TABLE `${prefix}joblistdefinition` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `idChecklistable` int(12) unsigned DEFAULT NULL,
  `nameChecklistable` varchar(100) DEFAULT NULL,
  `idType` int(12) unsigned DEFAULT NULL,
  `lineCount` int(3) DEFAULT '0',
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX `joblistdefinitionChecklistable` ON `${prefix}joblistdefinition` (`idChecklistable`);
CREATE INDEX `joblistdefinitionNameChecklistable` ON `${prefix}joblistdefinition` (`nameChecklistable`);
CREATE INDEX `joblistdefinitionType` ON `${prefix}joblistdefinition` (`idType`);

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES 
(162, 'menuJoblistDefinition', 88, 'object', 640, 'ReadWriteEnvironment', 0, 'Automation ');
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES 
(1,162,1);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `hasCsv`, `sortOrder`, `idle`, `orientation`) VALUES 
(63, 'reportMacroJoblist', 4, 'joblist.php', 1, 460, 0, 'L');
INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`, `multiple`) VALUES 
(63, 'idActivity', 'activityList', 20, 0, NULL, 0),
(63, 'idProject', 'projectList', 10, 0, 'currentProject', 0);
INSERT INTO `${prefix}habilitationreport` (`idProfile`,`idReport`,`allowAccess`) VALUES
(1,63,1),
(2,63,1),
(3,63,1);
INSERT INTO `${prefix}habilitationother` (`idProfile`, `scope`, `rightAccess`) VALUES
(1, 'joblist', 1),
(2, 'joblist', 1),
(3, 'joblist', 1),
(4, 'joblist', 1),
(6, 'joblist', 2),
(7, 'joblist', 2),
(5, 'joblist', 2); 

-- PAPJUL ADDITION FOR JOBS (END)
-- ==============================

CREATE TABLE `${prefix}kpidefinition` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `description` mediumtext,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX `kpidefinitionCode` ON `${prefix}kpidefinition` (`code`);
INSERT INTO `${prefix}kpidefinition` (`id`, `name`, `code`, `idle`,`description`) VALUES 
(1, 'project duration KPI', 'duration', 0, '
<b>KPI for not ended project = [project planned duration] / [project validated duration]</b><br/>
<b>KPI for ended project = [project real duration] / [project validated duration]</b><br/>
<br/>
KPI < 1 => project is shorter than expected<br/>
KPI = 1 => project is exactly as long as expected<br/>
KPI > 1 => project is longer than expected<br/>
<br/>
This indicator is consolidated amongst projects (for organization) without weighting.'),
(2, 'project workload KPI', 'workload', 0, '
<b>KPI for project = ( [project real work] + [project left work] / [project validated work]</b><br/>
<br/>
KPI < 1 => project workload is less than expected<br/>
KPI = 1 => project workload is conform to expected<br/>
KPI > 1 => project workload is more than expected<br/>
<br/>
This indicator is consolidated amongst projects (for organization) with weighting on [project validated work].'),
(3, 'project terms KPI', 'term', 0, '
<b>KPI for project = [sum of real amount for all project terms] / [sum of validated amount for all project terms]</b><br/>
<br/>
This indicator has no intrinsic meaning but has some compared to project progress.<br/>
So for this indicator, thresholds will not be compared to KPI value directly but to : [project progress] - [KPI value] <br/>(that should then be as small as possible).<br/>
<br/>
This indicator is not consolidated amongst projects (for organization).'),
(4, 'project deliverables quality KPI', 'deliverable', 0, '
<b>KPI for deliverable = [Estimated quality value of deliverable] / [Nominal (max) quality value for deliverables]</b><br/>
Quality value is defined in the deliverable status list, that will be selected on the deliverable.<br/>
Nominal quality value is the max of the values defined in the deliverable status list.<br/>
<b>KPI consolidated on project = Sum of ([Estimated quality value of deliverables]*[Weighting of deliverable]) / Sum([Nominal (max) quality value of deliverables]*[Weighting of deliverable])</b><br/>
Weigting value of deliverable is defined in the deliverable weighting list, that will be selected on the deliverable.<br/>
Consolidated value may not be calculated if all deliverables have zero weight.<br/>
Unitary value of KPI for single deliverable is not stored in KPI history. Only consolidated value for project is stored is KPI history.<br/>
<br/>
This indicator is consolidated amongst projects (for organization) with weighting on global weight of deliverables on each project.'),
(5, 'project incomings quality KPI', 'incoming', 0, '
<b>KPI for incoming = ( [Estimated Quality value of incoming] / [Nominal (max) Quality value for incomings]</b><br/>
Quality value is defined in the incoming Status list, that will be selected on the incoming.<br/>
Nominal Quality value is the max of the values defined in the incoming Status list.<br/>
<b>KPI consolidated on project = ( Sum of ([Estimated Quality value of incomings]*[Weighting of incoming]) / Sum([Nominal (max) Quality value of incomings]*[Weighting of incoming])</b><br/>
Weigting value of incoming is defined in the incoming Weighting list, that will be selected on the incoming.<br/>
Consolidated value may not be calculated if all incomings have zero weight.<br/>
Unitary value of KPI for single incoming is not stored in KPI history. Only consolidated value for project is stored is KPI history.<br/>
<br/>
This indicator is consolidated amongst projects (for organization) with weighting on global weight of incomings on each project.');  

CREATE TABLE `${prefix}kpithreshold` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `idKpiDefinition` int(12) unsigned DEFAULT NULL,
  `thresholdValue` decimal(5,2) DEFAULT NULL,
  `thresholdColor` varchar(7) DEFAULT '#FFFFFF',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX `kpithresholdKpiDefinition` ON `${prefix}kpithreshold` (`idKpiDefinition`);
INSERT INTO `${prefix}kpithreshold` (`id`, `name`, `idKpiDefinition`, `thresholdValue`, `thresholdColor`) VALUES 
(1, 'good', 1, 0, '#98fb98'),
(2, 'acceptable', 1, 1.2, '#f4a460'),
(3, 'not acceptable', 1, 1.5, '#f08080'),
(4, 'good', 2, 0, '#98fb98'),
(5, 'acceptable', 2, 1.2, '#f4a460'),
(6, 'not acceptable', 2, 1.5, '#f08080'),
(7, 'sufficient', 3, 0.0, '#98fb98'),
(8, 'partially sufficient', 3, 0.4, '#f4a460'),
(9, 'not sufficient', 3, 0.7, '#f08080'),
(10, 'not good', 4, 0, '#f08080'),
(11, 'good', 4, 0.66, '#98fb98'),
(12, 'not good', 5, 0, '#f08080'),
(13, 'good', 5, 0.66, '#98fb98');  

CREATE TABLE `${prefix}kpivalue` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idKpiDefinition` int(12) unsigned DEFAULT NULL,
  `refType` varchar(100) DEFAULT NULL,
  `refId` int(12) unsigned DEFAULT NULL,
  `kpiType` varchar(1) DEFAULT NULL,
  `kpiDate` date DEFAULT NULL,
  `day` varchar(8) DEFAULT NULL,
  `week` varchar(6) DEFAULT NULL,
  `month` varchar(6) DEFAULT NULL,
  `year` varchar(4) DEFAULT NULL,
  `kpiValue` decimal(5,2) DEFAULT NULL,
  `weight` decimal(14,5) DEFAULT NULL,
  `refDone` int(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX `kpivalueKpiDefinition` ON `${prefix}kpivalue` (`idKpiDefinition`);
CREATE INDEX `kpivalueReference` ON `${prefix}kpivalue` (`refType`, `refId`);

CREATE TABLE `${prefix}kpihistory` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idKpiDefinition` int(12) unsigned DEFAULT NULL,
  `refType` varchar(100) DEFAULT NULL,
  `refId` int(12) unsigned DEFAULT NULL,
  `kpiType` varchar(1) DEFAULT NULL,
  `kpiDate` date DEFAULT NULL,
  `day` varchar(8) DEFAULT NULL,
  `week` varchar(6) DEFAULT NULL,
  `month` varchar(6) DEFAULT NULL,
  `year` varchar(4) DEFAULT NULL,
  `kpiValue` decimal(5,2) DEFAULT NULL,
  `weight` decimal(14,5) DEFAULT NULL,
  `refDone` int(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX `kpihistoryKpiDefinition` ON `${prefix}kpihistory` (`idKpiDefinition`);
CREATE INDEX `kpihistoryReference` ON `${prefix}kpihistory` (`refType`, `refId`);
CREATE INDEX `kpihistoryDate` ON `${prefix}kpihistory` (`kpiDate`);

CREATE TABLE `${prefix}deliverable` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `externalReference` varchar(100) DEFAULT NULL,
  `scope` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `idDeliverableType` int(12) unsigned DEFAULT NULL,
  `creationDateTime` datetime DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `result` mediumtext DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `plannedDate` date DEFAULT NULL,
  `realDate` date DEFAULT NULL,
  `validationDate` date DEFAULT NULL,
  `impactWork` decimal(5) DEFAULT NULL,
  `impactDuration` int(5) DEFAULT NULL,
  `impactCost` decimal(9) DEFAULT NULL,
  `idDeliverableWeight` int(12) unsigned DEFAULT NULL,
  `idDeliverableStatus` int(12) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE INDEX `deliverableType` ON `${prefix}deliverable` (`idDeliverableType`);
CREATE INDEX `deliverableStatusIdx` ON `${prefix}deliverable` (`idDeliverableStatus`);
CREATE INDEX `deliverableProject` ON `${prefix}deliverable` (`idProject`);

CREATE TABLE `${prefix}deliverableweight` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `scope` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `value` int(3) unsigned NOT NULL,
  `color` varchar(7) DEFAULT '#FFFFFF',
  `sortOrder` int(3) DEFAULT 0, 
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `${prefix}deliverableweight` (`id`, `scope`, `name`, `value`, `sortOrder`, `color`, `idle`) VALUES 
(1, 'Deliverable', 'low', 0, 10, '#d3d3d3', '0'),
(2, 'Deliverable', 'medium', 0, 20, '#d3d3d3', '0'),
(3, 'Deliverable', 'high', 1, 30, '#ffc0cb', '0'),
(4, 'Incoming', 'low', 0, 10, '#d3d3d3', '0'),
(5, 'Incoming', 'medium', 0, 20, '#d3d3d3', '0'),
(6, 'Incoming', 'high', 1, 30, '#ffc0cb', '0');

CREATE TABLE `${prefix}deliverablestatus` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `scope` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `value` int(3) DEFAULT 0,
  `color` varchar(7) DEFAULT '#FFFFFF',
  `sortOrder` int(3) DEFAULT 0, 
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `${prefix}deliverablestatus` (`id`, `scope`, `name`, `value`, `sortOrder`, `color`, `idle`) VALUES 
(1, 'Deliverable', 'not done', 0, 10, '#ff0000', '0'),
(2, 'Deliverable', 'delivery refused (major reservations)', 1, 20, '#ff8c00', '0'),
(3, 'Deliverable', 'accepted with minor reservations', 2, 30, '#ffff00', '0'),
(4, 'Deliverable', 'accepted without reservations', 3, 40, '#7fff00', '0'),
(5, 'Incoming', 'not provided', 0, 10, '#ff0000', '0'),
(6, 'Incoming', 'not conform', 1, 20, '#ff8c00', '0'),
(7, 'Incoming', 'accepted with minor reservations', 2, 30, '#ffff00', '0'),
(8, 'Incoming', 'accepted without reservations', 3, 40, '#7fff00', '0');

INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `code`) VALUES 
('Deliverable', 'document', '10',0,'DOC'),
('Deliverable', 'software', '20',0,'SOFT'),
('Deliverable', 'hardware', '30',0,'HARD'),
('Incoming', 'document', '10',0,'DOC'),
('Incoming', 'software', '20',0,'SOFT'),
('Incoming', 'hardware', '30',0,'HARD');

INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(163,'menuDeliverableWeight', 36, 'object', 792, 'ReadWriteList', 0, 'ListOfValues'),
(164,'menuDeliverableStatus', 36, 'object', 794, 'ReadWriteList', 0, 'ListOfValues'),
(165,'menuDeliverableType', 79, 'object', 938, 'ReadWriteType', 0, 'Type'),
(166,'menuIncomingType', 79, 'object', 936, 'ReadWriteType', 0, 'Type'),
(167,'menuDeliverable', 6, 'object', 374, 'Project', 0, 'Work Meeting'),
(168,'menuIncoming', 6, 'object', 372, 'Project', 0, 'Work Meeting'),
(169,'menuKpiDefinition', 88, 'object', 615, 'ReadWriteEnvironment', 0, 'Automation'),
(171,'menuIncomingWeight', 36, 'object', 791, 'ReadWriteList', 0, 'ListOfValues'),
(172,'menuIncomingStatus', 36, 'object', 793, 'ReadWriteList', 0, 'ListOfValues');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES 
(1, 163, 1),
(1, 164, 1),
(1, 165, 1),
(1, 166, 1),
(1, 167, 1),
(2, 167, 1),
(3, 167, 1),
(1, 168, 1),
(2, 168, 2),
(3, 168, 3),
(1, 169, 1),
(1, 171, 1),
(1, 172, 1);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) 
SELECT `idProfile`, 167, `idAccessProfile` FROM `${prefix}accessright` WHERE `idMenu`=16;
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) 
SELECT `idProfile`, 168, `idAccessProfile` FROM `${prefix}accessright` WHERE `idMenu`=16;   

CREATE TABLE `${prefix}category` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(170,'menuCategory', 36, 'object', 791, 'ReadWriteType', 0, ' ListOfValues');
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES 
(1,170,1);
INSERT INTO `${prefix}category` (`id`, `name`, `idle`) VALUES 
(1, 'Build', 0),
(2, 'Run', 0);

ALTER TABLE `${prefix}project` ADD COLUMN `idCategory` int(12) unsigned DEFAULT NULL;

ALTER TABLE `${prefix}type` ADD COLUMN `idCategory` int(12) unsigned DEFAULT NULL;

INSERT INTO `${prefix}linkable` (`id`,`name`, `idle`, `idDefaultLinkable`) VALUES (21,'Deliverable', 0, 9);
INSERT INTO `${prefix}linkable` (`id`,`name`, `idle`, `idDefaultLinkable`) VALUES (22,'Incoming', 0, 9);
UPDATE `${prefix}linkable` set `idDefaultLinkable`=21 WHERE id=9;

DELETE FROM `${prefix}type` where scope='Invoice';

INSERT INTO `${prefix}reportcategory` (`id`, `name`, `sortOrder`) VALUES 
(11,'reportCategoryKpi',27); 
UPDATE `${prefix}report` set idReportCategory=9, sortOrder=sortOrder+400 WHERE idReportCategory=5;
DELETE FROM `${prefix}reportcategory` WHERE `id`=5;

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `orientation`) VALUES 
(64, 'reportKpiDurationProject', 11, 'kpiDuration.php?scope=Project', 1110, 'P'),
(65, 'reportKpiDurationOrganization', 11, 'kpiDuration.php?scope=Organization', 1115, 'P'),
(66, 'reportKpiWorkloadProject', 11, 'kpiWorkload.php?scope=Project', 1120, 'P'),
(67, 'reportKpiWorkloadOrganization', 11, 'kpiWorkload.php?scope=Organization', 1125, 'P'),
(68, 'reportKpiTerm', 11, 'kpiTerm.php', 1150, 'P'),
(69, 'reportKpiDeliverableProject', 11, 'kpiDeliverable.php?class=Deliverable&scope=Project', 1130, 'P'),
(70, 'reportKpiDeliverableOrganization', 11, 'kpiDeliverable.php?scope=Organization', 1135, 'P'),
(71, 'reportKpiIncomingProject', 11, 'kpiDeliverable.php?class=Incoming&scope=Project', 1140, 'P'),
(72, 'reportKpiIncomingOrganization', 11, 'kpiDeliverable.php?class=Incoming&scope=Organization', 1145, 'P');

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES 
(64, 'idProject', 'projectList', 10, 'currentProject'),
(64, 'month', 'month', 20, null),
(64, 'showThreshold', 'boolean', 30, true),
(65, 'idOrganization', 'organizationList', 10, 'currentOrganization'),
(65, 'idProjectType', 'projectTypeList', 20, null),
(65, 'month', 'month', 30, 'currentYear'),
(65, 'showThreshold', 'boolean', 40, true),
(65, 'onlyFinished', 'boolean', 50, true),
(66, 'idProject', 'projectList', 10, 'currentProject'),
(66, 'month', 'month', 20, null),
(66, 'showThreshold', 'boolean', 30, true),
(67, 'idOrganization', 'organizationList', 10, 'currentOrganization'),
(67, 'idProjectType', 'projectTypeList', 20, null),
(67, 'idCategory', 'categoryList', 30, null),
(67, 'month', 'month', 40, 'currentYear'),
(67, 'showThreshold', 'boolean', 50, true),
(67, 'onlyFinished', 'boolean', 60, true),
(68, 'idProject', 'projectList', 10, 'currentProject'),
(68, 'month', 'month', 20, null),
(69, 'idProject', 'projectList', 10, 'currentProject'),
(69, 'month', 'month', 20, null),
(69, 'showThreshold', 'boolean', 30, true),
(70, 'idOrganization', 'organizationList', 10, 'currentOrganization'),
(70, 'idProjectType', 'projectTypeList', 20, null),
(70, 'month', 'month', 30, 'currentYear'),
(70, 'showThreshold', 'boolean', 40, true),
(70, 'onlyFinished', 'boolean', 50, true),
(71, 'idProject', 'projectList', 10, 'currentProject'),
(71, 'month', 'month', 20, null),
(71, 'showThreshold', 'boolean', 30, true),
(72, 'idOrganization', 'organizationList', 10, 'currentOrganization'),
(72, 'idProjectType', 'projectTypeList', 20, null),
(72, 'month', 'month', 30, 'currentYear'),
(72, 'showThreshold', 'boolean', 40, true),
(72, 'onlyFinished', 'boolean', 50, true);

INSERT INTO `${prefix}habilitationreport` (`idProfile`,`idReport`,`allowAccess`) VALUES
(1,64,1),
(2,64,1),
(3,64,1),
(1,65,1),
(2,65,1),
(1,66,1),
(2,66,1),
(3,66,1),
(1,67,1),
(2,67,1),
(1,68,1),
(2,68,1),
(3,68,1),
(1,69,1),
(2,69,1),
(3,69,1),
(1,70,1),
(2,70,1),
(1,71,1),
(2,71,1),
(3,71,1),
(1,72,1),
(2,72,1);

ALTER TABLE `${prefix}type` ADD `lockNoLeftOnDone` int(1) unsigned default '0';

INSERT INTO `${prefix}habilitationother` (idProfile,scope,rightAccess) VALUES 
(1,'changeValidatedData','1'),
(2,'changeValidatedData','1'),
(3,'changeValidatedData','1'),
(4,'changeValidatedData','2'),
(6,'changeValidatedData','2'),
(7,'changeValidatedData','2'),
(5,'changeValidatedData','2');

UPDATE `${prefix}menu` SET sortOrder=395 WHERE id=11;
DELETE FROM `${prefix}menu` WHERE id=12;

INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(173,'menuConfiguration', 0, 'menu', 380, null, 0, 'Work Meeting EnvironmentalParameter');

UPDATE `${prefix}menu` SET `menuClass`='Work Meeting EnvironmentalParameter', sortOrder=382, idMenu=173 WHERE id=86;
UPDATE `${prefix}menu` SET `menuClass`='Work Meeting EnvironmentalParameter', sortOrder=384, idMenu=173 WHERE id=87;
UPDATE `${prefix}menu` SET `menuClass`='Work Meeting EnvironmentalParameter', sortOrder=386, idMenu=173 WHERE id=141;
UPDATE `${prefix}menu` SET `menuClass`='Work Meeting EnvironmentalParameter', sortOrder=388, idMenu=173 WHERE id=142;

INSERT INTO `${prefix}importable` ( `name`, `idle`) VALUES 
('User', '0'),
('DocumentVersion', '0');