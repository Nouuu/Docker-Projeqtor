-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.1.1                                       //
-- // Date : 2015-10-27                                     //
-- ///////////////////////////////////////////////////////////

DELETE FROM `${prefix}plannedwork` WHERE idProject not in (select id from `${prefix}project`);
DELETE FROM `${prefix}assignment` WHERE idProject not in (select id from `${prefix}project`);

DELETE FROM `${prefix}columnselector` WHERE objectClass in ('Project','Quotation','Command','Bill');

DELETE FROM `${prefix}planningmode` WHERE id=21;
INSERT INTO `${prefix}planningmode` (`id`, `applyTo`, `name`, `code`, `sortOrder`, `idle`, `mandatoryStartDate`, `mandatoryEndDate`) VALUES
(21, 'TestSession', 'PlanningModeSTART', 'START', 130, 0 , 1, 0);