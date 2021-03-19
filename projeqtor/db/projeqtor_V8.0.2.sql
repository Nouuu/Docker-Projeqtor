-- Patch on V8.0

UPDATE `${prefix}type` SET idle=1 where code='LEAVESYST' and scope ='Activity';

ALTER TABLE `${prefix}command` CHANGE `initialWork` `initialWork` DECIMAL(14,5) UNSIGNED,
 CHANGE `addWork` `addWork` DECIMAL(14,5) UNSIGNED,
 CHANGE `validatedWork` `validatedWork` DECIMAL(14,5) UNSIGNED;

