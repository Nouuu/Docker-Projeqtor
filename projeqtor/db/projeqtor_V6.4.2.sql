-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 6.4.2 specific for postgresql               //
-- // Date : 2017-10-14                                     //
-- ///////////////////////////////////////////////////////////

INSERT INTO `${prefix}habilitationother` (`idProfile`, `scope`, `rightAccess`) 
SELECT `id`, 'multipleUpdate', '1' FROM `${prefix}profile`;   