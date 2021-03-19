-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 4.3                                //
-- // Date : 2014-03-06                                     //
-- ///////////////////////////////////////////////////////////

UPDATE `${prefix}project` p SET 
sortOrder=(select wbsSortable from `${prefix}planningelement` pe where refType='Project' and refId=p.id);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`) VALUES
(52, 'reportAvailabilitySynthesis', 2, 'availabilitySynthesis.php', 482);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES 
(52, 'period', 'nextPeriod', 10, '10/month'),
(52,'idTeam','teamList',20,null);

INSERT INTO `${prefix}habilitationreport` (`idReport`, `idProfile`,  `allowAccess`) VALUES
(52, 1, 1),
(52, 2, 1),
(52, 3, 1);