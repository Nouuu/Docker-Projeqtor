-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 7.3.0                                       //
-- // Date : 2018-06-18                                     //
-- ///////////////////////////////////////////////////////////

UPDATE `${prefix}menu` SET sortOrder=425 WHERE id=58;

-- FILTER ON REPORTS
ALTER TABLE `${prefix}report`
ADD `filterClass` varchar(100) DEFAULT NULL;

UPDATE `${prefix}report` set `filterClass`='Ticket' WHERE id in (9,10,11,12,13,14,15,16,17,18,73,74,80,83);

-- UPGRADE FILESIZE FOR ATTACHED FILES

ALTER TABLE `${prefix}attachment` CHANGE `fileSize` `fileSize` BIGINT(12) UNSIGNED;
ALTER TABLE `${prefix}documentversion` CHANGE `fileSize` `fileSize` BIGINT(12) UNSIGNED;

-- FINANCIAL TTC 

ALTER TABLE `${prefix}tender`
ADD `discountFullAmount` decimal(11,2) UNSIGNED;

ALTER TABLE `${prefix}providerbill`
ADD `discountFullAmount` decimal(11,2) UNSIGNED;

ALTER TABLE `${prefix}providerorder`
ADD `discountFullAmount` decimal(11,2) UNSIGNED;

INSERT INTO `${prefix}parameter` (`idUser`,`idProject`,`parameterCode`,`parameterValue`) VALUES 
(NULL,NULL,'ImputOfAmountProvider','HT'),
(NULL,NULL,'ImputOfBillLineProvider','HT'),
(NULL,NULL,'ImputOfAmountClient','HT'),
(NULL,NULL,'ImputOfBillLineClient','HT');
