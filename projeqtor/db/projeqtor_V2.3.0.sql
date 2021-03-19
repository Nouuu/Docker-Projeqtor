
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V2.3.0                                      //
-- // Date : 2012-05-12                                     //
-- ///////////////////////////////////////////////////////////
--
--

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`) VALUES 
(106,'menuResourcePlanning',7,'item',225,NULL,0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 106, 1),
(2, 106, 1),
(3, 106, 1),
(4, 106, 1),
(5, 106, 1),
(6, 106, 1),
(7, 106, 1);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`, `idle`) 
VALUES (40,'reportWorkPerActivity',1,'workPerActivity.php',170,0);

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(104,40,'idProject','projectList',10,0,'currentProject');

INSERT INTO `${prefix}habilitationreport` (`idProfile`, `idReport`, `allowAccess`) VALUES
(1,40,1),
(2,40,1),
(3,40,1),
(4,40,0),
(5,40,0),
(6,40,0),
(7,40,0);