-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.3.1                                       //
-- // Date : 2019-12-16                                     //
-- ///////////////////////////////////////////////////////////


-- ======================================
-- Change Request
-- ======================================

UPDATE `${prefix}menu` SET  `level`='Project'  WHERE  `name`='menuChangeRequest';

DELETE FROM `${prefix}parameter` WHERE `parameterCode`='selectAbsenceActivity';
DELETE FROM `${prefix}parameter` WHERE `parameterCode`='inputIdProject';
DELETE FROM `${prefix}parameter` WHERE `parameterCode`='inputAssId'; 