
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V3.0.0                               //
-- // Date : 2012-09-06                                     //
-- ///////////////////////////////////////////////////////////
--
--
CREATE TABLE `${prefix}mutex` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}parameter` (idUser, idProject, parameterCode, parameterValue) VALUES
(null,null, 'cronDirectory', '../files/cron');

INSERT INTO `${prefix}parameter` (idUser, idProject, parameterCode, parameterValue) VALUES
(null,null, 'paramMailTitleDirect', '[${dbName}] message from ${sender} : ${item} #${id}');

CREATE TABLE `${prefix}otherversion` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `refType` varchar(100) DEFAULT NULL,
  `refId` int(12) unsigned NOT NULL,
  `idVersion` int(12) unsigned NOT NULL,
  `scope` varchar(100) DEFAULT NULL,
  `comment` varchar(4000), 
  `creationDate` datetime, 
  `idUser` int(12) unsigned default null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE INDEX otherversionRef ON `${prefix}otherversion` (refType, refId);
CREATE INDEX otherversionVersion ON `${prefix}otherversion` (idVersion);
CREATE INDEX otherversionUser ON `${prefix}otherversion` (idUser);

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(132,38,'otherVersions','boolean',900,0,null),
(133,39,'otherVersions','boolean',900,0,null);