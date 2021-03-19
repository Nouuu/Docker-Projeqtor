-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.2.5                                       //
-- // Date : 2016-03-02                                     //
-- ///////////////////////////////////////////////////////////

DELETE FROM `${prefix}affectation` WHERE idProject NOT IN (SELECT id FROM `${prefix}project`);
DELETE FROM `${prefix}assignment` WHERE idProject NOT IN (SELECT id FROM `${prefix}project`);
DELETE FROM `${prefix}work` WHERE idProject NOT IN (SELECT id FROM `${prefix}project`);

--UPDATE `${prefix}affectation` SET startDate=NULL WHERE startDate='0000-00-00';
--UPDATE `${prefix}affectation` SET endDate=NULL WHERE endDate='0000-00-00';

UPDATE `${prefix}attachment` SET refType='ProductVersion' where refType='Version';
UPDATE `${prefix}link` SET ref1Type='ProductVersion' where ref1Type='Version';
UPDATE `${prefix}link` SET ref2Type='ProductVersion' where ref2Type='Version';
UPDATE `${prefix}note` SET refType='ProductVersion' where refType='Version';

ALTER TABLE `${prefix}projecthistory` CHANGE `realWork` `realWork` DECIMAL(14,5) UNSIGNED;
ALTER TABLE `${prefix}projecthistory` CHANGE `leftWork` `leftWork` DECIMAL(14,5) UNSIGNED;