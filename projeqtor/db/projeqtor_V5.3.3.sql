-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.3.3                                       //
-- // Date : 2016-04-26                                     //
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}attachment` CHANGE `fileName` `fileName` VARCHAR(1024), 
CHANGE `link` `link` VARCHAR(1024);

DELETE FROM `${prefix}tempupdate` WHERE 1=1;

INSERT INTO `${prefix}tempupdate` (select id, refType, refId from `${prefix}workelement` WHERE (refType, refId) in 
    ( select refType, refId from  `${prefix}workelement` group by  refType, refId having count(*) >1 ));
  
DELETE FROM `${prefix}tempupdate` WHERE id in 
  ( select min(id) from  `${prefix}workelement` group by  refType, refId having count(*) >1 );

DELETE FROM `${prefix}workelement` WHERE id in 
  (select id from `${prefix}tempupdate`);

DELETE FROM `${prefix}tempupdate` WHERE 1=1;

ALTER TABLE `${prefix}workelement` DROP INDEX workelementReference;

CREATE UNIQUE INDEX workelementReference ON `${prefix}workelement` (refType, refId);

DELETE FROM `${prefix}tempupdate` WHERE 1=1;

INSERT INTO `${prefix}tempupdate` (select id, refType, refId from `${prefix}planningelement` WHERE (refType, refId) in 
    ( select refType, refId from  `${prefix}planningelement` group by  refType, refId having count(*) >1 ));
  
DELETE FROM `${prefix}tempupdate` WHERE id in 
  ( select min(id) from  `${prefix}planningelement` group by  refType, refId having count(*) >1 );

DELETE FROM `${prefix}planningelement` WHERE id in 
  (select id from `${prefix}tempupdate`);

DELETE FROM `${prefix}tempupdate` WHERE 1=1;

ALTER TABLE `${prefix}planningelement` DROP INDEX planningelementRef;

CREATE UNIQUE INDEX planningelementReference ON `${prefix}planningelement` (`refType`,`refId`);