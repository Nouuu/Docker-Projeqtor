
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V3.4.0                                      //
-- // Date : 2013-05-06                                     //
-- ///////////////////////////////////////////////////////////
--
--

UPDATE `${prefix}assignment` SET idProject=(select idProject from `${prefix}activity` A where A.id=refId) 
where idProject=0 and refType='Activity';

UPDATE `${prefix}assignment` SET idProject=(select idProject from `${prefix}ticket` T where T.id=refId) 
where idProject=0 and refType='Ticket';

UPDATE `${prefix}work` SET idProject=(select idProject from `${prefix}activity` A where A.id=refId) 
where idProject=0 and refType='Activity';

UPDATE `${prefix}work` SET idProject=(select idProject from `${prefix}ticket` T where T.id=refId) 
where idProject=0 and refType='Ticket';