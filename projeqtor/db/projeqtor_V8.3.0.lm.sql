-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.3.0                                       //
-- // Date : 2019-09-27                                     //
-- ///////////////////////////////////////////////////////////

-- ======================================
-- Live Meeting
-- ======================================

CREATE TABLE `livemeeting` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idMeeting` int(12),
  `param` varchar(5000),
  `result` varchar(10000),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;