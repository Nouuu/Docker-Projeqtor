-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 4.1.0                                       //
-- // Date : 2013-11-14                                     //
-- ///////////////////////////////////////////////////////////
--
--
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 4.1.0                                       //
-- // Date : 2013-11-14                                     //
-- ///////////////////////////////////////////////////////////
--
--

CREATE TABLE `${prefix}calendardefinition` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}calendardefinition` (id, name, sortOrder, idle) VALUES
(1, 'default', 10, 0);

ALTER TABLE `${prefix}calendar` ADD COLUMN `idCalendarDefinition` int(12) unsigned default 1;

ALTER TABLE `${prefix}resource` ADD COLUMN `idCalendarDefinition` int(12) unsigned default 1;

ALTER TABLE `${prefix}client` ADD COLUMN `designation` varchar(50), 
  ADD COLUMN `street` varchar(50), 
  ADD COLUMN `complement` varchar(50), 
  ADD COLUMN `zip` varchar(50), 
  ADD COLUMN `city` varchar(50), 
  ADD COLUMN `state` varchar(50), 
  ADD COLUMN `country` varchar(50);