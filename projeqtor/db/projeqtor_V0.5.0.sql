
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR EXPORT                                      //
-- //-------------------------------------------------------//
-- // Version : V0.5.0                                      //
-- // Date : 2009-10-18                                     //
-- ///////////////////////////////////////////////////////////

--
-- Structure de la TABLE `${prefix}link`
--

CREATE TABLE `${prefix}link` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `ref1Type` varchar(100) DEFAULT NULL,
  `ref1Id` int(12) unsigned NOT NULL,
  `ref2Type` varchar(100) DEFAULT NULL,
  `ref2Id` int(12) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


--
-- Structure de la TABLE `${prefix}linkable`
--
CREATE TABLE `${prefix}linkable` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Structure de la table `${prefix}activity`
--
ALTER TABLE `${prefix}activity` ADD idActivity INT(12) UNSIGNED;

--
-- Structure de la table `${prefix}ticket`
--
ALTER TABLE `${prefix}ticket` ADD idActivity INT(12) UNSIGNED;

--
-- Structure de la table `${prefix}milestone`
--
ALTER TABLE `${prefix}milestone` ADD idActivity INT(12) UNSIGNED;

--
-- Contenu de la table `${prefix}linkable`
--
INSERT INTO `${prefix}linkable` (`id`, `name`, `idle`) VALUES (1, 'Action', 0),
(2, 'Issue', 0),
(3, 'Risk', 0);

