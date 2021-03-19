-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 9.0.5                                       //
-- // Date : 2021-02-10                                     //
-- ///////////////////////////////////////////////////////////
-- Patch on V9.0

INSERT INTO `${prefix}referencable` (`id`,`name`, `idle`) VALUES 
(26,'ProviderOrder', '0'),
(27,'ProviderBill', '0'),
(28,'ProviderPayment', '0');

UPDATE `${prefix}referencable` set idle=1 WHERE name in ('Payment','ProviderPayment');

-- UPDATE `${prefix}menu` set idMenu=143 WHERE name = 'menuImportProject';

INSERT INTO `${prefix}navigation` (name, idParent, idMenu, sortOrder) select name, 7, id, 35 from `${prefix}menu` WHERE name='menuImportProject'; 
