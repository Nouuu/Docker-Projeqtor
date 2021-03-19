-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 4.3                                //
-- // Date : 2014-03-06                                     //
-- ///////////////////////////////////////////////////////////

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES 
(133, 'menuDiary', '7', 'item', '145', null, 0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 133, 1),
(2, 133, 1),
(3, 133, 1),
(4, 133, 1),
(5, 133, 0),
(6, 133, 0),
(7, 133, 0);

INSERT INTO `${prefix}habilitationother` (idProfile,scope,rightAccess) VALUES 
(1,'diary','4'),
(2,'diary','2'),
(3,'diary','3'),
(4,'diary','2'),
(6,'diary','1'),
(7,'diary','1'),
(5,'diary','1');

INSERT INTO `${prefix}habilitationother` (idProfile,scope,rightAccess) VALUES 
(1,'resourcePlanning','1'),
(2,'resourcePlanning','1'),
(3,'resourcePlanning','1'),
(4,'resourcePlanning','2'),
(6,'resourcePlanning','2'),
(7,'resourcePlanning','2'),
(5,'resourcePlanning','2');

UPDATE `${prefix}project` p SET 
sortOrder=(select wbsSortable from `${prefix}planningelement` pe where refType='Project' and refId=p.id);

INSERT INTO `${prefix}type` (`scope`, `name`, `sortOrder`, `idle`, `idWorkflow`, `mandatoryDescription`, `mandatoryResultOnDone`, `mandatoryResourceOnHandled`, `lockHandled`, `lockDone`, `lockIdle`, `code`) VALUES 
('Client', 'business prospect', '10', '0', '1', '0', '0', '0', '0', '1', '1', ''),
('Client', 'customer', '10', '0', '1', '0', '0', '0', '0', '1', '1', '');

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `idle`) VALUES 
(134, 'menuClientType', '79', 'object', '925', 0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 134, 1),
(2, 134, 0),
(3, 134, 0),
(4, 134, 0),
(5, 134, 0),
(6, 134, 0),
(7, 134, 0);

ALTER TABLE `${prefix}client` ADD COLUMN `idClientType` int(12) unsigned DEFAULT NULL;
CREATE INDEX clientClientType ON `${prefix}client` (idClientType);