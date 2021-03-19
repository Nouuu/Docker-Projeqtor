-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 6.3.1 specific for postgresql               //
-- // Date : 2017-04-21                                     //
-- ///////////////////////////////////////////////////////////


CREATE INDEX `deliveryDeliverableTypeIdx` ON `${prefix}delivery` (`idDeliverableType`);
CREATE INDEX `deliveryDeliverableStatusIdx` ON `${prefix}delivery` (`idDeliverableStatus`);
CREATE INDEX `deliveryProjectIdx` ON `${prefix}delivery` (`idProject`);

CREATE TABLE `${prefix}language` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(100) NOT NULL,
  `sortOrder` int(3),
  `idle` int(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `${prefix}productlanguage` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProduct` int(12) unsigned NOT NULL,
  `idLanguage` int(12) unsigned NOT NULL,
  `creationDate` date NOT NULL,
  `idUser` int(12) unsigned NOT NULL,
  `idle` int(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `${prefix}productcontext` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProduct` int(12) unsigned NOT NULL,
  `idContext` int(12) unsigned NOT NULL,
  `creationDate` date NOT NULL,
  `idUser` int(12) unsigned NOT NULL,
  `idle` int(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `${prefix}version` ADD `isStarted` INT(1) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `${prefix}version` ADD `realStartDate` DATE NULL DEFAULT NULL;
ALTER TABLE `${prefix}version` ADD `plannedStartDate` DATE NULL DEFAULT NULL;
ALTER TABLE `${prefix}version` ADD `initialStartDate` DATE NULL DEFAULT NULL;

ALTER TABLE `${prefix}version` ADD `isDelivered` INT(1) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `${prefix}version` ADD `realDeliveryDate` DATE NULL DEFAULT NULL;
ALTER TABLE `${prefix}version` ADD `plannedDeliveryDate` DATE NULL DEFAULT NULL;
ALTER TABLE `${prefix}version` ADD `initialDeliveryDate` DATE NULL DEFAULT NULL;