-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.5.1 for PG only                           //
-- // Date : 2016-07-28                                     //
-- ///////////////////////////////////////////////////////////
DROP INDEX tenderstatus;
CREATE INDEX tenderStatusIndex ON `${prefix}tender` (idStatus);

CREATE TABLE `${prefix}tenderstatus` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `isWaiting` int(1) unsigned DEFAULT '0',
  `isReceived` int(1) unsigned DEFAULT '0',
  `isNotSelect` int(1) unsigned DEFAULT '0',
  `isSelected` int(1) unsigned DEFAULT '0',
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}tenderstatus` (`name`, `color`, `sortOrder`, `idle`, `isWaiting`, `isReceived`, `isNotSelect`, `isSelected`) VALUES 
('request to send',       '#ffa500', '10', '0', '0', '0', '0', '0'),
('waiting for reply',     '#f08080', '20', '0', '1', '0', '0', '0'),
('out of date answer',    '#c0c0c0', '30', '0', '0', '1', '1', '0'),
('incomplete file',       '#c0c0c0', '40', '0', '0', '1', '1', '0'),
('admissible',            '#87ceeb', '50', '0', '0', '1', '0', '0'),
('short list',            '#4169e1', '60', '0', '0', '1', '0', '0'),
('not selected',          '#c0c0c0', '70', '0', '0', '1', '1', '0'),
('selected',              '#98fb98', '80', '0', '0', '1', '0', '1');
