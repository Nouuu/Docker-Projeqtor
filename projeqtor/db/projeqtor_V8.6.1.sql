-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.6.1                                       //
-- // Date : 2020-09-21                                     //
-- ///////////////////////////////////////////////////////////
-- Patch on V8.6

INSERT INTO `${prefix}copyable` (`id`,`name`, `idle`, `sortOrder`,`idDefaultCopyable`) VALUES 
(31,'CatalogUO', '0', '131','31');

ALTER TABLE `${prefix}planningelementbaseline` 
ADD COLUMN `revenue` decimal(11,2) unsigned DEFAULT NULL,
ADD COLUMN `commandSum` decimal(11,2) unsigned DEFAULT NULL,
ADD COLUMN `billSum` decimal(11,2) unsigned DEFAULT NULL,
ADD COLUMN `idRevenueMode` int(12) unsigned DEFAULT NULL COMMENT '12',
ADD COLUMN `idWorkUnit` int(12) unsigned DEFAULT NULL COMMENT '12',
ADD COLUMN `idComplexity` int(12) unsigned DEFAULT NULL COMMENT '12',
ADD COLUMN `quantity` int(5) unsigned DEFAULT NULL COMMENT '5';