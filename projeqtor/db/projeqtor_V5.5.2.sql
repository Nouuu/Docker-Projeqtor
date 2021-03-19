-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.5.2                        //
-- // Date : 2016-07-28                                     //
-- ///////////////////////////////////////////////////////////

UPDATE `${prefix}menu` set level='Project' where id in (1,150,7,8,9,123,106,133,110,74,151,152,94,146,43,6,11);

UPDATE `${prefix}menu` set idle=1 where id in (151,152);

-- remove duplicate in table Work

DELETE FROM `${prefix}tempupdate` WHERE 1=1;

ALTER TABLE `${prefix}tempupdate` ADD `idOther` int(12) UNSIGNED DEFAULT NULL,
ADD `workValue` DECIMAL(8,5) UNSIGNED,
ADD `costValue` DECIMAL(12,2) UNSIGNED;

INSERT INTO `${prefix}tempupdate` (select distinct min(id), day, idAssignment, idWorkElement, sum(work), sum(cost) from `${prefix}work` 
 where idAssignment is not null group by day, idAssignment, idWorkElement having count(*) >1 );
  
UPDATE `${prefix}work` SET work=(select workValue from `${prefix}tempupdate` temp where temp.id=`${prefix}work`.id),
cost=(select costValue from `${prefix}tempupdate` temp where temp.id=`${prefix}work`.id)
WHERE id in (select id from `${prefix}tempupdate`);
 
DELETE FROM `${prefix}work` WHERE (day, idAssignment, idWorkElement) in ( select refType, refId, idOther from  `${prefix}tempupdate`) 
AND id not in (select id from `${prefix}tempupdate`);
DELETE FROM `${prefix}work` WHERE (day, idAssignment) in ( select refType, refId from  `${prefix}tempupdate` where idOther is null) 
AND id not in (select id from `${prefix}tempupdate`) and idWorkElement is null;

DELETE FROM `${prefix}tempupdate` WHERE 1=1;

ALTER TABLE `${prefix}work` DROP INDEX workReference;
CREATE UNIQUE INDEX workReference ON `${prefix}work` (idAssignment, workDate, idWorkElement);