
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V1.2.0                           //
-- // Date : 2010-09-01                                     //
-- ///////////////////////////////////////////////////////////
--
--
ALTER TABLE `${prefix}reportparameter` ADD `defaultValue` varchar(100);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`) VALUES
(7, 'reportPlanGantt', 2, '../tool/jsonPlanning.php?print=true', 5);

UPDATE `${prefix}reportparameter` SET defaultValue='currentWeek'
WHERE paramType='week';
UPDATE `${prefix}reportparameter` SET defaultValue='currentMonth'
WHERE paramType='month';
UPDATE `${prefix}reportparameter` SET defaultValue='currentYear'
WHERE paramType='year';

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES 
(7, 7, 'startDate', 'date', 20, 'today'),
(8, 7, 'endDate', 'date', 40, null),
(9, 7, 'format', 'periodScale', 40, 'day'),
(10, 7, 'idProject', 'projectList', 10, 'currentProject');

CREATE TABLE `${prefix}decision` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idDecisionType` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `creationDate` date DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `decisionDate` date DEFAULT NULL,
  `origin` varchar(100) DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE `${prefix}question` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idQuestionType` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `creationDate` date DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `sendMail` varchar(100) DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `replier` varchar(100) DEFAULT NULL,
  `initialDueDate` date DEFAULT NULL,
  `actualDueDate` date DEFAULT NULL,
  `result` varchar(4000) DEFAULT NULL,
  `done` int(1) unsigned DEFAULT '0',
  `doneDate` date DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `idleDate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE `${prefix}meeting` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idMeetingType` int(12) unsigned DEFAULT NULL,
  `meetingDate` date DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `attendees` varchar(4000) DEFAULT NULL,
  `idUser` int(12) unsigned DEFAULT NULL,
  `sendTo` varchar(4000) DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `result` varchar(4000) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

UPDATE `${prefix}menu` SET sortOrder=958
WHERE ID=52;
UPDATE `${prefix}menu` SET sortOrder=941
WHERE ID=41;
UPDATE `${prefix}menu` SET sortOrder=942
WHERE ID=59;

UPDATE `${prefix}menu` SET type='menu',
  name='menuReview',
  idle=0,
  sortOrder=350,
  level=null
WHERE ID=6;

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES
(62, 'menuMeeting', 6, 'object', 360, 'Project', 0),
(63, 'menuDecision', 6, 'object', 370, 'Project', 0),
(64, 'menuQuestion', 6, 'object', 380, 'Project', 0),
(65, 'menuMeetingType', 36, 'object', 954, null, 0),
(66, 'menuDecisionType', 36, 'object', 955, null, 0),
(67, 'menuQuestionType', 36, 'object', 956, null, 0),
(68, 'menuStatusMail', 36, 'object', 943, null, 0),
(69, 'menuMail', 11, 'object', 503, 'Project', 0);

INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) VALUES
(1, 62, 8),
(2, 62, 2),
(3, 62, 7),
(4, 62, 1),
(6, 62, 1),
(7, 62, 1),
(5, 62, 1),
(1, 63, 8),
(2, 63, 2),
(3, 63, 7),
(4, 63, 1),
(6, 63, 1),
(7, 63, 1),
(5, 63, 1),
(1, 64, 8),
(2, 64, 2),
(3, 64, 7),
(4, 64, 1),
(6, 64, 1),
(7, 64, 1),
(5, 64, 1),
(1, 69, 2),
(2, 69, 9),
(3, 69, 1),
(4, 69, 1),
(6, 69, 9),
(7, 69, 9),
(5, 69, 9);;

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 62, 1),
(2, 62, 1),
(3, 62, 1),
(4, 62, 1),
(6, 62, 1),
(7, 62, 1),
(5, 62, 1),
(1, 63, 1),
(2, 63, 1),
(3, 63, 1),
(4, 63, 1),
(6, 63, 1),
(7, 63, 1),
(5, 63, 1),
(1, 64, 1),
(2, 64, 1),
(3, 64, 1),
(4, 64, 1),
(6, 64, 1),
(7, 64, 1),
(5, 64, 1),
(1, 65, 1),
(2, 65, 0),
(3, 65, 0),
(4, 65, 0),
(6, 65, 0),
(7, 65, 0),
(5, 65, 0),
(1, 66, 1),
(2, 66, 0),
(3, 66, 0),
(4, 66, 0),
(6, 66, 0),
(7, 66, 0),
(5, 66, 0),
(1, 67, 1),
(2, 67, 0),
(3, 67, 0),
(4, 67, 0),
(6, 67, 0),
(7, 67, 0),
(5, 67, 0),
(1, 68, 1),
(2, 68, 0),
(3, 68, 0),
(4, 68, 0),
(6, 68, 0),
(7, 68, 0),
(5, 68, 0),
(1, 69, 1),
(2, 69, 0),
(3, 69, 1),
(4, 69, 1),
(6, 69, 0),
(7, 69, 0),
(5, 69, 0);

UPDATE `${prefix}habilitation` SET allowAccess=1
WHERE idMenu=6;
UPDATE `${prefix}habilitation` SET allowAccess=1
WHERE idMenu=11 and idProfile in (1,3,4);

INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `color`, idWorkflow) VALUES
('Meeting', 'Steering Committee', 10, 0, NULL, 7),
('Meeting', 'Progress Metting', 20, 0, NULL, 7),
('Meeting', 'Team meeting', 30, 0, NULL, 7),
('Decision', 'Functional', 10, 0, NULL, 6),
('Decision', 'Operational', 20, 0, NULL, 6),
('Decision', 'Contractual', 30, 0, NULL, 6),
('Decision', 'Strategic', 40, 0, NULL, 6),
('Question', 'Functional', 10, 0, NULL, 5),
('Question', 'Technical', 20, 0, NULL, 5);

INSERT INTO `${prefix}linkable` (`id`, `name`, `idle`) VALUES
(4, 'Meeting', 0),
(5, 'Decision', 0),
(6, 'Question', 0);

INSERT INTO `${prefix}status` (`id`, `name`, `setDoneStatus`, `setIdleStatus`, `color`, `sortOrder`, `idle`) VALUES
(13, 'prepared', 0, 0, '#d2691e', 290, 0);

INSERT INTO `${prefix}workflow` (id,name, description, idle, workflowUpdate) VALUES 
(5,'Simple with validation','Simple workflow with limited status, including validation.
Anyone can change status.',0,'[     ]'),
(6,'Validation','Short workflow with only validation or cancel possibility.
Restricted validation rights.',0,'[      ]'),
(7,'Simple with preparation','Simple workflow with limited status, including preparation.
Anyone can change status.',0,'[     ]');

DELETE FROM `${prefix}workflowstatus` WHERE idWorkflow = 5;
INSERT INTO `${prefix}workflowstatus` (idWorkflow,idStatusFrom,idStatusTo,idProfile,allowed) VALUES 
(5,1,10,1,1),
(5,1,10,2,1),
(5,1,10,3,1),
(5,1,10,4,1),
(5,1,10,6,1),
(5,1,10,7,1),
(5,1,10,5,1),
(5,1,9,1,1),
(5,1,9,2,1),
(5,1,9,3,1),
(5,1,9,4,1),
(5,1,9,6,1),
(5,1,9,7,1),
(5,1,9,5,1),
(5,8,10,1,1),
(5,8,10,2,1),
(5,8,10,3,1),
(5,8,10,4,1),
(5,8,10,6,1),
(5,8,10,7,1),
(5,8,10,5,1),
(5,8,9,1,1),
(5,8,9,2,1),
(5,8,9,3,1),
(5,8,9,4,1),
(5,8,9,6,1),
(5,8,9,7,1),
(5,8,9,5,1),
(5,10,3,1,1),
(5,10,3,2,1),
(5,10,3,3,1),
(5,10,3,4,1),
(5,10,3,6,1),
(5,10,3,7,1),
(5,10,3,5,1),
(5,10,9,1,1),
(5,10,9,2,1),
(5,10,9,3,1),
(5,10,9,4,1),
(5,10,9,6,1),
(5,10,9,7,1),
(5,10,9,5,1),
(5,3,4,1,1),
(5,3,4,2,1),
(5,3,4,3,1),
(5,3,4,4,1),
(5,3,4,6,1),
(5,3,4,7,1),
(5,3,4,5,1),
(5,3,9,1,1),
(5,3,9,2,1),
(5,3,9,3,1),
(5,3,9,4,1),
(5,3,9,6,1),
(5,3,9,7,1),
(5,3,9,5,1),
(5,4,8,1,1),
(5,4,8,2,1),
(5,4,8,3,1),
(5,4,8,4,1),
(5,4,8,6,1),
(5,4,8,7,1),
(5,4,8,5,1),
(5,4,12,1,1),
(5,4,12,2,1),
(5,4,12,3,1),
(5,4,12,4,1),
(5,4,12,6,1),
(5,4,12,7,1),
(5,4,12,5,1),
(5,12,8,1,1),
(5,12,8,2,1),
(5,12,8,3,1),
(5,12,8,4,1),
(5,12,8,6,1),
(5,12,8,7,1),
(5,12,8,5,1),
(5,12,7,1,1),
(5,12,7,2,1),
(5,12,7,3,1),
(5,12,7,4,1),
(5,12,7,6,1),
(5,12,7,7,1),
(5,12,7,5,1),
(5,7,8,1,1),
(5,7,8,2,1),
(5,7,8,3,1),
(5,7,8,4,1),
(5,7,8,6,1),
(5,7,8,7,1),
(5,7,8,5,1),
(5,9,8,1,1),
(5,9,8,2,1),
(5,9,8,3,1),
(5,9,8,4,1),
(5,9,8,6,1),
(5,9,8,7,1),
(5,9,8,5,1),
(7,1,13,1,1),
(7,1,13,2,1),
(7,1,13,3,1),
(7,1,13,4,1),
(7,1,13,6,1),
(7,1,13,7,1),
(7,1,13,5,1),
(7,1,9,1,1),
(7,1,9,2,1),
(7,1,9,3,1),
(7,1,9,4,1),
(7,1,9,6,1),
(7,1,9,7,1),
(7,1,9,5,1),
(7,13,9,1,1),
(7,13,9,2,1),
(7,13,9,3,1),
(7,13,9,4,1),
(7,13,9,6,1),
(7,13,9,7,1),
(7,13,9,5,1),
(7,13,4,1,1),
(7,13,4,2,1),
(7,13,4,3,1),
(7,13,4,4,1),
(7,13,4,6,1),
(7,13,4,7,1),
(7,13,4,5,1),
(7,4,6,1,1),
(7,4,6,2,1),
(7,4,6,3,1),
(7,4,6,4,1),
(7,4,6,6,1),
(7,4,6,7,1),
(7,4,6,5,1),
(7,6,12,1,1),
(7,6,12,2,1),
(7,6,12,3,1),
(7,6,12,6,1),
(7,6,12,6,1),
(7,6,12,7,1),
(7,6,12,5,1),
(6,1,12,1,1),
(6,1,12,3,1),
(6,1,9,1,1),
(6,1,9,3,1);

CREATE TABLE `${prefix}mailable` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}mailable` (`id`, `name`, `idle`) VALUES
(1, 'Ticket', 0),
(2, 'Activity', 0),
(3, 'Milestone', 0),
(4, 'Risk', 0),
(5, 'Action', 0),
(6, 'Issue', 0),
(7, 'Meeting', 0),
(8, 'Decision', 0),
(9, 'Question', 0);

CREATE TABLE `${prefix}statusmail` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `idMailable` int(12) unsigned DEFAULT NULL,
  `mailToUser` int(1) unsigned DEFAULT 0,
  `mailToResource` int(1) unsigned DEFAULT 0,
  `mailToProject` int(1) unsigned DEFAULT 0,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}statusmail` (`idStatus`, `idMailable`, `mailToUser`,`mailToResource`, `mailToProject`) VALUES
(1,1,1,1,1),
(2,1,1,1,0),
(11,1,1,1,0),
(8,1,1,1,1),
(10,1,1,1,0),
(3,1,1,1,0),
(4,1,1,1,0),
(5,1,1,1,0),
(6,1,1,1,0),
(12,1,1,1,0),
(7,1,1,1,0),
(9,1,1,1,0),
(8,2,1,1,0),
(10,2,0,1,0),
(3,2,1,0,0),
(4,2,1,1,0),
(12,2,1,1,0),
(7,2,1,1,0),
(9,2,1,1,0),
(8,3,1,1,0),
(10,3,0,1,0),
(3,3,1,0,0),
(4,3,1,1,0),
(12,3,1,1,0),
(7,3,1,1,0),
(9,3,1,1,0),
(8,4,1,1,0),
(10,4,0,1,0),
(3,4,1,0,0),
(4,4,1,1,0),
(12,4,1,1,0),
(7,4,1,1,0),
(9,4,1,1,0),
(8,5,1,1,0),
(10,5,0,1,0),
(3,5,1,0,0),
(4,5,1,1,0),
(12,5,1,1,0),
(7,5,1,1,0),
(9,5,1,1,0),
(8,6,1,1,0),
(10,6,0,1,0),
(3,6,1,0,0),
(4,6,1,1,0),
(12,6,1,1,0),
(7,6,1,1,0),
(9,6,1,1,0),
(13,7,0,0,1),
(6,7,0,0,1),
(12,8,0,0,1),
(9,8,0,0,1),
(1,9,1,1,1),
(2,9,1,1,0),
(11,9,1,1,0),
(8,9,1,1,1),
(10,9,1,1,0),
(3,9,1,1,0),
(4,9,1,1,0),
(5,9,1,1,0),
(6,9,1,1,0),
(12,9,1,1,0),
(7,9,1,1,0),
(9,9,1,1,0);

CREATE TABLE `${prefix}mail` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idUser`  int(12) unsigned DEFAULT NULL,
  `idProject`  int(12) unsigned DEFAULT NULL,
  `refType` int(12) unsigned DEFAULT NULL,
  `refId` int(12) unsigned DEFAULT NULL,
  `idStatus` int(12) unsigned DEFAULT NULL,
  `mailDateTime` datetime DEFAULT NULL,
  `mailTo` varchar(4000) DEFAULT NULL,
  `mailTitle` varchar(4000) DEFAULT NULL,
  `mailBody` varchar(4000) DEFAULT NULL,
  `mailStatus` varchar(100) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

ALTER TABLE `${prefix}assignment` ADD `plannedStartDate` date DEFAULT NULL,
ADD `plannedEndDate` date DEFAULT NULL;
