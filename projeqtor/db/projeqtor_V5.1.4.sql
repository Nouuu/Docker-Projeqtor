-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.1.4                                       //
-- // Date : 2015-11-25                                     //
-- ///////////////////////////////////////////////////////////

DELETE FROM `${prefix}tempupdate` WHERE 1=1;

INSERT INTO `${prefix}tempupdate` (select id, refType, refId from `${prefix}workelement` WHERE (refType, refId) in 
    ( select refType, refId from  `${prefix}workelement` group by  refType, refId having count(*) >1 ));
  
DELETE FROM `${prefix}tempupdate` WHERE id in 
  ( select min(id) from  `${prefix}workelement` group by  refType, refId having count(*) >1 );

DELETE FROM `${prefix}workelement` WHERE id in 
  (select id from `${prefix}tempupdate`);

DELETE FROM `${prefix}tempupdate` WHERE 1=1;