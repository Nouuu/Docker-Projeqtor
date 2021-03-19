-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 6.0.3                                       //
-- // Date : 2016-12-02                                     //
-- ///////////////////////////////////////////////////////////

UPDATE  `${prefix}extrahiddenfield` set scope=concat('Type#',scope) where scope not like '%#%';

INSERT INTO `${prefix}importable` (`id`, `name`, `idle`) VALUES ('43', 'Provider', '0');