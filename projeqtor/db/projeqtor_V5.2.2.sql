-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.2.0                                       //
-- // Date : 2015-12-04                                     //
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}projecthistory` CHANGE `realWork` `realWork` DECIMAL(14,5) UNSIGNED,
 CHANGE `leftWork` `leftWork` DECIMAL(14,5) UNSIGNED;

DELETE FROM `${prefix}indicatorableindicator` WHERE `idIndicatorable`=11;
DELETE FROM `${prefix}indicatorable` WHERE id=11;
DELETE FROM `${prefix}indicatorableindicator` WHERE `idIndicatorable`=10;
DELETE FROM `${prefix}indicatorable` WHERE id=10;