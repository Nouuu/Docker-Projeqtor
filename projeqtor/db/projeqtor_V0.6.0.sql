
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR EXPORT                                      //
-- //-------------------------------------------------------//
-- // Version : V0.5.0                                      //
-- // Date : 2009-10-18                                     //
-- ///////////////////////////////////////////////////////////

--
-- Structure de la TABLE `${prefix}assignment`
--
CREATE TABLE `${prefix}assignment` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idResource` int(12) unsigned NOT NULL,
  `idProject` int(12) unsigned NOT NULL,
  `refType`  varchar(100) DEFAULT NULL,
  `refId` int(12) unsigned NOT NULL,
  `rate` int(3) UNSIGNED DEFAULT 100, 
  `assignedWork` NUMERIC(6,2) UNSIGNED,
  `realWork` NUMERIC(6,2) UNSIGNED,
  `leftWork` NUMERIC(6,2) UNSIGNED,
  `plannedWork` NUMERIC(6,2) UNSIGNED,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Remove unused Task table
--
DROP TABLE `${prefix}task` ;
 
--
-- Structure de la TABLE `${prefix}planningelement`
--
ALTER TABLE `${prefix}planningelement` CHANGE `initialWork` `initialWork` NUMERIC(6,2) UNSIGNED,
 CHANGE `validatedWork` `validatedWork` NUMERIC(6,2) UNSIGNED,
 CHANGE `plannedWork` `plannedWork` NUMERIC(6,2) UNSIGNED,
 CHANGE `realWork` `realWork` NUMERIC(6,2) UNSIGNED,
 ADD `leftWork` NUMERIC(6,2) UNSIGNED,
 ADD `assignedWork` NUMERIC(6,2) UNSIGNED;

--
-- Add a type of activity : Task
--
INSERT INTO `${prefix}type` (`id`, `scope`, `name`, `sortOrder`, `idle`, `color`) VALUES
(26, 'Activity', 'Task', 01, 0, NULL);

--
-- tructure de la TABLE project
--
ALTER TABLE `${prefix}project` ADD idUser int(12) UNSIGNED;

--
-- CREATE INDEXes to forreign keys
--
CREATE INDEX accessrightProfile ON `${prefix}accessright` (`idProfile`);
CREATE INDEX  accessrightMenu ON `${prefix}accessright` (`idMenu`);

CREATE INDEX actionProject ON `${prefix}action` (`idProject`);
CREATE INDEX actionUser ON `${prefix}action` (`idUser`);
CREATE INDEX actionResource ON `${prefix}action` (`idResource`);
CREATE INDEX actionStatus ON `${prefix}action` (`idStatus`);
  
CREATE INDEX activityProject ON `${prefix}activity` (`idProject`);
CREATE INDEX activityUser ON `${prefix}activity` (`idUser`);
CREATE INDEX activityResource ON `${prefix}activity` (`idResource`);
CREATE INDEX activityStatus ON `${prefix}activity` (`idStatus`);
CREATE INDEX activityType ON `${prefix}activity` (`idActivityType`);
CREATE INDEX activityActivity ON `${prefix}activity` (`idActivity`); 
  
CREATE INDEX affectationProject ON `${prefix}affectation` (`idProject`);
CREATE INDEX affectationResource ON `${prefix}affectation` (`idResource`);   
  
CREATE INDEX assignmentProject ON `${prefix}assignment` (`idProject`);
CREATE INDEX assignmentResource ON `${prefix}assignment` (`idResource`);
CREATE INDEX assignmentRef ON `${prefix}assignment` (`refType`, `refId`);

CREATE INDEX attachementUser ON `${prefix}attachement` (`idUser`);
CREATE INDEX attachementRef ON `${prefix}attachement` (`refType`, `refId`);        

CREATE INDEX habilitationProfile ON `${prefix}habilitation` (`idProfile`);
CREATE INDEX habilitationMenu ON `${prefix}habilitation` (`idMenu`);  
  
CREATE INDEX historyUser ON `${prefix}history` (`idUser`);
CREATE INDEX historyRef ON `${prefix}history` (`refType`, `refId`); 

CREATE INDEX issueProject ON `${prefix}issue` (`idProject`);
CREATE INDEX issueUser ON `${prefix}issue` (`idUser`);
CREATE INDEX issueResource ON `${prefix}issue` (`idResource`);
CREATE INDEX issueStatus ON `${prefix}issue` (`idStatus`);
CREATE INDEX issueType ON `${prefix}issue` (`idIssueType`);  
  
CREATE INDEX linkRef1 ON `${prefix}link` (`ref1Type`, `ref1Id`);
CREATE INDEX linkRef2 ON `${prefix}link` (`ref2Type`, `ref2Id`);
  
CREATE INDEX menuMenu ON `${prefix}menu` (`idMenu`);

CREATE INDEX messageProject ON `${prefix}message` (`idProject`);
CREATE INDEX messageUser ON `${prefix}message` (`idUser`);
CREATE INDEX messageType ON `${prefix}message` (`idMessageType`);
CREATE INDEX messageProfile ON `${prefix}message` (`idProfile`);           
  
CREATE INDEX milestoneProject ON `${prefix}milestone` (`idProject`);
CREATE INDEX milestoneUser ON `${prefix}milestone` (`idUser`);
CREATE INDEX milestoneResource ON `${prefix}milestone` (`idResource`);
CREATE INDEX milestoneStatus ON `${prefix}milestone` (`idStatus`);
CREATE INDEX milestoneType ON `${prefix}milestone` (`idMilestoneType`);
CREATE INDEX milestoneActivity ON `${prefix}milestone` (`idActivity`);
  
CREATE INDEX noteUser ON `${prefix}note` (`idUser`);
CREATE INDEX noteRef ON `${prefix}note` (`refType`, `refId`);        

CREATE INDEX parameterProject ON `${prefix}parameter` (`idProject`);
CREATE INDEX parameterUser ON `${prefix}parameter` (`idUser`);
  
CREATE INDEX planningelementProject ON `${prefix}planningelement` (`idProject`);
CREATE INDEX planningelementWbsSortable ON `${prefix}planningelement` (`wbsSortable`(255));  

CREATE INDEX projectProject ON `${prefix}project` (`idProject`);
CREATE INDEX projectClient ON `${prefix}project` (`idClient`);

CREATE INDEX riskProject ON `${prefix}risk` (`idProject`);
CREATE INDEX riskUser ON `${prefix}risk` (`idUser`);
CREATE INDEX riskResource ON `${prefix}risk` (`idResource`);
CREATE INDEX riskStatus ON `${prefix}risk` (`idStatus`);
CREATE INDEX riskType ON `${prefix}risk` (`idRiskType`);  

CREATE INDEX ticketProject ON `${prefix}ticket` (`idProject`);
CREATE INDEX ticketUser ON `${prefix}ticket` (`idUser`);
CREATE INDEX ticketResource ON `${prefix}ticket` (`idResource`);
CREATE INDEX ticketStatus ON `${prefix}ticket` (`idStatus`);
CREATE INDEX ticketType ON `${prefix}ticket` (`idTicketType`);
CREATE INDEX ticketActivity ON `${prefix}ticket` (`idActivity`); 
  
--
-- Structure de la TABLE `${prefix}work`
--
CREATE TABLE `${prefix}work` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idResource` int(12) unsigned NOT NULL,
  `idProject` int(12) unsigned NOT NULL,
  `refType`  varchar(100) DEFAULT NULL,
  `refId` int(12) unsigned NOT NULL,
  `idAssignment` int(12) unsigned default NULL,
  `work` NUMERIC(3,2) UNSIGNED,
  `workDate` date DEFAULT NULL,
  `day`  varchar(8),
  `week` varchar(6),
  `month` varchar(6),
  `year` varchar(4),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE INDEX workDay ON `${prefix}work` (day);
CREATE INDEX workWeek ON `${prefix}work` (week);
CREATE INDEX workMonth ON `${prefix}work` (month);
CREATE INDEX workYear ON `${prefix}work` (year);
CREATE INDEX workRef ON `${prefix}work` (refType, refId);
CREATE INDEX workResource ON `${prefix}work` (idResource);
CREATE INDEX workAssignment ON `${prefix}work` (idAssignment);        

UPDATE `${prefix}menu` SET idle=0 where id=8;
UPDATE `${prefix}habilitation` SET allowAccess=1 where idMenu=8;

  