-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 4.2.1                                       //
-- // Date : 2014-03-06                                     //
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}quotation` CHANGE `creationDate` `creationDate` date;

ALTER TABLE `${prefix}command` CHANGE `creationDate` `creationDate` date;