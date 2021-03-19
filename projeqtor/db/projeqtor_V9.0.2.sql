-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.6.1                                       //
-- // Date : 2020-09-21                                     //
-- ///////////////////////////////////////////////////////////
-- Patch on V8.6

UPDATE `${prefix}navigation` SET tag='log;cron' where name='menuAdmin';

ALTER TABLE `${prefix}parameter` DROP INDEX parameterProject;
ALTER TABLE `${prefix}parameter` DROP INDEX parameterUser;
CREATE UNIQUE INDEX parameterUserProject ON `${prefix}parameter` (idUser, idProject);
