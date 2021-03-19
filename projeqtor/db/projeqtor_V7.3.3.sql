-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 7.3.2                                       //
-- // Date : 2018-12-28                                     //
-- ///////////////////////////////////////////////////////////
--
--

ALTER TABLE `${prefix}budget`
CHANGE `update1Amount` `update1Amount` decimal(14,2),
CHANGE `update2Amount` `update2Amount` decimal(14,2);
ALTER TABLE `${prefix}budget`
CHANGE `update1FullAmount` `update1FullAmount` decimal(14,2),
CHANGE `update2FullAmount` `update2FullAmount` decimal(14,2);

INSERT INTO `${prefix}linkable` (`id`,`name`, `idle`, `idDefaultLinkable`) VALUES 
(31,'ProviderOrder', null,0),
(32,'ProviderBill', null,0), 
(33,'CallForTender', null,0);