ALTER TABLE `${prefix}planningelementbaseline`
ADD `isManualProgress` int(1) unsigned DEFAULT '0';

ALTER TABLE `${prefix}resource` 
CHANGE `capacity` `capacity` DECIMAL (8,5);