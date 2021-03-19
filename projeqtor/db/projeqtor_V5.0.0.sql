-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.0.0                                       //
-- // Date : 2014-11-30                                     //
-- ///////////////////////////////////////////////////////////

-- ///////////////////////////////////////////////////////////
-- Change from varchar(4000) to MediumText for editorable fields
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}accessprofile` CHANGE `description` `description` mediumtext;

ALTER TABLE `${prefix}action` CHANGE `description` `description` mediumtext,
 CHANGE `result` `result` mediumtext;

ALTER TABLE `${prefix}activity` CHANGE `description` `description` mediumtext,
 CHANGE `result` `result` mediumtext;

ALTER TABLE `${prefix}affectation` CHANGE `description` `description` mediumtext;

ALTER TABLE `${prefix}bill` CHANGE `description` `description` mediumtext;

ALTER TABLE `${prefix}client` CHANGE `description` `description` mediumtext;
 
ALTER TABLE `${prefix}command` CHANGE `description` `description` mediumtext,
 CHANGE `additionalInfo` `additionalInfo` mediumtext,
 CHANGE `comment` `comment` mediumtext;

ALTER TABLE `${prefix}contexttype` CHANGE `description` `description` mediumtext;

ALTER TABLE `${prefix}decision` CHANGE `description` `description` mediumtext;
 
ALTER TABLE `${prefix}expense` CHANGE `description` `description` mediumtext;

ALTER TABLE `${prefix}expensedetailtype` CHANGE `description` `description` mediumtext;

ALTER TABLE `${prefix}history` CHANGE `oldValue` `oldValue` mediumtext,
 CHANGE `newValue` `newValue` mediumtext;
 
ALTER TABLE `${prefix}issue` CHANGE `description` `description` mediumtext,
 CHANGE `result` `result` mediumtext,
 CHANGE `cause` `cause` mediumtext,
 CHANGE `impact` `impact` mediumtext;

ALTER TABLE `${prefix}mail` CHANGE `mailBody` `mailBody` mediumtext;
 
ALTER TABLE `${prefix}meeting` CHANGE `description` `description` mediumtext,
 CHANGE `result` `result` mediumtext;

ALTER TABLE `${prefix}message` CHANGE `description` `description` mediumtext;
 
ALTER TABLE `${prefix}milestone` CHANGE `description` `description` mediumtext,
 CHANGE `result` `result` mediumtext;

ALTER TABLE `${prefix}note` CHANGE  `note` `note` mediumtext;

ALTER TABLE `${prefix}opportunity` CHANGE `description` `description` mediumtext,
 CHANGE `result` `result` mediumtext,
 CHANGE `cause` `cause` mediumtext,
 CHANGE `impact` `impact` mediumtext;
 
ALTER TABLE `${prefix}periodicmeeting` CHANGE `description` `description` mediumtext;

ALTER TABLE `${prefix}predefinedtext` CHANGE `text` `text` mediumtext;

ALTER TABLE `${prefix}product` CHANGE `description` `description` mediumtext;
 
ALTER TABLE `${prefix}profile` CHANGE `description` `description` mediumtext;

ALTER TABLE `${prefix}project` CHANGE  `description` `description` mediumtext;

ALTER TABLE `${prefix}question` CHANGE `description` `description` mediumtext,
 CHANGE `result` `result` mediumtext;
 
ALTER TABLE `${prefix}quotation` CHANGE `description` `description` mediumtext,
 CHANGE `additionalInfo` `additionalInfo` mediumtext,
 CHANGE `comment` `comment` mediumtext;
ALTER TABLE `${prefix}quotation` ADD `result` mediumtext;

ALTER TABLE `${prefix}requirement` CHANGE `description` `description` mediumtext,
 CHANGE `result` `result` mediumtext;
 
ALTER TABLE `${prefix}resource` CHANGE `description` `description` mediumtext;
 
ALTER TABLE `${prefix}risk` CHANGE `description` `description` mediumtext,
 CHANGE `result` `result` mediumtext,
 CHANGE `cause` `cause` mediumtext,
 CHANGE `impact` `impact` mediumtext;

ALTER TABLE `${prefix}role` CHANGE `description` `description` mediumtext;

ALTER TABLE `${prefix}team` CHANGE `description` `description` mediumtext;
 
ALTER TABLE `${prefix}testcase` CHANGE `description` `description` mediumtext,
 CHANGE `prerequisite` `prerequisite` mediumtext,
 CHANGE `result` `result` mediumtext;

ALTER TABLE `${prefix}testsession` CHANGE `description` `description` mediumtext,
 CHANGE `result` `result` mediumtext;
  
ALTER TABLE `${prefix}ticket` CHANGE `description` `description` mediumtext,
 CHANGE `result` `result` mediumtext;

ALTER TABLE `${prefix}type` CHANGE `description` `description` mediumtext;

ALTER TABLE `${prefix}version` CHANGE `description` `description` mediumtext;

ALTER TABLE `${prefix}workflow` CHANGE `description` `description` mediumtext;

-- ///////////////////////////////////////////////////////////
-- Other Changes
-- ///////////////////////////////////////////////////////////

ALTER TABLE `${prefix}message` ADD `showOnLogin` int(1) unsigned DEFAULT 0;

RENAME TABLE `${prefix}attachement` TO `${prefix}attachment`;

UPDATE `${prefix}parameter` SET parameterCode='paramAttachmentDirectory' WHERE parameterCode='paramAttachementDirectory';
UPDATE `${prefix}parameter` SET parameterCode='paramAttachmentMaxSize' WHERE parameterCode='paramAttachementMaxSize';
UPDATE `${prefix}parameter` SET parameterCode='displayAttachment' WHERE parameterCode='displayAttachement';

CREATE INDEX workelementActivity ON `${prefix}workelement` (idActivity);

UPDATE `${prefix}columnselector` SET formatter='thumbName22' WHERE field in ('nameResource', 'nameUser', 'nameContact', 'nameResourceSelect');

CREATE TABLE `${prefix}menuselector` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `idle` int(1) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--INSERT INTO `${prefix}indicatorable` (`id`,`name`, `idle`) VALUES (13,'Meeting', '0');
--INSERT INTO `${prefix}indicatorableindicator` (`idIndicatorable`, `nameIndicatorable`, `idIndicator`, `idle`) VALUES 
--('13', 'Meeting', '???', '0');

INSERT INTO `${prefix}habilitationother` (idProfile,scope,rightAccess) VALUES 
(1,'canUpdateCreation','1'),
(2,'canUpdateCreation','2'),
(3,'canUpdateCreation','2'),
(4,'canUpdateCreation','2'),
(6,'canUpdateCreation','2'),
(7,'canUpdateCreation','2'),
(5,'canUpdateCreation','2');

ALTER TABLE `${prefix}affectation` ADD `idProfile` int(12) unsigned;
UPDATE `${prefix}affectation` SET idProfile=(select idProfile from `${prefix}resource` R where R.id=idResource); 

DELETE FROM `${prefix}planningelement` WHERE refName is null;

ALTER TABLE `${prefix}planningelement` ADD `marginWork` decimal(14,5),
ADD `marginCost` decimal(14,5), ADD `marginWorkPct` int(6), ADD `marginCostPct` int(6);

ALTER TABLE `${prefix}project` ADD `creationDate` datetime DEFAULT NULL,
ADD `objectives` mediumtext;
ALTER TABLE `${prefix}project` ADD `idResource` int(12) unsigned DEFAULT NULL;
UPDATE `${prefix}project` set `idResource`=`idUser`;

ALTER TABLE `${prefix}expensedetailtype` ADD `individual` int(1) unsigned DEFAULT 0,
ADD `project` int(1) unsigned DEFAULT 0;
UPDATE `${prefix}expensedetailtype` set `individual`=1;
INSERT INTO `${prefix}expensedetailtype` (id, name, sortOrder, value01, unit01, value02, unit02, value03, unit03, idle, project) VALUES
(5, 'detail', 50, null, 'units', null, '€ per unit', null, null, 0, 1);

ALTER TABLE `${prefix}document` ADD `idUser` int(12) unsigned,
ADD `creationDate` date DEFAULT NULL;
UPDATE `${prefix}document` set idUser=idAuthor;

UPDATE `${prefix}history` set refTYpe='Attachment' where refType='Attachement';

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(38,'showDoneVersions','boolean',850,0,null),
(39,'showDoneVersions','boolean',850,0,null);

ALTER TABLE `${prefix}assignment` ADD `plannedStartFraction` DECIMAL(6,5) default 0,
ADD `plannedEndFraction` DECIMAL(6,5) default 1;
ALTER TABLE `${prefix}planningelement` ADD `plannedStartFraction` DECIMAL(6,5) default 0,
ADD `plannedEndFraction` DECIMAL(6,5) default 1;
ALTER TABLE `${prefix}planningelement` ADD `validatedStartFraction` DECIMAL(6,5) default 0,
ADD `validatedEndFraction` DECIMAL(6,5) default 1;

INSERT INTO `${prefix}planningmode` (`id`, `applyTo`, `name`, `code`, `sortOrder`, `idle`, `mandatoryStartDate`, `mandatoryEndDate`) VALUES
(19, 'Activity', 'PlanningModeSTART', 'START', 130, 0 , 1, 0),
(20, 'Activity', 'PlanningModeQUART', 'QUART', 340, 0 , 1, 1);
UPDATE `${prefix}planningmode` SET code='FIXED' WHERE name='PlanningModeFIXED';

ALTER TABLE `${prefix}resource` ADD `function` VARCHAR(100) default NULL;

INSERT INTO `${prefix}list` (`id`, `list`, `name`, `code`, `sortOrder`, `idle`) VALUES
(1000001, 'readWrite', 'displayWrite', 'WRITE', 10, 0),
(1000002, 'readWrite', 'displayReadOnly', 'READ', 20, 0);

UPDATE `${prefix}menu` SET level='Project' 
where name in ('menuBill', 'menuTerm', 'menuActivityPrice');

UPDATE `${prefix}menu` SET level='ReadWriteEnvironment' 
where name in ('menuProduct', 'menuVersion', 'menuActivityPrice', 'menuUser', 'menuResource', 'menuContact', 'menuClient','menuRecipient',
'menuTeam','menuWorkflow','menuDocumentDirectory', 'menuCalendar',
'menuStatusMail', 'menuTicketDelay','menuIndicatorDefinition','menuPredefinedNote', 'menuChecklistDefinition');
UPDATE `${prefix}menu` SET level='ReadWriteType' 
where idMenu=79;
UPDATE `${prefix}menu` SET level='ReadWriteList' 
where idMenu=36;

ALTER TABLE `${prefix}menu` ADD `menuClass` varchar(400);

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(135, 'menuAccessRightNoProject', 37, 'item', 968, NULL, 0, 'HabilitationParameter');
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 135, 1),
(2, 135, 0),
(3, 135, 0),
(4, 135, 0),
(5, 135, 0),
(6, 135, 0),
(7, 135, 0);

ALTER TABLE `${prefix}work` ADD `idWorkElement` int(12) unsigned DEFAULT NULL;
CREATE INDEX workWorkelement ON `${prefix}work` (idWorkElement);
UPDATE `${prefix}work` set idWorkElement=
(select id from `${prefix}workelement` we where we.refType=`${prefix}work`.refType and we.refId=`${prefix}work`.refId);

UPDATE `${prefix}workelement` set refName=(select name from `${prefix}ticket` t where refId=t.id) where refTYpe='Ticket';
 
CREATE TABLE `${prefix}plugin` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `description` mediumtext,
  `name` varchar(100),
  `zipFile` varchar(4000),
  `deploymentDate` date DEFAULT NULL,
  `isDeployed` int(1) unsigned DEFAULT 0,
  `deploymentVersion`  varchar(100),
  `compatibilityVersion` varchar(100),
  `pluginVersion` varchar(100),
  `idle` int(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
CREATE INDEX pluginName ON `${prefix}plugin` (name);

-- ///////////////////////////////////////////////////////////
-- Menu upgrade for new contectual menu function
-- ///////////////////////////////////////////////////////////

UPDATE `${prefix}menu` SET menuClass='Work Risk RequirementTest Financial Meeting ' WHERE name='menuToday';
UPDATE `${prefix}menu` SET menuClass='Work Risk RequirementTest Financial Meeting ' WHERE name='menuProject';
UPDATE `${prefix}menu` SET menuClass='Work Risk RequirementTest Financial Meeting ' WHERE name='menuDocument';
UPDATE `${prefix}menu` SET menuClass='Work Risk Meeting ' WHERE name='menuWork';
UPDATE `${prefix}menu` SET menuClass='Work ' WHERE name='menuTicket';
UPDATE `${prefix}menu` SET menuClass='Work ' WHERE name='menuTicketSimple';
UPDATE `${prefix}menu` SET menuClass='Work ' WHERE name='menuActivity';
UPDATE `${prefix}menu` SET menuClass='Work ' WHERE name='menuMilestone';
UPDATE `${prefix}menu` SET menuClass='Work Risk Meeting ' WHERE name='menuAction';
UPDATE `${prefix}menu` SET menuClass='Work Risk RequirementTest Financial ' WHERE name='menuFollowup';
UPDATE `${prefix}menu` SET menuClass='Work ' WHERE name='menuImputation';
UPDATE `${prefix}menu` SET menuClass='Work ' WHERE name='menuPlanning';
UPDATE `${prefix}menu` SET menuClass='Work ' WHERE name='menuPortfolioPlanning';
UPDATE `${prefix}menu` SET menuClass='Work ' WHERE name='menuResourcePlanning';
UPDATE `${prefix}menu` SET menuClass='Work ' WHERE name='menuDiary';
UPDATE `${prefix}menu` SET menuClass='Work Risk RequirementTest Financial ' WHERE name='menuReports';
UPDATE `${prefix}menu` SET menuClass='Work RequirementTest ' WHERE name='menuRequirementTest';
UPDATE `${prefix}menu` SET menuClass='RequirementTest ' WHERE name='menuRequirement';
UPDATE `${prefix}menu` SET menuClass='RequirementTest ' WHERE name='menuTestCase';
UPDATE `${prefix}menu` SET menuClass='RequirementTest ' WHERE name='menuTestSession';
UPDATE `${prefix}menu` SET menuClass='Work Financial ' WHERE name='menuFinancial';
UPDATE `${prefix}menu` SET menuClass='Work Financial ' WHERE name='menuIndividualExpense';
UPDATE `${prefix}menu` SET menuClass='Financial ' WHERE name='menuProjectExpense';
UPDATE `${prefix}menu` SET menuClass='Financial ' WHERE name='menuQuotation';
UPDATE `${prefix}menu` SET menuClass='Financial ' WHERE name='menuCommand';
UPDATE `${prefix}menu` SET menuClass='Financial ' WHERE name='menuTerm';
UPDATE `${prefix}menu` SET menuClass='Financial ' WHERE name='menuBill';
UPDATE `${prefix}menu` SET menuClass='Financial ' WHERE name='menuPayment';
UPDATE `${prefix}menu` SET menuClass='Financial ' WHERE name='menuActivityPrice';
UPDATE `${prefix}menu` SET menuClass='Risk ' WHERE name='menuRiskManagementPlan';
UPDATE `${prefix}menu` SET menuClass='Risk ' WHERE name='menuRisk';
UPDATE `${prefix}menu` SET menuClass='Risk ' WHERE name='menuOpportunity';
UPDATE `${prefix}menu` SET menuClass='Risk ' WHERE name='menuIssue';
UPDATE `${prefix}menu` SET menuClass='Work Meeting ' WHERE name='menuReview';
UPDATE `${prefix}menu` SET menuClass='Work Meeting ' WHERE name='menuMeeting';
UPDATE `${prefix}menu` SET menuClass='Meeting ' WHERE name='menuPeriodicMeeting';
UPDATE `${prefix}menu` SET menuClass='Meeting ' WHERE name='menuDecision';
UPDATE `${prefix}menu` SET menuClass='Meeting ' WHERE name='menuQuestion';
UPDATE `${prefix}menu` SET menuClass='Admin ' WHERE name='menuTool';
UPDATE `${prefix}menu` SET menuClass='Admin ' WHERE name='menuRequestor';
UPDATE `${prefix}menu` SET menuClass='Admin ' WHERE name='menuMail';
UPDATE `${prefix}menu` SET menuClass='Admin ' WHERE name='menuAlert';
UPDATE `${prefix}menu` SET menuClass='Admin ' WHERE name='menuMessage';
UPDATE `${prefix}menu` SET menuClass='Admin ' WHERE name='menuImportData';
UPDATE `${prefix}menu` SET menuClass='Work Financial EnvironmentalParameter ' WHERE name='menuEnvironmentalParameter';
UPDATE `${prefix}menu` SET menuClass='EnvironmentalParameter ' WHERE name='menuProduct';
UPDATE `${prefix}menu` SET menuClass='EnvironmentalParameter ' WHERE name='menuVersion';
UPDATE `${prefix}menu` SET menuClass='EnvironmentalParameter ' WHERE name='menuAffectation';
UPDATE `${prefix}menu` SET menuClass='EnvironmentalParameter ' WHERE name='menuContext';
UPDATE `${prefix}menu` SET menuClass='EnvironmentalParameter HabilitationParameter ' WHERE name='menuUser';
UPDATE `${prefix}menu` SET menuClass='Work EnvironmentalParameter ' WHERE name='menuResource';
UPDATE `${prefix}menu` SET menuClass='Financial EnvironmentalParameter ' WHERE name='menuContact';
UPDATE `${prefix}menu` SET menuClass='Financial EnvironmentalParameter ' WHERE name='menuClient';
UPDATE `${prefix}menu` SET menuClass='Financial EnvironmentalParameter ' WHERE name='menuRecipient';
UPDATE `${prefix}menu` SET menuClass='EnvironmentalParameter ' WHERE name='menuTeam';
UPDATE `${prefix}menu` SET menuClass='EnvironmentalParameter ' WHERE name='menuDocumentDirectory';
UPDATE `${prefix}menu` SET menuClass='Work EnvironmentalParameter ' WHERE name='menuCalendar';
UPDATE `${prefix}menu` SET menuClass='Automation ' WHERE name='menuAutomation';
UPDATE `${prefix}menu` SET menuClass='Automation ' WHERE name='menuWorkflow';
UPDATE `${prefix}menu` SET menuClass='Automation ' WHERE name='menuStatusMail';
UPDATE `${prefix}menu` SET menuClass='Automation ' WHERE name='menuTicketDelay';
UPDATE `${prefix}menu` SET menuClass='Automation ' WHERE name='menuIndicatorDefinition';
UPDATE `${prefix}menu` SET menuClass='Automation ' WHERE name='menuPredefinedNote';
UPDATE `${prefix}menu` SET menuClass='Automation ' WHERE name='menuChecklistDefinition';
UPDATE `${prefix}menu` SET menuClass='Work Risk RequirementTest Financial Meeting Admin Automation EnvironmentalParameter ListOfValues Type HabilitationParameter ' WHERE name='menuParameter';
UPDATE `${prefix}menu` SET menuClass='ListOfValues ' WHERE name='menuListOfValues';
UPDATE `${prefix}menu` SET menuClass='ListOfValues ' WHERE name='menuRole';
UPDATE `${prefix}menu` SET menuClass='ListOfValues ' WHERE name='menuStatus';
UPDATE `${prefix}menu` SET menuClass='ListOfValues ' WHERE name='menuQuality';
UPDATE `${prefix}menu` SET menuClass='ListOfValues ' WHERE name='menuHealth';
UPDATE `${prefix}menu` SET menuClass='ListOfValues ' WHERE name='menuOverallProgress';
UPDATE `${prefix}menu` SET menuClass='ListOfValues ' WHERE name='menuTrend';
UPDATE `${prefix}menu` SET menuClass='ListOfValues ' WHERE name='menuLikelihood';
UPDATE `${prefix}menu` SET menuClass='ListOfValues ' WHERE name='menuCriticality';
UPDATE `${prefix}menu` SET menuClass='ListOfValues ' WHERE name='menuSeverity';
UPDATE `${prefix}menu` SET menuClass='ListOfValues ' WHERE name='menuUrgency';
UPDATE `${prefix}menu` SET menuClass='ListOfValues ' WHERE name='menuPriority';
UPDATE `${prefix}menu` SET menuClass='ListOfValues ' WHERE name='menuRiskLevel';
UPDATE `${prefix}menu` SET menuClass='ListOfValues ' WHERE name='menuFeasibility';
UPDATE `${prefix}menu` SET menuClass='ListOfValues ' WHERE name='menuEfficiency';
UPDATE `${prefix}menu` SET menuClass='' WHERE name='menuType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuProjectType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuTicketType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuActivityType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuMilestoneType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuQuotationType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuCommandType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuIndividualExpenseType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuProjectExpenseType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuExpenseDetailType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuBillType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuPaymentType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuRiskType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuInvoiceType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuOpportunityType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuActionType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuIssueType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuMeetingType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuDecisionType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuQuestionType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuMessageType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuDocumentType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuContextType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuRequirementType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuTestCaseType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuTestSessionType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuClientType';
UPDATE `${prefix}menu` SET menuClass='Type ' WHERE name='menuHabilitationParameter';
UPDATE `${prefix}menu` SET menuClass='HabilitationParameter ' WHERE name='menuProfile';
UPDATE `${prefix}menu` SET menuClass='HabilitationParameter ' WHERE name='menuAccessProfile';
UPDATE `${prefix}menu` SET menuClass='HabilitationParameter ' WHERE name='menuHabilitation';
UPDATE `${prefix}menu` SET menuClass='HabilitationParameter ' WHERE name='menuHabilitationReport';
UPDATE `${prefix}menu` SET menuClass='HabilitationParameter ' WHERE name='menuAccessRight';
UPDATE `${prefix}menu` SET menuClass='HabilitationParameter ' WHERE name='menuAccessRightNoProject';
UPDATE `${prefix}menu` SET menuClass='HabilitationParameter ' WHERE name='menuHabilitationOther';
UPDATE `${prefix}menu` SET menuClass='Admin ' WHERE name='menuAdmin';
UPDATE `${prefix}menu` SET menuClass='Admin ' WHERE name='menuAudit';
UPDATE `${prefix}menu` SET menuClass='Admin EnvironmentalParameter HabilitationParameter ' WHERE name='menuGlobalParameter';
UPDATE `${prefix}menu` SET menuClass='' WHERE name='menuProjectParameter';
UPDATE `${prefix}menu` SET menuClass='Work Risk RequirementTest Financial Meeting Admin Automation EnvironmentalParameter ListOfValues Type HabilitationParameter ' WHERE name='menuUserParameter';
