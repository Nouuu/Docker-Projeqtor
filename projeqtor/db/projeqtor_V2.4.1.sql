-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V2.4.1                                      //
-- // Date : 2012-07-17                                     //
-- ///////////////////////////////////////////////////////////
--
--
ALTER TABLE `${prefix}requirement` ADD COLUMN `countTotal` int(5) default 0;

ALTER TABLE `${prefix}testsession` ADD COLUMN `countPlanned` int(5) default 0;

INSERT INTO `${prefix}mailable` (`id`, `name`, `idle`) VALUES
(10, 'Requirement', 0),
(11, 'TestSession', 0),
(12, 'TestCase', 0);

INSERT INTO `${prefix}statusmail` (`idStatus`, `idMailable`, `mailToUser`,`mailToResource`, `mailToProject`) VALUES
(4,10,0,1,1),
(12,10,0,1,1),
(3,11,0,1,1),
(4,11,0,1,1);

CREATE TABLE `${prefix}importable` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}importable` (`id`, `name`, `idle`) VALUES
(1, 'Ticket', 0),
(2, 'Activity', 0),
(3, 'Milestone', 0),
(4, 'Risk', 0),
(5, 'Action', 0),
(6, 'Issue', 0),
(7, 'Meeting', 0),
(8, 'Decision', 0),
(9, 'Question', 0),
(10, 'IndividualExpense', 0),
(11, 'ProjectExpense', 0),
(12, 'Client', 0),
(13, 'Contact', 0),
(14, 'Project', 0),
(15, 'Team', 0),
(16, 'Resource', 0),
(17, 'Affectation', 0),
(18, 'Assignment', 0),
(19, 'Product', 0),
(20, 'Version', 0),
(21, 'Document', 0),
(22, 'Requirement', 0),
(23, 'TestCase', 0),
(24, 'TestSession', 0),
(25, 'TestCaseRun', 0);                    

