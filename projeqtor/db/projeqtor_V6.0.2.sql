-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 6.0.2                                       //
-- // Date : 2016-11-23                                     //
-- ///////////////////////////////////////////////////////////

INSERT INTO `${prefix}linkable` (`id`,`name`, `idle`, `idDefaultLinkable`) VALUES (20,'Organization', 0, null);

DELETE FROM `${prefix}columnselector` WHERE `objectClass` in ('Organization');
