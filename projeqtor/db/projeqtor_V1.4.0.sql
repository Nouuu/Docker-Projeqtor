
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V1.4.0                           //
-- // Date : 2010-11-03                                     //
-- ///////////////////////////////////////////////////////////
--
--
ALTER TABLE `${prefix}planningmode` ADD `mandatoryDuration` int(1) unsigned DEFAULT '0';

INSERT INTO `${prefix}planningmode` (`id`, `applyTo`, `name`, `code`, `sortOrder`, `idle`, `mandatoryStartDate`, `mandatoryEndDate`, `mandatoryDuration`) VALUES
(7, 'Activity', 'PlanningModeHALF', 'HALF', 320, 0 , 0, 0, 0),
(8, 'Activity', 'PlanningModeFDUR', 'FDUR', 450, 0, 0 , 0, 1);

UPDATE `${prefix}planningelement` SET idPlanningMode='5' 
WHERE refType='Milestone';

ALTER TABLE `${prefix}resource` ADD `address` varchar(4000),
ADD isContact int(1) unsigned DEFAULT '0',
ADD idClient int(12) unsigned DEFAULT NULL;

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES
(72, 'menuContact', 14, 'object', 913, null, 0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 72, 1);

ALTER TABLE `${prefix}issue` ADD idCriticality int(12) unsigned DEFAULT NULL;