
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V2.4.2                                      //
-- // Date : 2012-07-30                                     //
-- ///////////////////////////////////////////////////////////
--
--

INSERT INTO `${prefix}originable` (`id`, `name`, `idle`) VALUES
(14, 'Requirement', 0),
(15, 'TestSession', 0),
(16, 'TestCase', 0);

ALTER TABLE `${prefix}requirement` ADD COLUMN `idRunStatus` int(12) unsigned default null;
ALTER TABLE `${prefix}testsession` ADD COLUMN `idRunStatus` int(12) unsigned default null;
-- ALTER TABLE `${prefix}testcase` ADD COLUMN `idRunStatus` int(12) unsigned default null;

UPDATE `${prefix}runstatus` set sortOrder=200 WHERE id=1;
UPDATE `${prefix}runstatus` set sortOrder=300 WHERE id=2;
UPDATE `${prefix}runstatus` set sortOrder=500 WHERE id=3;
UPDATE `${prefix}runstatus` set sortOrder=400 WHERE id=4;

INSERT INTO `${prefix}runstatus` (id, name, color, sortOrder, idle) VALUES
(5, 'void', '#BB64BB', 100, 1);

ALTER TABLE `${prefix}requirement` ADD COLUMN `idContact` int(12) unsigned default null;