
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V2.6.0                                      //
-- // Date : 2012-09-06                                     //
-- ///////////////////////////////////////////////////////////
--
--

ALTER TABLE `${prefix}note` ADD COLUMN `idPrivacy` int(12) unsigned default 1,
ADD COLUMN `idTeam` int(12) unsigned default 1;

UPDATE `${prefix}note` SET idTeam = (select idTeam from ${prefix}resource USR where USR.id=idUser);

ALTER TABLE `${prefix}attachement` ADD COLUMN `idPrivacy` int(12) unsigned default 1,
ADD COLUMN `idTeam` int(12) unsigned default 1;

UPDATE `${prefix}attachement` SET idTeam = (select idTeam from ${prefix}resource USR where USR.id=idUser);

CREATE TABLE `${prefix}privacy` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}privacy` (`id`, `name`, `color`, `sortOrder`, `idle`) VALUES
(1,'public','#003399',100,0),
(2,'team','#99FF99',200,0),
(3,'private','#FF9966',300,0);

CREATE TABLE `${prefix}predefinedtext` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `scope` varchar(100) default NULL,
  `idTextable` int(12) unsigned default NULL,
  `idType` int(12) unsigned default NULL,
  `name` varchar(100) default NULL,
  `text` varchar(4000) default NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE `${prefix}textable` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) default NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}textable` (`id`,`name`,`idle`) VALUES 
(1,'Action',0),
(2,'Activity',0),
(3,'Bill',0),
(4,'Decision',0),
(5,'IndividualExpense',0),
(6,'Issue',0),
(7,'Meeting',0),
(8,'Milestone',0),
(9,'Product',0),
(10,'Project',0),
(11,'ProjectExpense',0),
(12,'Question',0),
(13,'Requirement',0),
(14,'Risk',0),
(15,'Term',0),
(16,'TestCase',0),
(17,'TestSession',0),
(18,'Ticket',0),
(19,'Version',0);

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`) VALUES 
(116,'menuPredefinedNote',36,'object',763,NULL,0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 116, 1),
(2, 116, 0),
(3, 116, 0),
(4, 116, 0),
(5, 116, 0),
(6, 116, 0),
(7, 116, 0);

ALTER TABLE `${prefix}statusmail` ADD COLUMN `idEvent` int(12) unsigned default null;

CREATE TABLE `${prefix}event` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) default NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}event` (`id`,`name`,`idle`) VALUES 
(1,'responsibleChange',0),
(2,'noteAdd',0),
(3,'attachmentAdd',0);

INSERT INTO `${prefix}parameter` (idUser, idProject, parameterCode, parameterValue) VALUES
(null,null, 'paramMailTitleStatus', '[${dbName}] ${item} #${id} moved to status \'${status}\' : "${name}"'),
(null,null, 'paramMailTitleResponsible', '[${dbName}] ${responsible} is now responsible of ${item} #${id} : "${name}"'),
(null,null, 'paramMailTitleNote', '[${dbName}] New note has been posted on ${item} #${id} : "${name}"'), 
(null,null, 'paramMailTitleAttachment', '[${dbName}] New attachment has been posted on ${item} #${id} : "${name}"'),
(null,null, 'paramMailTitleNew', '[${dbName}] ${item} #${id} has been created : "${name}"');

INSERT INTO `${prefix}parameter` (idUser, idProject, parameterCode, parameterValue) VALUES
(null,null, 'cronCheckImport', '60'),
(null,null, 'cronImportDirectory', '../files/import'),
(null,null, 'cronImportLogDestination', 'file'),
(null,null, 'cronImportMailList', '');

ALTER TABLE `${prefix}ticket` ADD COLUMN `idProduct` int(12) unsigned default null;

CREATE TABLE `${prefix}importlog` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) default NULL,
  `mode` varchar(10) default 'manual',
  `importDateTime` datetime default NULL,
  `importFile` varchar(1000),
  `importClass` varchar(100),
  `importStatus` varchar(10),
  `importTodo` int(6),
  `importDone` int(6),
  `importDoneCreated` int(6),
  `importDoneModified` int(6),
  `importDoneUnchanged` int(6),
  `importRejected` int(6),
  `importRejectedInvalid` int(6),
  `importRejectedError` int(6),
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}copyable` (`id`, `name`, `idle`, `sortOrder`) VALUES
(12, 'Requirement', 0, 35);

UPDATE `${prefix}copyable` SET sortOrder=32 WHERE id=7;

UPDATE `${prefix}project` set sortOrder=(select wbsSortable from `${prefix}planningelement` where refType='Project' and refId=`${prefix}project`.id);

ALTER TABLE `${prefix}status` ADD COLUMN `isCopyStatus` int(1) unsigned default 0;

INSERT INTO `${prefix}status` (`name`, `setDoneStatus`, `setIdleStatus`, `color`, `sortOrder`, `idle`, `isCopyStatus`) VALUES
('copied', 0, 0, '#ffffff', 999, 1, 1);

ALTER TABLE `${prefix}workelement` CHANGE plannedWork plannedWork DECIMAL(9,5) UNSIGNED,
 CHANGE realWork realWork DECIMAL(9,5) UNSIGNED,
 CHANGE leftWork leftWork DECIMAL(9,5) UNSIGNED;
ALTER TABLE `${prefix}workelement` ALTER plannedWork DROP DEFAULT,
 ALTER realWork DROP DEFAULT,
 ALTER leftWork DROP DEFAULT;
ALTER TABLE `${prefix}workelement` ALTER plannedWork SET DEFAULT 0,
 ALTER realWork SET DEFAULT 0,
 ALTER leftWork SET DEFAULT 0;


 
ALTER TABLE `${prefix}statusmail` ADD COLUMN `mailToManager` int(1) unsigned default 0;

UPDATE `${prefix}runstatus` set name='notPlanned' WHERE name='void';

INSERT INTO `${prefix}linkable` (`id`,`name`,`idle`, idDefaultLinkable) VALUES
(14,'Project',0,14);