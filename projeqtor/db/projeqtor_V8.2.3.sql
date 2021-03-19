-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.2.3                                       //
-- // Date : 2019-10-21                                     //
-- ///////////////////////////////////////////////////////////
-- Patch on V8.2

UPDATE `${prefix}menu` set isAdminMenu=1 where id=221;

UPDATE `${prefix}columnselector` set formatter=null where formatter='timeFormatter';