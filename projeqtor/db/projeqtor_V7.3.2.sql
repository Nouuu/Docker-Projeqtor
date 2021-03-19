-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 7.3.2                                       //
-- // Date : 2018-12-06                                     //
-- ///////////////////////////////////////////////////////////
--
--

DELETE FROM `${prefix}columnselector` WHERE objectClass='Tender';

-- Fix issue of work entered by X given to Y

DELETE FROM `${prefix}tempupdate` WHERE 1=1;

INSERT INTO `${prefix}tempupdate` (id, idOther)
 SELECT w.id, (SELECT xa.idResource FROM `${prefix}assignment` xa WHERE xa.id=w.idAssignment) 
 FROM `${prefix}work` w 
 WHERE w.idAssignment is not null AND w.idResource!=(SELECT a.idResource FROM `${prefix}assignment` a, `${prefix}resource` r WHERE a.id=w.idAssignment AND a.idResource=r.id AND r.isResourceTeam!=1);

UPDATE `${prefix}work` w SET idResource=(SELECT t.idOther FROM `${prefix}tempupdate` t WHERE t.id=w.id) 
WHERE w.id IN (SELECT id FROM `${prefix}tempupdate`);

DELETE FROM `${prefix}tempupdate` WHERE 1=1;

-- Fix issue of planning element with refId=0
DELETE FROM `${prefix}planningelement` WHERE refType='Project' and refId=0 and refName is null;