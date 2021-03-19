
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR EXPORT                                      //
-- //-------------------------------------------------------//
-- // Version : V0.7.0                                      //
-- // Date : 2010-03-07                                     //
-- ///////////////////////////////////////////////////////////
--
-- Structure de la TABLE `${prefix}plannedwork`
--
CREATE TABLE `${prefix}plannedwork` (
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

CREATE INDEX plannedworkDay ON `${prefix}plannedwork` (day);
CREATE INDEX plannedworkWeek ON `${prefix}plannedwork` (week);
CREATE INDEX plannedworkMonth ON `${prefix}plannedwork` (month);
CREATE INDEX plannedworkYear ON `${prefix}plannedwork` (year);
CREATE INDEX plannedworkRef ON `${prefix}plannedwork` (refType, refId);
CREATE INDEX plannedworkResource ON `${prefix}plannedwork` (idResource);
CREATE INDEX plannedworkAssignment ON `${prefix}plannedwork` (idAssignment);        
  
ALTER TABLE `${prefix}assignment` ADD realStartDate date DEFAULT NULL,
ADD realEndDate date DEFAULT NULL;

CREATE TABLE `${prefix}dependency` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `predecessorId` int(12) unsigned DEFAULT NULL,
  `predecessorRefType`  varchar(100) DEFAULT NULL,
  `predecessorRefId` int(12) unsigned NOT NULL,
  `successorId` int(12) unsigned DEFAULT NULL,
  `successorRefType`  varchar(100) DEFAULT NULL,
  `successorRefId` int(12) unsigned NOT NULL,
  `dependencyType`  varchar(12),
  `dependencyDelay` NUMERIC(3,2) UNSIGNED,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE INDEX dependencyPredecessorRef ON `${prefix}dependency` (predecessorRefType, predecessorRefId);
CREATE INDEX dependencyPredecessorId ON `${prefix}dependency` (predecessorId);
CREATE INDEX dependencySuccessorRef ON `${prefix}dependency` (successorRefType, successorRefId);
CREATE INDEX dependencySuccessorId ON `${prefix}dependency` (successorId);        

--
-- Structure de la TABLE `${prefix}dependable`
--
CREATE TABLE `${prefix}dependable` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Contenu de la table `${prefix}dependable`
--
INSERT INTO `${prefix}dependable` (`id`, `name`, `idle`) VALUES (1, 'Activity', 0),
(2, 'Milestone', 0),
(3, 'Project', 0);
 

--
-- Contenu de la table `${prefix}PlanningElement`
--
ALTER TABLE `${prefix}planningelement` ADD dependencyLevel NUMERIC(3) UNSIGNED;
CREATE INDEX planningElementDependencyLevel ON `${prefix}planningelement` (dependencyLevel);
 
--
-- Contenu de la table `${prefix}PlanningElement`
--
UPDATE `${prefix}planningelement` SET priority = 500
WHERE priority is null;

-- 
ALTER TABLE `${prefix}ticket` ADD idCriticality int(12) unsigned,
ADD idUrgency int(12) unsigned,
ADD idPriority int(12) unsigned;


TRUNCATE TABLE `${prefix}criticality` ;
INSERT INTO `${prefix}criticality` (id,name,value,color,sortOrder,idle) VALUES 
(1,'Low',1,'#32cd32',10,0),
(2,'Medium',2,'#ffd700',20,0),
(3,'High',4,'#ff0000',30,0),
(4,'Critical',8,'#000000',40,0);

TRUNCATE TABLE `${prefix}likelihood` ;
INSERT INTO `${prefix}likelihood` (id,name,value,color,sortOrder,idle) VALUES 
(1,'Low (10%)',1,'#32cd32',10,0),
(2,'Medium (50%)',2,'#ffd700',20,0),
(3,'High (90%)',4,'#ff0000',30,0);

TRUNCATE TABLE `${prefix}priority` ;
INSERT INTO `${prefix}priority` (id,name,value,color,sortOrder,idle) VALUES 
(1,'Low priority',1,'#32cd32',40,0),
(2,'Medium priority',2,'#ffd700',30,0),
(3,'Hight priority',4,'#ff0000',20,0),
(4,'Critical priority (immediate action required)',8,'#000000',10,0);

TRUNCATE TABLE `${prefix}severity` ;
INSERT INTO `${prefix}severity` (id,name,value,color,sortOrder,idle) VALUES 
(1,'Low',1,'#32cd32',10,0),
(2,'Medium',2,'#ffd700',20,0),
(3,'High',4,'#ff0000',30,0);

TRUNCATE TABLE `${prefix}urgency` ;
INSERT INTO `${prefix}urgency` (id,name,value,color,sortOrder,idle) VALUES 
(1,'Blocking',4,'#ff0000',30,0),
(2,'Urgent',2,'#ffd700',20,0),
(3,'Not urgent',1,'#32cd32',10,0);


INSERT INTO `${prefix}menu` (id,name,idMenu,type,sortOrder,level,idle) VALUES 
(58,'menuImportData',11,'item',520,null,0);

INSERT INTO `${prefix}habilitation` (idProfile, idMenu, allowAccess) VALUES
(1,58,1),
(2,58,0),
(3,58,0),
(4,58,0),
(5,58,0),
(6,58,0),
(7,58,0);