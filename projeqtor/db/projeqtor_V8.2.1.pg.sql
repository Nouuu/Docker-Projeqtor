-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.2.1                                       //
-- // Date : 2019-09-20                                     //
-- ///////////////////////////////////////////////////////////
-- Patch on V8.2.0 for PG SQL only

ALTER TABLE `${prefix}tender`
CHANGE `initialTaxAmount` `taxAmount` DECIMAL(11,2) UNSIGNED;

ALTER TABLE `${prefix}tender`
CHANGE `initialFullAmount` `fullAmount` DECIMAL(11,2) UNSIGNED;

ALTER TABLE `${prefix}tender`
CHANGE `plannedAmount` `totalUntaxedAmount` DECIMAL(11,2) UNSIGNED;

ALTER TABLE `${prefix}tender`
CHANGE `plannedTaxAmount` `totalTaxAmount` DECIMAL(11,2) UNSIGNED;

ALTER TABLE `${prefix}tender`
CHANGE `plannedFullAmount` `totalFullAmount` DECIMAL(11,2) UNSIGNED;