-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.1.0                                       //
-- // Date : 2019-05-14                                     //
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}statusmail` CHANGE `idEvent` `idEventForMail` INT(12) UNSIGNED DEFAULT NULL;

RENAME TABLE `${prefix}event` TO `${prefix}eventformail`;

UPDATE `${prefix}columnselector` set field='nameEventForMail', attribute='idEventForMail' where attribute='idEvent';
UPDATE `${prefix}history` set colName='idEventForMail' where colName='idEvent';

CREATE TABLE `${prefix}resourcesurbooking` (
  `id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idResource` INT(12) NOT NULL,
  `capacity` decimal(10,2) DEFAULT NULL,
  `description`  mediumtext,
  `idle` int(1) unsigned DEFAULT 0,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE INDEX `resourcevariablesurbooking` ON `${prefix}resourcesurbooking` (`idResource`);

ALTER TABLE `${prefix}planningelement` ADD surbooked int(1) DEFAULT 0;

ALTER TABLE `${prefix}planningelementbaseline` ADD surbooked int(1) DEFAULT 0;

ALTER TABLE `${prefix}plannedwork` ADD surbooked int(1) DEFAULT 0;

ALTER TABLE `${prefix}plannedwork` ADD surbookedWork decimal(8,5) unsigned DEFAULT NULL;

ALTER TABLE `${prefix}plannedworkbaseline` ADD surbooked int(1) DEFAULT 0;

ALTER TABLE `${prefix}plannedworkbaseline` ADD surbookedWork decimal(8,5) unsigned DEFAULT NULL;

ALTER TABLE `${prefix}assignment` ADD surbooked int(1) DEFAULT 0;

-- /Flo
INSERT INTO `${prefix}originable` ( `name`, `idle`) VALUES ('DocumentVersion', 0);

ALTER TABLE `${prefix}message` ADD COLUMN `startDate` datetime DEFAULT NULL,
ADD COLUMN `endDate` datetime DEFAULT NULL;

-- Password
ALTER TABLE `${prefix}resource` CHANGE `crypto` `cryptotemp` VARCHAR(100) DEFAULT NULL;
ALTER TABLE `${prefix}resource` CHANGE `cryptotemp` `crypto` VARCHAR(100) DEFAULT NULL;

-- Issue with workflow 
DELETE FROM `${prefix}tempupdate` WHERE 1=1;
INSERT INTO `${prefix}tempupdate` (id) SELECT max(id) FROM `${prefix}workflowstatus` group by idWorkFlow, idStatusFrom, idStatusTo, idProfile having count(*)>1;
DELETE FROM `${prefix}workflowstatus` where id in (SELECT id FROM `${prefix}tempupdate`);
DELETE FROM `${prefix}tempupdate` WHERE 1=1;
INSERT INTO `${prefix}tempupdate` (id) SELECT max(id) FROM `${prefix}workflowstatus` group by idWorkFlow, idStatusFrom, idStatusTo, idProfile having count(*)>1;
DELETE FROM `${prefix}workflowstatus` where id in (SELECT id FROM `${prefix}tempupdate`);
DELETE FROM `${prefix}tempupdate` WHERE 1=1;
INSERT INTO `${prefix}tempupdate` (id) SELECT max(id) FROM `${prefix}workflowstatus` group by idWorkFlow, idStatusFrom, idStatusTo, idProfile having count(*)>1;
DELETE FROM `${prefix}workflowstatus` where id in (SELECT id FROM `${prefix}tempupdate`);
DELETE FROM `${prefix}tempupdate` WHERE 1=1;

CREATE UNIQUE INDEX `workflowstatusReference` ON `${prefix}workflowstatus` (idWorkFlow,idStatusFrom,idStatusTo,idProfile);

-- ADD tLaguerie and dFayolle ticket #396

INSERT INTO `${prefix}reportcategory` (`id`,`name`, `sortOrder`, `idle`) VALUES 
(20, 'reportCategoryResources', 90, 0);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `idle`, `orientation`, `hasCsv`, `hasView`, `hasPrint`, `hasPdf`, `hasToday`, `hasFavorite`, `hasWord`, `hasExcel`, `filterClass`) VALUES 
(102, 'reportResourceInputOutput', 20, 'resourceReportInputOutput.php', 1210, 0, 'L', 0, 1, 1, 1, 1, 1, 0, 0, NULL ),
(103, 'reportResourceWorkload', 20, 'resourceReportWorkload.php', 1220, 0, 'L', 0, 1, 1, 1, 1, 1, 0, 0, NULL ),
(104, 'reportResourceSeniority', 20, 'resourceReportSeniority.php', 1230, 0, 'L', 0, 1, 1, 1, 1, 1, 0, 0, NULL );

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`, `multiple`) VALUES 
(102, 'idOrganization', 'organizationList', 10, 0, 'currentOrganization', 0),
(102, 'year', 'year', 20, 0, 'currentYear', 0),
(102, 'isEmployee', 'isEmployee', 35, 0, NULL, 0), 
(102, 'idProfile', 'profileList', 40, 0, NULL, 1),
(103, 'idOrganization', 'organizationList', 10, 0, 'currentOrganization', 0),
(103 , 'year', 'year', 20, 0, 'currentYear', 0),
(103, 'isEmployee', 'isEmployee', 35, 0, NULL, 0),
(103 , 'idProfile', 'profileList', 40, 0, NULL, 1),
(104, 'idOrganization', 'organizationList', 10, 0, 'currentOrganization', 0),
(104, 'year', 'year', 20, 0, 'currentYear', 0),
(104, 'nbOfMonths', 'intMonthInput', 30, 0, 12, 0),
(104, 'isEmployee', 'isEmployee', 35, 0, NULL, 0),
(104, 'idProfile', 'profileList', 40, 0, NULL, 1);

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES 
(1, 102, 1),
(1, 103, 1),
(1, 104, 1);
       
ALTER TABLE `${prefix}resource` ADD startDate DATE DEFAULT NULL,
ADD endDate DATE DEFAULT NULL,
ADD subcontractor INT(1) DEFAULT 0,
ADD student INT(1) DEFAULT 0;

-- END tLaguerie and dFayolle ticket #396

ALTER TABLE `${prefix}filter` ADD sortOrder int(12) DEFAULT NULL;

UPDATE `${prefix}filter` set sortOrder=id;

-- Ticket #4073
ALTER TABLE `${prefix}resource` CHANGE `function` `contactFunction` VARCHAR(100) DEFAULT NULL;

-- SSO
INSERT INTO `${prefix}parameter` (`parameterCode`, `parameterValue`) VALUES 
('SAML_attributeUid' ,'uid'),
('SAML_attributeMail' ,'mail'),
('SAML_defaultProfile','5'),
('SAML_ssoCommonName','SSO');
