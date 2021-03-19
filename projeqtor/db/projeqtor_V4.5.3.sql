-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 4.5.3                                       //
-- // Date : 2014-12-02                                     //
-- ///////////////////////////////////////////////////////////

CREATE TABLE `${prefix}tempupdate` (
  `id` int(12),
  `refType` varchar(100) DEFAULT NULL,
  `refId` int(12)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

DELETE FROM `${prefix}tempupdate` WHERE 1=1;

INSERT INTO `${prefix}tempupdate` (select id, refType, refId from `${prefix}workelement` WHERE (refType, refId) in 
    ( select refType, refId from  `${prefix}workelement` group by  refType, refId having count(*) >1 ));
  
DELETE FROM `${prefix}tempupdate` WHERE id in 
  ( select min(id) from  `${prefix}workelement` group by  refType, refId having count(*) >1 );

DELETE FROM `${prefix}workelement` WHERE id in 
  (select id from `${prefix}tempupdate`);

DELETE FROM `${prefix}tempupdate` WHERE 1=1;