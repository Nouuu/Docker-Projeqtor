
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V1.0.0                           //
-- // Date : 2010-06-03                                     //
-- ///////////////////////////////////////////////////////////
--
--

ALTER TABLE `${prefix}action` ADD idPriority int(12) unsigned DEFAULT NULL;

ALTER TABLE `${prefix}assignment` ADD comment varchar(4000);

CREATE TABLE `${prefix}filter` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `refType` varchar(100),
  `idUser` int(12) unsigned,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `${prefix}filtercriteria` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idFilter` int(12) unsigned NOT NULL,
  `dispAttribute` varchar(100),
  `dispOperator` varchar(100),
  `dispValue` varchar(4000),
  `sqlAttribute` varchar(100),
  `sqlOperator` varchar(100),
  `sqlValue` varchar(100), 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `${prefix}parameter` (`idUser`,`idProject`,`parameterCode`,`parameterValue`) VALUES 
(NULL,NULL,'isManualProgress','YES');