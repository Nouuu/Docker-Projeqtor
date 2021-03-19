
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR EXPORT                                      //
-- //-------------------------------------------------------//
-- // Version : V0.8.0                                      //
-- // Date : 2010-04-22                                     //
-- ///////////////////////////////////////////////////////////
--
-- Structure de la TABLE `${prefix}plannedwork`
--

ALTER TABLE `${prefix}planningelement` ADD idPlanningMode int(12) DEFAULT NULL;

CREATE TABLE `${prefix}planningmode` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `code` varchar(5) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `mandatoryStartDate` int(1) unsigned DEFAULT '0',
  `mandatoryEndDate` int(1) unsigned DEFAULT '0',
  `applyTo` varchar(20) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `${prefix}planningmode` (`id`, `applyTo`, `name`, `code`, `sortOrder`, `idle`, `mandatoryStartDate`, `mandatoryEndDate`) VALUES
(1, 'Activity', 'PlanningModeASAP', 'ASAP', 100, 0 , 0, 0),
(2, 'Activity', 'PlanningModeREGUL', 'REGUL', 200, 0, 1 , 1 ),
(3, 'Activity', 'PlanningModeFULL', 'FULL', 300, 0, 1, 1),
(4, 'Activity', 'PlanningModeALAP', 'ALAP', 400, 0, 0, 1),
(5, 'Milestone', 'PlanningModeFLOAT', 'FLOAT', 100, 0 , 0, 0),
(6, 'Milestone', 'PlanningModeFIXED', 'FIXED', 200, 0 , 0, 1);

ALTER TABLE `${prefix}resource` ADD capacity NUMERIC(5,2) UNSIGNED DEFAULT 1;

ALTER TABLE `${prefix}ticket` ADD done int(1) unsigned DEFAULT '0',
  ADD `doneDateTime` datetime DEFAULT NULL;
ALTER TABLE `${prefix}ticket` CHANGE `closureDateTime` `idleDateTime` datetime DEFAULT NULL;
  
ALTER TABLE `${prefix}activity` ADD done int(1) unsigned DEFAULT '0',
  ADD `idleDate` date DEFAULT NULL,
  ADD `doneDate` date DEFAULT NULL;

ALTER TABLE `${prefix}milestone` ADD done int(1) unsigned DEFAULT '0',
  ADD `idleDate` date DEFAULT NULL,
  ADD `doneDate` date DEFAULT NULL;
  
ALTER TABLE `${prefix}risk` ADD done int(1) unsigned DEFAULT '0',
  ADD `doneDate` date DEFAULT NULL;
ALTER TABLE `${prefix}risk` CHANGE `closureDate` `idleDate` date DEFAULT NULL;
  
ALTER TABLE `${prefix}action` ADD done int(1) unsigned DEFAULT '0',
  ADD `doneDate` date DEFAULT NULL;
ALTER TABLE `${prefix}action` CHANGE `closureDate` `idleDate` date DEFAULT NULL;
  
ALTER TABLE `${prefix}project` ADD done int(1) unsigned DEFAULT '0',
  ADD `idleDate` date DEFAULT NULL,
  ADD `doneDate` date DEFAULT NULL;

ALTER TABLE `${prefix}issue` ADD done int(1) unsigned DEFAULT '0',
  ADD `doneDate` date DEFAULT NULL;
ALTER TABLE `${prefix}issue` CHANGE `closureDate` `idleDate` date DEFAULT NULL;
  
ALTER TABLE `${prefix}status` CHANGE `setEndStatus` `setDoneStatus` int(1) unsigned DEFAULT '0';

ALTER TABLE `${prefix}planningelement` ADD done int(1) unsigned DEFAULT '0';