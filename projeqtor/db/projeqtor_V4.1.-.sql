-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 4.1.0                                       //
-- // Date : 2013-11-14                                     //
-- ///////////////////////////////////////////////////////////
--
--

DELETE FROM `${prefix}columnselector` WHERE attribute='idTicketType' and hidden='1';

UPDATE `${prefix}columnselector` set attribute='idTicketType', field='nameTicketType'
WHERE attribute='idticketType';

DELETE FROM `${prefix}columnselector` WHERE attribute='requestRefreshProject';

CREATE TABLE `${prefix}quality` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `icon` varchar(100),
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}quality` (`id`, `name`, `color`, `sortOrder`, `idle`, `icon`) VALUES
(1,'conform','#32CD32',100,0,'smileyGreen.png'),
(2,'some remarks','#ffd700',200,0,'smileyYellow.png'),
(3,'not conform','#FF0000',300,0,'smileyRed.png');

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`) VALUES 
(128,'menuQuality',36,'object',706,NULL,0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 128, 1),
(2, 128, 0),
(3, 128, 0),
(4, 128, 0),
(5, 128, 0),
(6, 128, 0),
(7, 128, 0);

CREATE TABLE `${prefix}trend` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `icon` varchar(100),
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}trend` (`id`, `name`, `color`, `sortOrder`, `idle`, `icon`) VALUES
(1,'increasing','#32CD32',100,0,'arrowUpGreen.png'),
(2,'even','#ffd700',200,0,'arrowRightGrey.png'),
(3,'decreasing','#FF0000',300,0,'arrowDownRed.png');

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`) VALUES 
(129,'menuTrend',36,'object',709,NULL,0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 129, 1),
(2, 129, 0),
(3, 129, 0),
(4, 129, 0),
(5, 129, 0),
(6, 129, 0),
(7, 129, 0);

ALTER TABLE `${prefix}project` ADD COLUMN `idQuality` int(12) unsigned,
ADD COLUMN `idTrend` int(12) unsigned,
ADD COLUMN `idSponsor` int(12) unsigned;

ALTER TABLE `${prefix}health` ADD COLUMN `icon` varchar(100);

ALTER TABLE `${prefix}type` ADD COLUMN `showInFlash` int(1) unsigned default 0;