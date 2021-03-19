-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.2.4                                       //
-- // Date : 2016-02-20                                     //
-- ///////////////////////////////////////////////////////////

INSERT INTO `${prefix}importable` (`id`, `name`, `idle`) VALUES ('42', 'DocumentDirectory', '0');

INSERT INTO `${prefix}mailable` (`id`, `name`, `idle`) VALUES 
(25,'Component', '0'), 
(26,'ComponentVersion', '0');

UPDATE `${prefix}mailable` set name='ProductVersion' where name='Version';
