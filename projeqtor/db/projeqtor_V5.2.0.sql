-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.2.0                                       //
-- // Date : 2015-12-04                                     //
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}product` ADD `scope` varchar(100);
UPDATE `${prefix}product` SET `scope`='Product';

ALTER TABLE `${prefix}version` ADD `scope` varchar(100);
UPDATE `${prefix}version` SET `scope`='Product';

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(141, 'menuComponent', 14, 'object', 464, 'ReadWriteEnvironment', 0, 'EnvironmentalParameter');
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 141, 1),
(2, 141, 0),
(3, 141, 0),
(4, 141, 0),
(5, 141, 0),
(6, 141, 0),
(7, 141, 0);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) 
SELECT `idProfile`, 141, `idAccessProfile` FROM `${prefix}accessright` WHERE `idMenu`=86;  

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(142, 'menuComponentVersion', 14, 'object', 466, 'ReadWriteEnvironment', 0, 'EnvironmentalParameter');
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 142, 1),
(2, 142, 0),
(3, 142, 0),
(4, 142, 0),
(5, 142, 0),
(6, 142, 0),
(7, 142, 0);
INSERT INTO `${prefix}accessright` (`idProfile`, `idMenu`, `idAccessProfile`) 
SELECT `idProfile`, 142, `idAccessProfile` FROM `${prefix}accessright` WHERE `idMenu`=87;  

UPDATE `${prefix}menu` SET name='menuProductVersion' WHERE name='menuVersion';

CREATE TABLE `${prefix}productstructure` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProduct` int(12) unsigned DEFAULT NULL,
  `idComponent` int(12) unsigned DEFAULT NULL,
  `comment` varchar(4000),
  `creationDate` date,
  `idUser` int(12) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX ProductStructureProduct ON `${prefix}productstructure` (idProduct);
CREATE INDEX ProductStructureComponent ON `${prefix}productstructure` (idComponent);

CREATE TABLE `${prefix}plugintriggeredevent` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idPlugin` int(12) unsigned DEFAULT NULL,
  `event` varchar(100),
  `className` varchar(100),
  `script` varchar(255),
  `idle` int(1) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX plugintriggeredeventPlugin ON `${prefix}plugintriggeredevent` (idPlugin);

ALTER TABLE `${prefix}type` ADD `idPlanningMode` int(12) unsigned DEFAULT NULL;
UPDATE `${prefix}type` set `idPlanningMode`=1 where scope='Activity';
UPDATE `${prefix}type` set `idPlanningMode`=16 where scope='Meeting';
UPDATE `${prefix}type` set `idPlanningMode`=5 where scope='Milestone';
UPDATE `${prefix}type` set `idPlanningMode`=9 where scope='TestSession';

CREATE TABLE `${prefix}extrahiddenfield` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `scope` varchar(100),
  `idType` int(12) unsigned DEFAULT NULL,
  `field` varchar(100),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

UPDATE `${prefix}importable` set name='ProductVersion' where name='Version';
INSERT INTO `${prefix}importable` (`name`, `idle`) VALUES ('Component', '0'),
('ComponentVersion', '0'),
('ProductStructure', '0'),
('Bill', '0'),
('Payment', '0'),
('ResourceCost', '0');

ALTER TABLE `${prefix}planningelement` ADD COLUMN `validatedExpenseCalculated` int(1) unsigned DEFAULT 0;

ALTER TABLE `${prefix}workelement` ADD COLUMN `idProject`  int(12) unsigned DEFAULT NULL;
UPDATE `${prefix}workelement` SET idProject=(SELECT `idProject` from `${prefix}ticket` where `${prefix}ticket`.`id`=`${prefix}workelement`.refId);

ALTER TABLE `${prefix}workelement` ADD `realCost` NUMERIC(11,2) DEFAULT NULL,
ADD `leftCost` NUMERIC(11,2) DEFAULT NULL;

CREATE TABLE `${prefix}restricttype` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProjectType` int(12) unsigned DEFAULT NULL,
  `idProject` int(12) unsigned DEFAULT NULL,
  `className` varchar(100) DEFAULT NULL,
  `idType` int(12) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX restricttypeProjectType ON `${prefix}restricttype` (idProjectType,className,idType);
CREATE INDEX restricttypeProject ON `${prefix}restricttype` (idProject,className,idType);

ALTER TABLE `${prefix}planningelement` ADD COLUMN `needReplan` int(1) unsigned DEFAULT 0;

ALTER TABLE `${prefix}attachment` CHANGE `fileName` `fileName` varchar(256);
