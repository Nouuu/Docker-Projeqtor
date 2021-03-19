-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : Consistency                                 //
-- // Date : 2017-12-29                                     //
-- ///////////////////////////////////////////////////////////
-- // This script includes some querys able to enforce      //
-- // database consistency                                  //
-- ///////////////////////////////////////////////////////////

-- Remove PlanningElement without corresponding item (item deleted, planning element persisting) 

DELETE FROM `${prefix}planningelement` WHERE refType='Activity' and refId not in (select id from `${prefix}activity`);
DELETE FROM `${prefix}planningelement` WHERE refType='Meeting' and refId not in (select id from `${prefix}meeting`);
DELETE FROM `${prefix}planningelement` WHERE refType='Milestone' and refId not in (select id from `${prefix}milestone`);
DELETE FROM `${prefix}planningelement` WHERE refType='PeriodicMeeting' and refId not in (select id from `${prefix}periodicmeeting`);
DELETE FROM `${prefix}planningelement` WHERE refType='Project' and refId not in (select id from `${prefix}project`);
DELETE FROM `${prefix}planningelement` WHERE refType='TestSession' and refId not in (select id from `${prefix}testsession`);

-- Remove WorkElement without corresponding item (item deleted, work element persisting)

DELETE FROM `${prefix}workelement` WHERE refType='Ticket' and refId not in (select id from `${prefix}ticket`);

-- Invalid idAssignment on Work (idAssignment not set for work on ticket with Planning Activity)

UPDATE ${prefix}work set idAssignment=(
    select min(ass.id) from ${prefix}assignment ass, ${prefix}ticket tkt 
    where ass.idResource=${prefix}work.idResource and ass.refType='Activity' and ass.refId=tkt.idActivity and ${prefix}work.refId=tkt.id),
    refId=(select tkt.idActivity from ${prefix}ticket tkt where ${prefix}work.refId=tkt.id),
    refType='Activity'
where idAssignment is null and refType='Ticket' and exists (select 'x' from ${prefix}ticket t where t.id=${prefix}work.refId and t.idActivity is not null)