-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 7.1.2                                       //
-- // Date : 2018-06-10                                     //
-- ///////////////////////////////////////////////////////////
--
--

INSERT INTO `${prefix}reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `idle`, `defaultValue`) VALUES
(83,'showDetail','showDetail',35,0,null);

-- Ticket #3432
UPDATE `${prefix}menu` set menuClass='Automation' where id=180;
