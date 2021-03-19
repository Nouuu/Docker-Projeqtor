-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 6.1.1                                       //
-- // Date : 2017-02-13                                     //
-- ///////////////////////////////////////////////////////////

UPDATE `${prefix}menu` SET `menuClass`='Type' WHERE `name`='menuDeliverableType';

UPDATE `${prefix}menu` SET `level`='ReadWriteType' WHERE `name`='menuCallForTenderType' or `name`='menuTenderType';