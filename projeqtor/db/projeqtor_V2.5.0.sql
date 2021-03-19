
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V2.5.0                                      //
-- // Date : 2012-08-08                                     //
-- ///////////////////////////////////////////////////////////
--
--

UPDATE `${prefix}menu` set idle=1 WHERE id=110;

-- work.php Report => add Team Parameter
INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(120,1,'idTeam','teamList',5,0,null),
(121,2,'idTeam','teamList',5,0,null),
(122,3,'idTeam','teamList',5,0,null);
-- workDetail.php Report => add Team Parameter
INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(123,28,'idTeam','teamList',5,0,null),
(124,29,'idTeam','teamList',5,0,null),
(125,30,'idTeam','teamList',5,0,null);
-- colorPlan.php Report => add Team Parameter
INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(126,4,'idTeam','teamList',5,0,null);
-- resourcePlan.php Report => add Team Parameter
INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(127,5,'idTeam','teamList',5,0,null);
-- projectPlan.php Report => add Team Parameter
INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(128,6,'idTeam','teamList',5,0,null);
-- activityPlan.php Report => add Team Parameter
INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(129,42,'idTeam','teamList',15,0,null);
-- detailPlan.php Report => add Team Parameter
INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(130,31,'idTeam','teamList',15,0,null);
-- availabilityPlan.php Report => add Team Parameter
INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(131,32,'idTeam','teamList',20,0,null);
