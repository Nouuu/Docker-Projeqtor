-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.4.1                                       //
-- // Date : 2020-03-18                                     //
-- ///////////////////////////////////////////////////////////
-- Patch on V8.4.0

ALTER TABLE `${prefix}budgetelement` CHANGE `expenseAssignedAmount` `expenseAssignedAmount` DECIMAL(11,2);
