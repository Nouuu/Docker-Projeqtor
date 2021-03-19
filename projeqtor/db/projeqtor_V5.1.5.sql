-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.1.5                                       //
-- // Date : 2015-12-09                                     //
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}quotation` CHANGE `untaxedAmount` `untaxedAmount` DECIMAL(12,2);

ALTER TABLE `${prefix}command` CHANGE `untaxedAmount` `untaxedAmount` DECIMAL(12,2),
CHANGE `addUntaxedAmount` `addUntaxedAmount` DECIMAL(12,2),
CHANGE `totalUntaxedAmount` `totalUntaxedAmount` DECIMAL(12,2);