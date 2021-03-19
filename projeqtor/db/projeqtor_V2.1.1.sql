
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V2.1.1                                      //
-- // Date : 2012-04-05                                     //
-- ///////////////////////////////////////////////////////////
--
--

UPDATE `${prefix}assignment` SET realWork=0 where realWork is null;
UPDATE `${prefix}assignment` SET leftWork=0 where leftWork is null;
UPDATE `${prefix}assignment` SET plannedWork=realWork+leftWork;

UPDATE `${prefix}planningelement` SET realWork=0 where realWork is null;
UPDATE `${prefix}planningelement` SET leftWork=0 where leftWork is null;
UPDATE `${prefix}planningelement` SET plannedWork=realWork+leftWork;

ALTER TABLE `${prefix}workelement` CHANGE `plannedWork` `plannedWork` DECIMAL(9,5) UNSIGNED,
CHANGE `realWork` `realWork` DECIMAL(9,5) UNSIGNED,
CHANGE `leftWork` `leftWork` DECIMAL(9,5) UNSIGNED;

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`) VALUES 
(105,'menuContextType',79,'object',952,NULL,0);
  
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 104, 1),
(1, 105, 1);

UPDATE `${prefix}contexttype` SET name='environment' WHERE id=1;
UPDATE `${prefix}contexttype` SET name='OS' WHERE id=2;
UPDATE `${prefix}contexttype` SET name='browser' WHERE id=3;
