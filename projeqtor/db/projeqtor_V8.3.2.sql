-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 8.3.2                                       //
-- // Date : 2020-01-02                                     //
-- ///////////////////////////////////////////////////////////
-- Patch on V8.3.0

INSERT INTO `${prefix}modulemenu` (`idModule`,`idMenu`,`hidden`,`active`) VALUES
 (11,227,0,(select active from ${prefix}module where id=11));
 
UPDATE `${prefix}modulemenu` SET idModule=10, active=(select active from ${prefix}module where id=10)
WHERE idMenu in (225,226);

UPDATE `${prefix}menu` SET level=null WHERE id=192;

UPDATE `${prefix}type` SET lockIdle=0
WHERE scope='ProviderPayment';

DELETE FROM `${prefix}columnselector`
WHERE objectClass='ProviderPaymentType';

INSERT INTO `${prefix}originable` ( `name`, `idle`) VALUES 
('ProviderOrder', 0),
('ProviderBill', 0);

INSERT INTO `${prefix}importable` (`id`, `name`, `idle`) VALUES
(56, 'ProviderOrder', 0),
(57, 'ProviderBill', 0);