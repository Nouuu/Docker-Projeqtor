-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 4.5.6                                       //
-- // Date : 2015-03-06                                     //
-- ///////////////////////////////////////////////////////////

UPDATE `${prefix}planningelement` SET validatedStartDate=realStartDate, validatedEndDate=realEndDate
WHERE idle=1 and validatedStartDate is null and validatedEndDate is null 
and realStartDate is not null and realEndDate is not null 
and idPlanningMode in (2,3,7);

INSERT INTO `${prefix}planningmode` (`id`, `applyTo`, `name`, `code`, `sortOrder`, `idle`, `mandatoryStartDate`, `mandatoryEndDate`) VALUES
(18, 'TestSession', 'PlanningModeGROUP', 'GROUP', 150, 0 , 0, 0);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`) VALUES
(54, 'reportWorkWeeklyResource', 1, 'work.php', 170),
(55, 'reportWorkMonthlyResource', 1, 'work.php',180),
(56, 'reportWorkYearlyResource', 1, 'work.php', 190);

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`,`defaultValue`) VALUES 
(54, 'week', 'week', 30, 'currentWeek'),
(55, 'month', 'month', 30, 'currentMonth'),
(56, 'year', 'year', 30,  'currentYear'),
(54,'idProject', 'projectList', 10, 'currentProject'),
(55,'idProject', 'projectList', 10, 'currentProject'),
(56,'idProject', 'projectList', 10, 'currentProject'),
(54,'idResource', 'resourceList', 20, 'currentResource'),
(55,'idResource', 'resourceList', 20, 'currentResource'),
(56,'idResource', 'resourceList', 20, 'currentResource');

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES
(1,54,1),
(2,54,1),
(3,54,1),
(4,54,1),
(5,54,0),
(6,54,0),
(7,54,0),
(1,55,1),
(2,55,1),
(3,55,1),
(4,55,1),
(5,55,0),
(6,55,0),
(7,55,0),
(1,56,1),
(2,56,1),
(3,56,1),
(4,56,1),
(5,56,0),
(6,56,0),
(7,56,0);

INSERT INTO `${prefix}habilitationother` (idProfile,scope,rightAccess) VALUES 
(1,'reportResourceAll','1'),
(2,'reportResourceAll','1'),
(3,'reportResourceAll','1'),
(4,'reportResourceAll','2'),
(6,'reportResourceAll','2'),
(7,'reportResourceAll','2'),
(5,'reportResourceAll','2');