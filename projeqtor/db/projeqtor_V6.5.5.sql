-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 6.5.1 specific for postgresql               //
-- // Date : 2017-12-12                                     //
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}menu` ADD `isAdminMenu` int(1) DEFAULT '0';
UPDATE `${prefix}menu` SET `isAdminMenu`=1 where id in (17,18,21,37,47,48,49,70,71,92,135,136,143);
