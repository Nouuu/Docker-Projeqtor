-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 4.5.0                                       //
-- // Date : 2014-08-14                                     //
-- ///////////////////////////////////////////////////////////

UPDATE `${prefix}accessprofile` SET name='accessProfileRestrictedReader' WHERE name='accessProfileRestrictiedReader';
UPDATE `${prefix}accessprofile` SET name='accessProfileRestrictedCreator' WHERE name='accessProfileRestricedCreator';

INSERT INTO `${prefix}dependable` (id, `name`, `scope`, `idDefaultDependable`, `idle`) VALUES 
(7, 'Meeting', 'PE', 1, 0);

UPDATE `${prefix}planningmode` SET mandatoryStartDate=1, mandatoryEndDate=1 WHERE code='HALF';

INSERT INTO `${prefix}accessscope` (`id`, `name`, `accessCode`, `sortOrder`, `idle`) VALUES
(5, 'accessScopeResp', 'RES', 250, 0);

ALTER TABLE `${prefix}audit` CHANGE `connection` `connectionDateTime` datetime;
ALTER TABLE `${prefix}audit` CHANGE `disconnection` `disconnectionDateTime` datetime;
ALTER TABLE `${prefix}audit` CHANGE `lastAccess` `lastAccessDateTime` datetime;

DELETE FROM `${prefix}columnselector` WHERE objectClass='Audit';

UPDATE `${prefix}assignment` ass set idProject=(SELECT idProject FROM `${prefix}planningelement` pe WHERE pe.refType=ass.refType and pe.refId=ass.refId)
WHERE exists (SELECT idProject FROM `${prefix}planningelement` pex WHERE pex.refType=ass.refType and pex.refId=ass.refId);

ALTER TABLE `${prefix}assignment` ADD COLUMN `notPlannedWork` DECIMAL(12,5) UNSIGNED DEFAULT 0;
ALTER TABLE `${prefix}planningelement` ADD COLUMN `notPlannedWork` DECIMAL(12,5) UNSIGNED DEFAULT 0;