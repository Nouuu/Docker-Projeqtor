-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 6.4.1 specific for postgresql               //
-- // Date : 2017-09-23                                     //
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}dependency` ALTER COLUMN `successorId` SET DEFAULT NULL;
ALTER TABLE `${prefix}dependency` CHANGE predecessorId predecessorId INT(12) UNSIGNED;
ALTER TABLE `${prefix}dependency` ALTER COLUMN `predecessorId` SET DEFAULT NULL;