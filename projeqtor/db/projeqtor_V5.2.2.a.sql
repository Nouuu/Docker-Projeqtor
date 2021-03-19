-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.2.0                                       //
-- // Date : 2015-12-04                                     //
-- ///////////////////////////////////////////////////////////

CREATE TABLE `${prefix}productversionstructure` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProductVersion` int(12) unsigned DEFAULT NULL,
  `idComponentVersion` int(12) unsigned DEFAULT NULL,
  `comment` varchar(4000),
  `creationDate` date,
  `idUser` int(12) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX ProductVersionStructureProduct ON `${prefix}productversionstructure` (idProductVersion);
CREATE INDEX ProductVersionStructureComponent ON `${prefix}productversionstructure` (idComponentVersion);
