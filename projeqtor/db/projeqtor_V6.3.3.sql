-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 6.3.1 specific for postgresql               //
-- // Date : 2017-04-21                                     //
-- ///////////////////////////////////////////////////////////

-- UPDATE Handled for projects, newly created in V6.3
INSERT INTO `${prefix}linkable` (`id`, `name`, `idle`, `idDefaultLinkable`) VALUES 
(25, 'Product', '0', '25'),
(26, 'Component', '0', '26'),
(27, 'ProductVersion', '0', '27'),
(28, 'ComponentVersion', '0', '28');
