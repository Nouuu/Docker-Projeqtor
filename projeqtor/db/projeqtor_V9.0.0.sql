-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 9.0.0                                      //
-- // Date : 2020-09-29                                     //
-- ///////////////////////////////////////////////////////////

CREATE TABLE `${prefix}navigation` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT COMMENT '12',
  `name` varchar(200) DEFAULT NULL,
  `idParent` int(12)  unsigned DEFAULT NULL COMMENT '12',
  `idMenu` int(12)  unsigned DEFAULT NULL COMMENT '12',
  `idReport` int(12)  unsigned DEFAULT NULL COMMENT '12',
  `sortOrder` int(3) unsigned DEFAULT NULL COMMENT '3',
  `tag` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}navigation` (`id`, `name`, `idParent`, `idMenu`,`sortOrder`,`idReport`) VALUES
-- 0 ------------------------------------------------------ root level
(9,'menuToday',0,1,10,0),
(1,'navPlanning',0,0,20,0),
(2,'navTicketing',0,0,30,0),
(3,'navFollowup',0,0,40,0),
(5,'navSteering',0,0,50,0),
(4,'navFinancial',0,0,60,0),
(18,'navConfigurationManagement',0,0,70,0),
(112,'navHumanResourceNav',0,0,80,0),
(128,'navEnvironmentNav',0,0,90,0),
(7,'navTool',0,0,100,0),
(6,'navReports',0,0,110,0),
(8,'navAdministration',0,0,120,0),
(300,'navPlugin',0,0,130,0),
-- 1 ------------------------------------------------------ Planning
(20,'menuProject',1,16,10,0),
(21,'menuActivity',1,25,20,0),
(22,'menuMilestone',1,26,30,0),
(23,'menuMeeting',1,62,40,0),
(302,'menuPeriodicMeeting',1,124,50,0),
(303,'menuTestSession',1,113,60,0),
(24,'menuPlanning',1,9,70,0),
(25,'menuPlannedWorkManual',1,252,80,0),
(310,'menuKanban',1,100006001,90,0),
(10,'navPlanningView',1,0,100,0),
-- 1 - 10 ------------------------------------------------- Plannings, vues de type Gantt
(260,'menuPlanning',10,9,10,0),
(26,'menuPortfolioPlanning',10,123,20,0),
(28,'menuResourcePlanning',10,106,30,0),
(27,'menuGlobalPlanning',10,196,40,0),
(306,'menuVersionsPlanning',10,179,70,0),
(307,'menuVersionsComponentPlanning',10,227,80,0),
(308,'menuGanttSupplierContract',10,232,90,0),
(309,'menuGanttClientContract',10,235,100,0),
-- 2 ------------------------------------------------------ Ticketing
(304,'menuProject',2,16,10,0),
(32,'menuTicket',2,22,20,0),
(33,'menuTicketSimple',2,118,30,0),
(311,'menuAction',2,4,40,0),
(34,'menuKanban',2,100006001,50,0),
(31,'menuDashboardTicket',2,150,60,0),
(11,'navIndicators',2,0,70,0),
-- 2 - 11 ------------------------------------------------- Indicators
(35,'menuTicketDelay',11,89,10,0),
(36,'menuTicketDelayPerProject',11,182,20,0),
(37,'menuIndicatorDefinition',11,90,30,0),
(38,'menuIndicatorDefinitionPerProject',11,181,40,0),
-- 3 ------------------------------------------------------ Followup
(305,'menuProject',3,16,10,0),
(39,'menuImputation',3,8,20,0),
(40,'menuAbsence',3,203,30,0),
(12,'navLeaveSystem',3,0,40,0),
(41,'menuImputationValidation',3,204,50,0),
(253,'menuConsultationValidation',3,254,60,0),
(258,'menuPlannedWorkManual',3,252,70,0),
(42,'menuConsultationPlannedWorkManual',3,253,80,0),
(30,'menuGlobalView',3,192,90,0),
(44,'menuDiary',3,133,100,0),
(250,'menuActivityStream',3,177,110,0),
-- 3 - 12 ------------------------------------------------- Followup - Leaves
(45,'menuLeaveCalendar',12,209,10,0),
(46,'menuLeave',12,210,20,0),
(47,'menuDashboardEmployeeManager',12,215,30,0),
-- 4 ------------------------------------------------------ Financials
(68,'menuHierarchicalBudget',4,233,10,0),
(315,'menuProjectSituation',4,245,20,0),
(13,'navExpenses',4,0,30,0),
(14,'navIncomes',4,0,40,0),
(15,'navSituationNav',4,0,50,0),
(69,'menuGanttSupplierContract',4,232,60,0),
(70,'menuGanttClientContract',4,235,70,0),
-- 4 - 13 ------------------------------------------------- Financial Expenses
(48,'menuBudget',13,197,10,0),
(57,'menuProjectExpense',13,76,20,0),
(56,'menuIndividualExpense',13,75,30,0),
(50,'menuCallForTender',13,153,40,0),
(51,'menuTender',13,154,50,0),
(52,'menuProviderOrder',13,191,60,0),
(54,'menuProviderBill',13,194,70,0),
(53,'menuProviderTerm',13,195,80,0),
(55,'menuProviderPayment',13,201,90,0),
(49,'menuSupplierContract',13,228,100,0),
-- 4 - 14 ------------------------------------------------- Financial Incomes
(59,'menuQuotation',14,131,10,0),
(60,'menuCommand',14,125,20,0),
(61,'menuTerm',14,96,30,0),
(62,'menuBill',14,97,40,0),
(63,'menuPayment',14,78,50,0),
(65,'menuGallery',14,146,60,0),
(64,'menuActivityPrice',14,94,70,0),
(66,'menuCatalog',14,174,80,0),
(67,'menuCatalogUO',14,255,90,0),
(58,'menuClientContract',14,234,100,0),
-- 4 - 14 ------------------------------------------------- Financial Situation
(71,'menuProjectSituation',15,245,10,0),
(72,'menuProjectSituationExpense',15,246,20,0),
(73,'menuProjectSituationIncome',15,247,30,0),
(325,'menuPredefinedSituation',15,249,40,0),
-- 5 ------------------------------------------------------ Steering
(74,'menuMeeting',5,62,10,0),
(77,'menuPeriodicMeeting',5,124,20,0),
(249,'menuAction',5,4,30,0),
(82,'menuChangeRequest',5,225,40,0),
(75,'menuDecision',5,63,50,0),
(76,'menuQuestion',5,64,60,0),
(312,'navDelivery',5,0,70,0),
(16,'navRiskManagement',5,0,80,0),
(17,'navRequirementsManagement',5,0,90,0),
(19,'navAssetManagement',5,0,100,0),
-- 5 - 312 ------------------------------------------------ Steering - Delivery
(80,'menuIncoming',312,168,10,0),
(78,'menuDeliverable',312,167,20,0),
(81,'menuDelivery',312,176,30,0),
(313,'menuKpiDefinition',312,169,80,0),
-- 5 - 16  ------------------------------------------------ Steering - Risk
(83,'menuRisk',16,3,10,0),
(84,'menuIssue',16,5,20,0),
(85,'menuOpportunity',16,119,30,0),
(314,'menuAction',16,4,40,0),
(333,'reportRiskManagementPlan',16,0,50,23),
-- 5 - 17  ------------------------------------------------ Steering - Requirement
(86,'menuRequirement',17,111,10,0),
(87,'menuTestCase',17,112,20,0),
(88,'menuTestSession',17,113,30,0),
(89,'menuDashboardRequirement',17,189,40,0),
-- 5 - 19  ------------------------------------------------ Steering - Assets
(96,'menuAsset',19,237,10,0),
(97,'menuLocation',19,238,20,0),
(98,'menuBrand',19,239,30,0),
(99,'menuModel',19,240,40,0),
(100,'menuAssetCategory',19,241,50,0),
(101,'menuAssetType',19,248,60,0),
-- 18 ----------------------------------------------------- Produit
(90,'menuProduct',18,86,10,0),
(91,'menuProductVersion',18,87,20,0),
(92,'menuComponent',18,141,30,0),
(93,'menuComponentVersion',18,142,40,0),
(94,'menuVersionsPlanning',18,179,50,0),
(95,'menuVersionsComponentPlanning',18,227,60,0),
-- 112 ---------------------------------------------------- Leave System
(113,'menuLeaveCalendar',112,209,10,0),
(114,'menuLeave',112,210,20,0),
(115,'menuEmployeeLeaveEarned',112,211,30,0),
(116,'menuEmploymentContract',112,213,40,0),
(117,'menuEmployeeManager',112,214,50,0),
(118,'menuDashboardEmployeeManager',112,215,60,0),
(119,'navParameter',112,0,70,0),
-- 112 ---------------------------------------------------- Leave System - Parameters
(120,'menuLeaveType',119,217,10,0),
(121,'menuEmploymentContractType',119,218,20,0),
(122,'menuEmploymentContractEndReason',119,219,30,0),
(123,'menuLeavesSystemHabilitation',119,220,40,0),
-- 128 ---------------------------------------------------- Environment
(252,'menuOrganization',128,158,10,0),
(136,'menuUser',128,17,20,0),
(137,'menuResource',128,44,30,0),
(147,'menuResourceTeam',128,188,40,0),
(148,'menuEmployee',128,212,50,0),
(141,'menuContact',128,72,60,0),
(135,'menuClient',128,15,70,0),
(146,'menuProvider',128,148,80,0),
(139,'menuAffectation',128,50,90,0),
(140,'menuTeam',128,57,100,0),
(143,'menuRecipient',128,95,110,0),
(145,'menuContext',128,104,120,0),
(144,'menuDocumentDirectory',128,103,130,0),
(142,'menuCalendar',128,85,140,0),
-- 7 ------------------------------------------------------ Tools
(248,'menuDocument',7,102,10,0),
(317,'menuDocumentDirectory',7,103,20,0),
(103,'menuImportData',7,58,30,0),
(102,'menuMessage',7,51,40,0),
(111,'menuMessageLegal',7,223,50,0),
(104,'menuMail',7,69,60,0),
(108,'menuMailToSend',7,187,70,0),
(105,'menuAlert',7,91,80,0),
(107,'menuNotification',7,185,90,0),
(109,'menuAutoSendReport',7,205,100,0),
(110,'menuDataCloning',7,222,110,0),
(318,'menuAudit',7,122,120,0),
-- 8 ------------------------------------------------------ Configuration
(126,'menuAdmin',8,92,10,0),
(106,'menuAudit',8,122,20,0),
(125,'menuUserParameter',8,20,30,0),
(124,'menuGlobalParameter',8,18,40,0),
(319,'menuModule',8,221,50,0),
(130,'navHabilitationParameter',8,0,60,0),
(129,'navAutomation',8,0,70,0),
(131,'navListOfValues',8,0,80,0),
(132,'navType',8,0,90,0),
(133,'navHumanResourceParameters',8,0,100,0),
(134,'menuDataCloningParameter',8,224,110,0),
-- 8 - 130 ------------------------------------------------ Configuration - Habilitation
(170,'menuModule',130,221,10,0),
(166,'menuProfile',130,49,20,0),
(163,'menuHabilitation',130,21,30,0),
(164,'menuAccessProfile',130,47,40,0),
(165,'menuAccessRight',130,48,50,0),
(171,'menuAccessProfileNoProject',130,256,60,0),
(169,'menuAccessRightNoProject',130,135,70,0),
(167,'menuHabilitationReport',130,70,80,0),
(168,'menuHabilitationOther',130,71,90,0),
-- 8 - 129 ------------------------------------------------ Configuration - Automation
(149,'menuWorkflow',129,59,10,0),
(160,'menuEmailTemplate',129,184,20,0),
(150,'menuStatusMail',129,68,30,0),
(157,'menuStatusMailPerProject',129,180,40,0),
(162,'menuInputMailbox',129,250,50,0),
(156,'menuKpiDefinition',129,169,50,0),
(151,'menuTicketDelay',129,89,60,0),
(159,'menuTicketDelayPerProject',129,182,70,0),
(152,'menuIndicatorDefinition',129,90,80,0),
(158,'menuIndicatorDefinitionPerProject',129,181,90,0),
(161,'menuNotificationDefinition',129,186,100,0),
(153,'menuPredefinedNote',129,116,110,0),
(154,'menuChecklistDefinition',129,130,120,0),
(155,'menuJoblistDefinition',129,162,130,0),
-- 8 - 131 ------------------------------------------------ Configuration - Lists of values
-- (320,'navGlobal',131,0,10,0),
(321,'navQuality',131,0,70,0),
(322,'navSteering',131,0,80,0),
(323,'navFinancial',131,0,90,0),
(324,'navDelivery',131,0,100,0),
-- 8 - 131 - 320 ------------------------------------------ Configuration - Lists of values - Global
(173,'menuStatus',131,34,10,0),
(172,'menuRole',131,73,20,0),
(194,'menuCategory',131,170,30,0),
(174,'menuResolution',131,149,40,0),
(199,'menuLanguage',131,178,50,0),
(247,'menuInterventionMode',131,251,60,0),
-- 8 - 131 - 321 ------------------------------------------ Configuration - Lists of values - Quality
(175,'menuQuality',321,128,10,0),
(176,'menuHealth',321,121,20,0),
(177,'menuOverallProgress',321,127,30,0),
(178,'menuTrend',321,129,40,0),
(179,'menuLikelihood',321,39,50,0),
(180,'menuCriticality',321,40,60,0),
(181,'menuSeverity',321,38,70,0),
(182,'menuUrgency',321,42,80,0),
(183,'menuPriority',321,41,90,0),
-- 8 - 131 - 322 ------------------------------------------ Configuration - Lists of values - Steering
(184,'menuRiskLevel',322,114,130,0),
(185,'menuFeasibility',322,115,140,0),
(186,'menuEfficiency',322,117,150,0),
-- 8 - 131 - 323 ------------------------------------------ Configuration - Lists of values - Financial
(187,'menuPaymentDelay',323,137,160,0),
(188,'menuPaymentMode',323,138,170,0),
(189,'menuDeliveryMode',323,139,180,0),
(190,'menuMeasureUnit',323,140,190,0),
(191,'menuBudgetOrientation',323,199,200,0),
(192,'menuBudgetCategory',323,200,210,0),
(193,'menuTenderStatus',323,157,220,0),
(245,'menuRenewal',323,231,290,0),
(246,'menuPredefinedSituation',323,249,300,0),
-- 8 - 131 - 324 ------------------------------------------ Configuration - Lists of values - Delivery
(195,'menuIncomingWeight',324,171,240,0),
(196,'menuDeliverableWeight',324,163,250,0),
(197,'menuIncomingStatus',324,172,260,0),
(198,'menuDeliverableStatus',324,164,270,0),
-- 8 - 132 ------------------------------------------------ Configuration - Lists of types
-- (326,'navPlanning',132,0,10,0),
(328,'navExpenses',132,0,70,0),
(329,'navIncomes',132,0,80,0),
(330,'navSteering',132,0,90,0),
(331,'navProduct',132,0,100,0),
(332,'navEnvironmentNav',132,0,110,0),
-- 8 - 132 - 326 ------------------------------------------ Configuration - Lists of types - Planning
(201,'menuProjectType',132,93,10,0),
(203,'menuActivityType',132,55,20,0),
(204,'menuMilestoneType',132,56,30,0),
(224,'menuMeetingType',132,65,40,0),
(202,'menuTicketType',132,53,50,0),
(222,'menuActionType',132,60,60,0),
-- 8 - 132 - 328 ------------------------------------------ Configuration - Lists of types - Financial Expense
(205,'menuBudgetType',328,198,10,0),
(212,'menuProjectExpenseType',328,81,20,0),
(211,'menuIndividualExpenseType',328,80,30,0),
(213,'menuExpenseDetailType',328,84,40,0),
(206,'menuCallForTenderType',328,155,50,0),
(207,'menuTenderType',328,156,60,0),
(208,'menuProviderOrderType',328,190,70,0),
(209,'menuProviderBillType',328,193,80,0),
(210,'menuProviderPaymentType',328,202,90,0),
(243,'menuSupplierContractType',328,229,100,0),
-- 8 - 132 - 329 ------------------------------------------ Configuration - Lists of types - Financial Incomes
(214,'menuQuotationType',329,132,10,0),
(215,'menuCommandType',329,126,20,0),
(216,'menuBillType',329,100,30,0),
(217,'menuPaymentType',329,83,40,0),
(218,'menuCatalogType',329,175,50,0),
(219,'menuInvoiceType',329,82,60,0),
(244,'menuClientContractType',329,236,70,0),
-- 8 - 132 - 330 ------------------------------------------ Configuration - Lists of types - Steering
(200,'menuOrganizationType',330,159,10,0),
(220,'menuRiskType',330,45,20,0),
(221,'menuOpportunityType',330,120,30,0),
(223,'menuIssueType',330,46,20,0),
(225,'menuChangeRequestType',330,226,50,0),
(226,'menuDecisionType',330,66,60,0),
(227,'menuQuestionType',330,67,70,0),
(231,'menuRequirementType',330,107,80,0),
(232,'menuTestCaseType',330,108,90,0),
(233,'menuTestSessionType',330,109,100,0),
(240,'menuIncomingType',330,166,110,0),
(241,'menuDeliverableType',330,165,120,0),
(242,'menuDeliveryType',330,183,130,0),
-- 8 - 132 - 331 ------------------------------------------ Configuration - Lists of types - Product
(236,'menuProductType',331,144,10,0),
(237,'menuComponentType',331,145,20,0),
(238,'menuProductVersionType',331,160,30,0),
(239,'menuComponentVersionType',331,161,40,0),
-- 8 - 132 - 332 ------------------------------------------ Configuration - Lists of types - Environment
(229,'menuDocumentType',332,101,10,0),
(228,'menuMessageType',332,52,20,0),
(234,'menuClientType',332,134,30,0),
(235,'menuProviderType',332,147,40,0),
(230,'menuContextType',332,105,50,0),
-- 8 - 133 ------------------------------------------------ Configuration - Leave System
(254,'menuLeaveType',133,217,10,0),
(255,'menuEmploymentContractType',133,218,20,0),
(256,'menuEmploymentContractEndReason',133,219,30,0),
(257,'menuLeavesSystemHabilitation',133,220,40,0),
-- 300 ---------------------------------------------------- Plugins
(301,'menuPluginManagement',300,136,10,0);

UPDATE `${prefix}navigation` set `tag`='gantt' WHERE name like '%Planning';

-- ========================================================

ALTER TABLE `${prefix}menucustom` ADD `idRow` INT(12) DEFAULT '1' COMMENT '12',  ADD `sortOrder` int(3) unsigned DEFAULT 1 COMMENT '3';

INSERT INTO `${prefix}parameter` (`parameterCode`, `parameterValue`) VALUES 
('menuLeftDisplayMode','ICONTXT');

INSERT INTO ${prefix}parameter (idUser, parameterCode, parameterValue) SELECT r.id , 'newGui', 0 FROM ${prefix}resource r where r.isUser=1;

ALTER TABLE `${prefix}notification` ADD `idPluginIdVersion` varchar(4000) DEFAULT NULL;

UPDATE `${prefix}habilitationother` set scope='canDeleteAttachment' where scope='canDeleteAttachement';

INSERT INTO `${prefix}originable` (`id`,`name`, `idle`) VALUES 
(32,'CallForTender', 0);

ALTER TABLE `${prefix}milestone` ADD `lastUpdateDateTime` datetime DEFAULT NULL;
