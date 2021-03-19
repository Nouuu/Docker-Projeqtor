-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.3.0                                       //
-- // Date : 2019-09-27                                     //
-- ///////////////////////////////////////////////////////////

-- ======================================
-- Kanban
-- ======================================

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(100006001, 'menuKanban', 0, 'item', 35, NULL, 0, 'Work Risk RequirementTest Financial Meeting ');        

CREATE TABLE `${prefix}kanban` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idUser` int(12),
  `isShared` int(1),
  `name`  varchar(64),
  `type`  varchar(64),
  `param` varchar(10000),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;