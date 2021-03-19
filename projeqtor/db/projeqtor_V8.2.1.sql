-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.2.1                                       //
-- // Date : 2019-09-20                                     //
-- ///////////////////////////////////////////////////////////
-- Patch on V8.2.0

DELETE FROM `${prefix}parameter` WHERE `parameterCode`='contentPaneRightDetailDivHeight' and idUser is null; 
DELETE FROM `${prefix}parameter` WHERE `parameterCode`='contentPaneRightDetailDivWidth' and idUser is null; 
UPDATE `${prefix}parameter` SET `parameterValue`=260 WHERE `parameterCode`='contentPaneDetailDivHeight' and idUser is null; 
UPDATE `${prefix}parameter` SET `parameterValue`=410 WHERE `parameterCode`='contentPaneDetailDivWidth' and idUser is null; 

INSERT INTO `${prefix}parameter` (`parameterCode`, `parameterValue`) VALUES 
('modeActiveStreamGlobal','false');

UPDATE `${prefix}parameter` SET `parameterValue`='top' WHERE `parameterCode`='paramScreen' and `parameterValue`='1';
UPDATE `${prefix}parameter` SET `parameterValue`='left' WHERE `parameterCode`='paramScreen' and `parameterValue`='2';
UPDATE `${prefix}parameter` SET `parameterValue`='switch' WHERE `parameterCode`='paramScreen' and `parameterValue`='5';
UPDATE `${prefix}parameter` SET `parameterValue`='col' WHERE `parameterCode`='paramLayoutObjectDetail' and `parameterValue`='4';
UPDATE `${prefix}parameter` SET `parameterValue`='tab' WHERE `parameterCode`='paramLayoutObjectDetail' and `parameterValue`='0';
UPDATE `${prefix}parameter` SET `parameterValue`='trailing' WHERE `parameterCode`='paramRightDiv' and `parameterValue`='0';
UPDATE `${prefix}parameter` SET `parameterValue`='bottom' WHERE `parameterCode`='paramRightDiv' and `parameterValue`='3';