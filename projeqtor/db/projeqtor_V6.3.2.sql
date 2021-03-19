-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 6.3.1 specific for postgresql               //
-- // Date : 2017-04-21                                     //
-- ///////////////////////////////////////////////////////////

-- UPDATE Handled for projects, newly created in V6.3
UPDATE `${prefix}project` set handled=1 where idStatus in (select id from `${prefix}status` where setHandledStatus=1);
