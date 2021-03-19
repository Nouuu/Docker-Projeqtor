-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 4.4.0                                       //
-- // Date : 2014-07-16                                     //
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}resource` ADD COLUMN `apiKey` varchar(400) DEFAULT NULL;

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `idle`) VALUES 
(53,'reportProductTestDetail',8,'productTestDetail.php',825,0);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(53,'idProject','projectList',10,0,null),
(53,'idProduct','productList',20,0,null),
(53,'idVersion','versionList',30,0,null);

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES
(1,53,1),
(2,53,1),
(3,53,1),
(4,53,0),
(5,53,0),
(6,53,0),
(7,53,0);

INSERT INTO `${prefix}mailable` (`id`,`name`, `idle`) VALUES 
(23,'Product', '0'),
(24,'Version', '0');

ALTER TABLE `${prefix}planningelement` ADD COLUMN `validatedCalculated` int(1) unsigned DEFAULT 0;

ALTER TABLE `${prefix}planningelement` ADD COLUMN `workElementEstimatedWork` DECIMAL(9,5) UNSIGNED;
ALTER TABLE `${prefix}planningelement` ADD COLUMN `workElementRealWork` DECIMAL(9,5) UNSIGNED;
ALTER TABLE `${prefix}planningelement` ADD COLUMN `workElementLeftWork` DECIMAL(9,5) UNSIGNED;
ALTER TABLE `${prefix}planningelement` ADD COLUMN `workElementCount` DECIMAL(5) UNSIGNED;

UPDATE `${prefix}planningelement` PE SET 
workElementEstimatedWork = (select sum(plannedWork) from  `${prefix}workelement` WE where WE.idActivity=PE.refId),
workElementRealWork = (select sum(RealWork) from  `${prefix}workelement` WE where WE.idActivity=PE.refId), 
workelementLeftWork = (select sum(LeftWork) from  `${prefix}workelement` WE where WE.idActivity=PE.refId),
workElementCount = (select count(*) from  `${prefix}workelement` WE where WE.idActivity=PE.refId)
WHERE PE.refType='Activity';      

ALTER TABLE `${prefix}planningelement` ADD COLUMN `expenseAssignedAmount` DECIMAL(11,2) UNSIGNED;
ALTER TABLE `${prefix}planningelement` ADD COLUMN `expensePlannedAmount` DECIMAL(11,2) UNSIGNED;
ALTER TABLE `${prefix}planningelement` ADD COLUMN `expenseRealAmount` DECIMAL(11,2) UNSIGNED;
ALTER TABLE `${prefix}planningelement` ADD COLUMN `expenseLeftAmount` DECIMAL(11,2) UNSIGNED;
ALTER TABLE `${prefix}planningelement` ADD COLUMN `expenseValidatedAmount` DECIMAL(11,2) UNSIGNED;
ALTER TABLE `${prefix}planningelement` ADD COLUMN `totalAssignedCost` DECIMAL(11,2) UNSIGNED;
ALTER TABLE `${prefix}planningelement` ADD COLUMN `totalPlannedCost` DECIMAL(11,2) UNSIGNED;
ALTER TABLE `${prefix}planningelement` ADD COLUMN `totalRealCost` DECIMAL(11,2) UNSIGNED;
ALTER TABLE `${prefix}planningelement` ADD COLUMN `totalLeftCost` DECIMAL(11,2) UNSIGNED;
ALTER TABLE `${prefix}planningelement` ADD COLUMN `totalValidatedCost` DECIMAL(11,2) UNSIGNED;
UPDATE `${prefix}planningelement` PE SET 
expenseAssignedAmount = (select sum(plannedAmount) from `${prefix}expense` EXP where EXP.idProject=PE.refId),
expenseRealAmount = (select sum(realAmount) from `${prefix}expense` EXP where EXP.idProject=PE.refId), 
expenseLeftAmount = (select sum(plannedAmount) from `${prefix}expense` EXP where EXP.idProject=PE.refId and EXP.realAmount is null),
expensePlannedAmount = expenseRealAmount + expenseLeftAmount
WHERE PE.refType='Project';      

UPDATE `${prefix}planningelement` PE SET 
totalAssignedCost=expenseAssignedAmount+assignedCost,
totalPlannedCost=expensePlannedAmount+plannedCost,
totalRealCost=expenseRealAmount+realCost,
totalLeftCost=expenseLeftAmount+leftCost,
totalValidatedCost=expenseValidatedAmount+validatedCost
WHERE PE.refType='Project';

ALTER TABLE `${prefix}resource` ADD COLUMN `dontReceiveTeamMails` int(1) unsigned DEFAULT 0;