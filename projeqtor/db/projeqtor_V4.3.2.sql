-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 4.3.2                                //
-- // Date : 2014-06-27                                     //
-- ///////////////////////////////////////////////////////////

DELETE FROM `${prefix}planningelement` WHERE refType = 'Activity' and not exists (select 'x' from `${prefix}activity` where id=refId);

DELETE FROM `${prefix}planningelement` WHERE refType = 'Milestone' and not exists (select 'x' from `${prefix}milestone` where id=refId);

UPDATE `${prefix}report` SET sortOrder=282 WHERE id=52;