-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 7.2.6                                       //
-- // Date : 2018-10-22                                     //
-- ///////////////////////////////////////////////////////////
--
--

DELETE FROM `${prefix}columnselector` WHERE objectClass='Work';

ALTER TABLE `${prefix}budget`
ADD `targetAmount` decimal(14,2) UNSIGNED,
ADD `targetFullAmount` decimal(14,2) UNSIGNED;
