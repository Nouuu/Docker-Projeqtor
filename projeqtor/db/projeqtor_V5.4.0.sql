-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.4.0                                       //
-- // Date : 2016-05-12                                     //
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}assignment` CHANGE `dailyCost` `dailyCost` DECIMAL(11,2) UNSIGNED,
CHANGE `newDailyCost` `newDailyCost` DECIMAL(11,2) UNSIGNED;

ALTER TABLE `${prefix}work` CHANGE `dailyCost` `dailyCost` DECIMAL(11,2) UNSIGNED;

ALTER TABLE `${prefix}billline` CHANGE `quantity` `quantity` DECIMAL(9,2) UNSIGNED;

INSERT INTO `${prefix}menu` (`id`,`name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(150,'menuDashboardTicket', 0, 'item', 15, NULL, 0, 'Work Risk RequirementTest Financial Meeting ');

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 150, 1),
(2, 150, 1),
(3, 150, 1),
(4, 150, 1),
(5, 150, 0),
(6, 150, 0),
(7, 150, 0);

ALTER TABLE `${prefix}expense` ADD COLUMN `idDocument` int(12) unsigned;
ALTER TABLE `${prefix}filter` ADD COLUMN `isShared` int(1) unsigned;

ALTER TABLE `${prefix}billline` CHANGE `quantity` `quantity` DECIMAL(9,2) UNSIGNED;

CREATE TABLE `${prefix}provider` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `idProviderType` int(12) unsigned DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `providerCode` varchar(25) DEFAULT NULL,
  `idPaymentDelay` int(12) unsigned DEFAULT NULL,
  `numTax` varchar (100) DEFAULT NULL,
  `tax` decimal(5,2),
  `designation` varchar (100),
  `street`  varchar (100),
  `complement`  varchar (100),
  `zip`  varchar (100),
  `city`  varchar (100),
  `state`  varchar (100),
  `country`  varchar (100),
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
CREATE INDEX providerProviderType ON `${prefix}provider` (idProviderType);

ALTER TABLE `${prefix}expense` ADD COLUMN `idProvider` int(12) unsigned DEFAULT NULL,
ADD COLUMN `sendDate` date DEFAULT NULL,
ADD COLUMN `idDeliveryMode` int(12) unsigned DEFAULT NULL,
ADD COLUMN `deliveryDelay` varchar(100) DEFAULT NULL,
ADD COLUMN `deliveryDate` date DEFAULT NULL,
ADD COLUMN `paymentCondition` varchar(100) DEFAULT NULL,
ADD COLUMN `receptionDate` date DEFAULT NULL,
ADD COLUMN `result` mediumtext DEFAULT NULL,
ADD COLUMN `taxPct` decimal(5,2) DEFAULT NULL,
ADD COLUMN `plannedFullAmount` decimal (11,2) DEFAULT 0,
ADD COLUMN `realFullAmount` decimal (11,2) DEFAULT 0,
ADD COLUMN `idleDate` date DEFAULT NULL,
ADD COLUMN `handled` int(1) unsigned DEFAULT '0',
ADD COLUMN `handledDate` date DEFAULT NULL,
ADD COLUMN `done` int(1) unsigned DEFAULT '0',
ADD COLUMN `doneDate` date DEFAULT NULL,
ADD COLUMN `idResponsible` int(12) unsigned DEFAULT NULL;
CREATE INDEX expenseProvider ON `${prefix}expense` (idProvider);
CREATE INDEX expenseResponsible ON `${prefix}expense` (idResponsible);

UPDATE `${prefix}expense` SET `plannedFullAmount`=`plannedAmount`,
`realFullAmount`=`realAmount`
WHERE `scope`='ProjectExpense';

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(147, 'menuProviderType', 79, 'object', 927, 'ReadWriteType', 0, 'Type '),
(148, 'menuProvider', 14, 'object', 525, 'ReadWriteEnvironment', 0, 'Financial EnvironmentalParameter ');
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 147, 1),
(2, 147, 0),
(3, 147, 0),
(4, 147, 0),
(5, 147, 0),
(6, 147, 0),
(7, 147, 0),
(1, 148, 1),
(2, 148, 0),
(3, 148, 0),
(4, 148, 0),
(5, 148, 0),
(6, 148, 0),
(7, 148, 0);
INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `idWorkflow`, `mandatoryDescription`, `mandatoryResultOnDone`, `mandatoryResourceOnHandled`, `lockHandled`, `lockDone`, `lockIdle`, `code`) VALUES 
('Provider', 'wholesaler', '10', '0', '1', '0', '0', '0', '0', '0', '0', ''),
('Provider', 'retailer', '20', '0', '1', '0', '0', '0', '0', '0', '0', ''),
('Provider', 'service provider', '30', '0', '1', '0', '0', '0', '0', '0', '0', ''),
('Provider', 'subcontractor', '40', '0', '1', '0', '0', '0', '0', '0', '0', ''),
('Provider', 'central purchasing', '50', '0', '1', '0', '0', '0', '0', '0', '0', '');

CREATE TABLE `${prefix}resolution` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `solved` int(1) unsigned DEFAULT '0',
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
INSERT INTO `${prefix}resolution` (`id`, `name`, `solved`, `color`, `sortOrder`, `idle`) VALUES 
(1, 'not resolved', 1, '#eeeeee', '10', 0),
(2, 'fixed', 1, '#00ff00', '20', 0),
(3, 'already fixed', 1, '#00ff00', '30', 0),
(4, 'duplicate', 0, '#ff0000', '40', 0),
(5, 'rejected', 0, '#ff0000', '50', 0),
(6, 'support provided', 1, '#00ff00', '60', 0),
(7, 'workaround provided', 1, '#00ff00', '70', 0),
(8, 'evolution done', 1, '#00ff00', '80', 0);

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(149, 'menuResolution', 36, 'object', 722, 'ReadWriteList', 0, 'ListOfValues ');
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 149, 1),
(2, 149, 0),
(3, 149, 0),
(4, 149, 0),
(5, 149, 0),
(6, 149, 0),
(7, 149, 0);

ALTER TABLE `${prefix}ticket` ADD COLUMN `idResolution` int(12) unsigned DEFAULT NULL,
ADD COLUMN `solved` int(1) unsigned DEFAULT '0',
ADD COLUMN `lastUpdateDateTime` datetime DEFAULT NULL;
UPDATE `${prefix}ticket` set `lastUpdateDateTime`=(select max(operationDate) from `${prefix}history` h WHERE h.refType='Ticket' and h.refId=`${prefix}ticket`.id);

ALTER TABLE `${prefix}action` ADD COLUMN `isPrivate` int(1) unsigned default 0;

ALTER TABLE `${prefix}type` ADD COLUMN `mandatoryResolutionOnDone` int(1) unsigned DEFAULT '0',
ADD COLUMN `lockSolved` int(1) unsigned DEFAULT '0';

ALTER TABLE `${prefix}project` ADD COLUMN `isUnderConstruction` int(1) unsigned DEFAULT '0';

ALTER TABLE `${prefix}product` ADD COLUMN `idResource` int(12) unsigned DEFAULT NULL;

ALTER TABLE `${prefix}expense` ADD COLUMN `paymentDone` int(1) unsigned DEFAULT '0';

ALTER TABLE `${prefix}activity` ADD COLUMN `isPlanningActivity` int(1) unsigned DEFAULT '0';

ALTER TABLE `${prefix}expensedetail` ADD COLUMN `externalReference` varchar(100) DEFAULT NULL;

DELETE FROM `${prefix}columnselector` WHERE `objectClass` in ('ProductVersion'); 

UPDATE `${prefix}activity` set `isPlanningActivity`=1 WHERE id in 
  ( select refId from `${prefix}planningelement` where refType='Activity' and workElementCount>0 );
  