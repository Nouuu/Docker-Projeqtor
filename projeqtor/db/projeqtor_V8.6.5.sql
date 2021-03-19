-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.6.2                                       //
-- // Date : 2020-11-18                                     //
-- ///////////////////////////////////////////////////////////
-- Patch on V8.6

UPDATE `${prefix}resource` set idOrganization=null, idTeam=null where isResource=0;

UPDATE `${prefix}notifiable` set notifiableItem=name where notifiableItem is null;
