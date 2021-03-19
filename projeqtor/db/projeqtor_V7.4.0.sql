-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 7.4.0                                       //
-- // Date : 2018-12-02                                     //
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}planningelement` CHANGE `wbsSortable` `wbsSortable` varchar(600);
ALTER TABLE `${prefix}planningelementbaseline` CHANGE `wbsSortable` `wbsSortable` varchar(600);
ALTER TABLE `${prefix}planningelementextension` CHANGE `wbsSortable` `wbsSortable` varchar(600);
ALTER TABLE `${prefix}project` CHANGE `sortOrder` `sortOrder` varchar(600);

CREATE TABLE `${prefix}absence` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(203, 'menuAbsence', 7, 'item', 115, Null, 0, 'Work');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 203, 1),
(2, 203, 1),
(3, 203, 1);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1,203,8);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`, `multiple`) VALUES
(81,'idRequirementType','requirementTypeList',50,0,NULL,0),
(81,'responsible','resourceList',60,0,NULL,0),
(81,'requestor','requestorList',70,0,NULL,0);

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES 
(1, 81, 1),
(2, 81, 1),
(3, 81, 1);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`,`hasCsv`) VALUES 
(88, 'reportRequirementYearlyByType', 8, 'requirementYearlyByTypeReport.php', 850, 0);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`, `multiple`) VALUES
(88, 'idProject', 'projectList', 10, 0, 'currentProject', 0), 
(88, 'idProduct', 'productList', 20, 0, NULL, 0), 
(88, 'idVersion', 'versionList', 30, 0, NULL, 0),
(88,'year','year',40,0,'currentYear',0), 
(88,'responsible','resourceList',50,0,NULL,0),
(88,'requestor','requestorList',60,0,NULL,0),
(88,'issuer','userList',70,0,NULL,0),
(88, 'idPriority', 'priorityList', 80, 0, NULL, 0);

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES 
(1, 88, 1),
(2, 88, 1),
(3, 88, 1);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`,`hasCsv`) VALUES
(89, 'reportRequirementWeeklySynthesis', 8, 'RequirementOpenedSynthesis.php', 885, 0),
(90, 'reportRequirementMonthlySynthesis', 8, 'RequirementOpenedSynthesis.php', 886, 0),
(91, 'reportRequirementYearlySynthesis', 8, 'RequirementOpenedSynthesis.php', 887, 0),
(92, 'reportRequirementGlobalSynthesis', 8, 'RequirementOpenedSynthesis.php', 888   , 0);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`, `multiple`) VALUES
(89, 'idProject', 'projectList', 10, 0, 'currentProject', 0),
(89, 'week', 'week', 20, 0, 'currentWeek', 0),
(89, 'issuer', 'userList', 30, 0, null, 0),
(89, 'responsible', 'resourceList', 40, 0, null, 0),
(89,'requestor','requestorList',50,0,NULL,0),
(90, 'idProject', 'projectList', 10, 0, 'currentProject', 0),
(90, 'month', 'month', 20, 0, 'currentMonth', 0),
(90, 'issuer', 'userList', 30, 0, null, 0),
(90, 'responsible', 'resourceList', 40, 0, null, 0),
(90,'requestor','requestorList',50,0,NULL,0),
(91, 'idProject', 'projectList', 10, 0, 'currentProject', 0),
(91, 'year', 'year', 20, 0, 'currentYear', 0),
(91, 'issuer', 'userList', 30, 0, null, 0),
(91, 'responsible', 'resourceList', 40, 0, null, 0),
(91,'requestor','requestorList',50,0,NULL,0),
(92, 'idProject', 'projectList', 10, 0, 'currentProject', 0),
(92, 'issuer', 'userList', 20, 0, null, 0),
(92, 'responsible', 'resourceList', 30, 0, null, 0),
(92,'requestor','requestorList',40,0,NULL,0);

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES 
(1, 89, 1),
(2, 89, 1),
(3, 89, 1),
(1, 90, 1),
(2, 90, 1),
(3, 90, 1),
(1, 91, 1),
(2, 91, 1),
(3, 91, 1),
(1, 92, 1),
(2, 92, 1),
(3, 92, 1);

CREATE TABLE `${prefix}workflowprofile` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idWorkflow` int(12) unsigned DEFAULT NULL,
  `idProfile` int(12) unsigned DEFAULT NULL,
  `checked` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `${prefix}restrictlist` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProfile` int(12) unsigned DEFAULT NULL,
  `showAll` int(1),
  `showStarted` int(1),
  `showDelivered` int(1),
  `showInService` int(1),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}parameter` (`idUser`, `idProject`, `parameterCode`, `parameterValue`) VALUES
(NULL, NULL, 'authorizeActivityOnDeliveredProduct', 'YES');

ALTER TABLE `${prefix}note` 
ADD `idNote` int(12),
ADD `replyLevel` int(12) DEFAULT '0';

UPDATE `${prefix}parameter` SET `parameterValue` = 'YES' WHERE `parameterCode` = 'globalNoteDiscussionMode';
 
ALTER TABLE `${prefix}planningelement`
ADD `isManualProgress` int(1) unsigned DEFAULT '0';

ALTER TABLE `${prefix}project`
ADD `fixPerimeter` int(1) unsigned DEFAULT '0';

INSERT INTO `${prefix}habilitationother` (idProfile, rightAccess, scope) VALUES
(1,1,'changeManualProgress'),
(3,1,'changeManualProgress');

INSERT INTO `${prefix}habilitationother` (idProfile, rightAccess, scope)
  SELECT idProfile, rightAccess, 'changePriority' FROM `${prefix}habilitationother` WHERE `scope`='changeValidatedData';
