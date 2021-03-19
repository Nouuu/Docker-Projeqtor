
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V1.7.0                                      //
-- // Date : 2010-07-25                                     //
-- ///////////////////////////////////////////////////////////
--
--
CREATE INDEX expenseProject ON `${prefix}expense` (idProject);
CREATE INDEX expenseType ON `${prefix}expense` (idExpenseType);
CREATE INDEX expenseUser ON `${prefix}expense` (idUser);
CREATE INDEX expenseResource ON `${prefix}expense` (idResource);
CREATE INDEX expenseStatus ON `${prefix}expense` (idStatus);
CREATE INDEX expenseDay ON `${prefix}expense` (day);
CREATE INDEX expenseWeek ON `${prefix}expense` (week);
CREATE INDEX expenseMonth ON `${prefix}expense` (month);
CREATE INDEX expenseYear ON `${prefix}expense` (year);

CREATE INDEX expensedetailProject ON `${prefix}expensedetail` (idProject);
CREATE INDEX expensedetailExpenseDetailType ON `${prefix}expensedetail` (idExpenseDetailType);
CREATE INDEX expensedetailExpense ON `${prefix}expensedetail` (idExpense);

CREATE INDEX habilitationotherProfile ON `${prefix}habilitationother` (idProfile);

CREATE INDEX listList ON `${prefix}list` (list);

CREATE INDEX planningmodeApplyTo ON `${prefix}planningmode` (applyTo);

CREATE INDEX resourcecostResource ON `${prefix}resourcecost` (idResource);

CREATE INDEX userIsResource ON `${prefix}resource` (isResource);
CREATE INDEX userIsUser ON `${prefix}resource` (isUser);
CREATE INDEX userIsContact ON `${prefix}resource` (isContact);

CREATE TABLE `${prefix}calendar` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `isOffDay` int(1) unsigned DEFAULT '0',
  `calendarDate` date DEFAULT NULL,
  `day`  varchar(8),
  `week` varchar(6),
  `month` varchar(6),
  `year` varchar(4),
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE INDEX calendarDay ON `${prefix}calendar` (day);
CREATE INDEX calendarWeek ON `${prefix}calendar` (week);
CREATE INDEX calendarMonth ON `${prefix}calendar` (month);
CREATE INDEX calendarYear ON `${prefix}calendar` (year);

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES
(85, 'menuCalendar', 14, 'object', 685, Null, 0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 85, 1),
(2, 85, 1),
(3, 85, 1);

CREATE TABLE `${prefix}origin` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `originType` varchar(100) default NULL,
  `originId` int(12) unsigned default NULL,
  `refType` varchar(100),
  `refId` int(12) unsigned,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE INDEX originOrigin ON `${prefix}origin` (originType, originId);
CREATE INDEX originRef ON `${prefix}origin` (refType, refId);

CREATE TABLE `${prefix}originable` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}originable` (`id`, `name`, `idle`) VALUES
(1, 'Ticket', 0),
(2, 'Activity', 0),
(3, 'Milestone', 0),
(4, 'IndividualExpense', 0),
(5, 'ProjectExpense', 0),
(6, 'Risk', 0),
(7, 'Action', 0),
(8, 'Issue', 0),
(9, 'Meeting', 0),
(10, 'Decision', 0),
(11, 'Question', 0);

CREATE TABLE `${prefix}copyable` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  `sortOrder` int(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}copyable` (`id`, `name`, `idle`, `sortOrder`) VALUES
(1, 'Ticket', 0, 10),
(2, 'Activity', 0, 20),
(3, 'Milestone', 0, 30),
(4, 'IndividualExpense', 0, 40),
(5, 'ProjectExpense', 0, 50),
(6, 'Risk', 0, 60),
(7, 'Action', 0, 70),
(8, 'Issue', 0, 80),
(9, 'Meeting', 0, 90),
(10, 'Decision', 0, 100),
(11, 'Question', 0, 110);

CREATE TABLE `${prefix}product` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `idClient` int(12) unsigned DEFAULT NULL,
  `idContact` int(12) unsigned DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `creationDate` date DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE INDEX productClient ON `${prefix}product` (idClient);
CREATE INDEX pruductContact ON `${prefix}product` (idContact);

CREATE TABLE `${prefix}version` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProduct` int(12) unsigned DEFAULT NULL,
  `idContact` int(12) unsigned DEFAULT NULL,
  `idResource` int(12) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `creationDate` date DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE INDEX versionProduct ON `${prefix}version` (idProduct);
CREATE INDEX versionContact ON `${prefix}version` (idContact);
CREATE INDEX versionResource ON `${prefix}version` (idResource);

CREATE TABLE `${prefix}versionproject` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `idVersion` int(12) unsigned DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE INDEX versionprojectProject ON `${prefix}versionproject` (idProject);
CREATE INDEX versionprojectVersion ON `${prefix}versionproject` (idVersion);

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES
(86, 'menuProduct', 14, 'object', 642, Null, 0),
(87, 'menuVersion', 14, 'object', 644, Null, 0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 86, 1),
(2, 86, 1),
(3, 86, 1),
(1, 87, 1),
(2, 87, 1),
(3, 87, 1);

ALTER TABLE `${prefix}project` ADD `idContact` int(12) unsigned DEFAULT NULL;
CREATE INDEX projectContact ON `${prefix}project` (idContact);

ALTER TABLE `${prefix}plannedwork` CHANGE `work` `work` DECIMAL(5,2) UNSIGNED;

ALTER TABLE `${prefix}ticket` ADD `idVersion` int(12) unsigned DEFAULT NULL,
ADD `idOriginalVersion` int(12) unsigned DEFAULT NULL;

ALTER TABLE `${prefix}activity` ADD `idVersion` int(12) unsigned DEFAULT NULL;

ALTER TABLE `${prefix}milestone` ADD `idVersion` int(12) unsigned DEFAULT NULL;

ALTER TABLE `${prefix}affectation` ADD `idContact` int(12) unsigned DEFAULT NULL,
ADD `idUser` int(12) unsigned DEFAULT NULL;
