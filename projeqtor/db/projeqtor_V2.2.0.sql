
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V2.2.0                                      //
-- // Date : 2012-04-12                                     //
-- ///////////////////////////////////////////////////////////
--
--

INSERT INTO `${prefix}linkable` (`id`,`name`,`idle`) VALUES
(7,'Ticket',0),
(8,'Activity',0);
INSERT INTO `${prefix}linkable` (`id`,`name`,`idle`) VALUES
(9,'Milestone',0);
INSERT INTO `${prefix}linkable` (`id`,`name`,`idle`) VALUES
(10,'Document',0);

ALTER TABLE `${prefix}link` ADD COLUMN `comment` varchar(4000), 
ADD COLUMN `creationDate` datetime, 
ADD COLUMN `idUser` int(12) unsigned default null;

ALTER TABLE `${prefix}attachement` ADD COLUMN `link` varchar(400),
ADD COLUMN `type` varchar(10) default 'file';

INSERT INTO `${prefix}indicator` (`id`, `code`, `type`, `name`, `sortOrder`, `idle`) VALUES
(15, 'RWOVW', 'percent', 'RealWorkOverValidatedWork', 250, 0),
(16, 'RWOAW', 'percent', 'RealWorkOverAssignedWork', 260, 0);
  
INSERT INTO `${prefix}indicatorableindicator` (`idIndicator`, `idIndicatorable`, `nameIndicatorable`, `idle`) VALUES
(15, 2, 'Activity',0),
(15, 8, 'Project',0),
(16, 2, 'Activity',0),
(16, 8, 'Project',0);

INSERT INTO `${prefix}parameter` (idUser, idProject, parameterCode, parameterValue) VALUES
(null, null, 'maxProjectsToDisplay','25');

ALTER TABLE `${prefix}workelement` ADD COLUMN `ongoing` int(1) unsigned default 0,
ADD COLUMN `ongoingStartDateTime` datetime default null,
ADD COLUMN `idUser` int(12) unsigned default null,
ADD COLUMN `idActivity` int(12) unsigned default null;

CREATE INDEX workelementUser ON `${prefix}workelement` (idUser);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `idle`)
VALUES (39,'reportVersionDetail',4,'versionDetail.php',450,0);

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(101,39,'idProject','projectList',10,0,'currentProject'),
(102,39,'idVersion','versionList',20,0,NULL),
(103,39,'responsible','resourceList',30,0,NULL);

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES
(1,39,1),
(2,39,1),
(3,39,1),
(4,39,0),
(5,39,0),
(6,39,0),
(7,39,0);

UPDATE `${prefix}menu` set type='item' where name='menuCalendar';

ALTER TABLE `${prefix}meeting` ADD COLUMN meetingStartTime time,
ADD COLUMN meetingEndTime time,
ADD COLUMN location varchar(100);

CREATE TABLE `${prefix}approver` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `refType` varchar(100) DEFAULT NULL,
  `refId` int(12) unsigned DEFAULT NULL,
  `idAffectable` int(12) unsigned DEFAULT NULL,
  `approved` int(1) unsigned default '0',
  `approvedDate` datetime default NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX approverRef ON `${prefix}approver` (refType, refId);
CREATE INDEX approverAffectable ON `${prefix}approver` (idAffectable);

ALTER TABLE `${prefix}documentversion` ADD COLUMN `approved` int(1) unsigned default '0';
