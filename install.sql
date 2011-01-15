-- phpMyAdmin SQL Dump
-- version 2.11.10
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Sam 15 Janvier 2011 à 17:14
-- Version du serveur: 5.0.77
-- Version de PHP: 5.1.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de données: `roomix`
--

-- --------------------------------------------------------

--
-- Structure de la table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `o_m` varchar(10) NOT NULL default 'Hotel',
  `locked` tinyint(1) NOT NULL default '1',
  `cbr` tinyint(1) NOT NULL default '1',
  `rmbc` tinyint(1) NOT NULL default '1',
  `reception` varchar(5) NOT NULL,
  `company` varchar(512) NOT NULL,
  `clean` varchar(6) NOT NULL,
  `minibar` varchar(6) NOT NULL,
  `logo` varchar(100) NOT NULL,
  `logo64` mediumtext NOT NULL,
  `vat_1` decimal(5,2) NOT NULL default '19.60',
  `vat_2` decimal(5,2) NOT NULL default '5.50',
  `mail` varchar(50) NOT NULL,
  `version` varchar(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `guest`
--

CREATE TABLE IF NOT EXISTS `guest` (
  `id` int(10) NOT NULL auto_increment,
  `first_name` varchar(20) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `address` varchar(60) NOT NULL,
  `cp` varchar(15) NOT NULL,
  `city` varchar(20) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `fax` varchar(15) NOT NULL,
  `mail` varchar(30) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Structure de la table `minibar`
--

CREATE TABLE IF NOT EXISTS `minibar` (
  `digit` int(1) NOT NULL,
  `label` varchar(50) NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `vat` decimal(5,2) NOT NULL default '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `models`
--

CREATE TABLE IF NOT EXISTS `models` (
  `room_model` varchar(20) NOT NULL,
  `room_price` decimal(6,2) NOT NULL default '0.00',
  `room_vat` decimal(5,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `rate`
--

CREATE TABLE IF NOT EXISTS `rate` (
  `name` varchar(20) NOT NULL,
  `prefix` varchar(20) NOT NULL,
  `rate` decimal(8,5) NOT NULL default '0.00000',
  `rate_offset` decimal(8,5) NOT NULL default '0.00000'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `register`
--

CREATE TABLE IF NOT EXISTS `register` (
  `id` int(11) NOT NULL auto_increment,
  `room_id` varchar(15) NOT NULL,
  `guest_id` varchar(10) NOT NULL,
  `date_ci` datetime NOT NULL,
  `date_co` datetime NOT NULL,
  `status` tinyint(1) NOT NULL,
  `billing_file` varchar(100) NOT NULL,
  `paid` tinyint(1) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=106 ;

-- --------------------------------------------------------

--
-- Structure de la table `rooms`
--

CREATE TABLE IF NOT EXISTS `rooms` (
  `id` int(11) NOT NULL auto_increment,
  `room_name` varchar(20) NOT NULL,
  `model` varchar(20) NOT NULL,
  `extension` varchar(5) NOT NULL,
  `groupe` varchar(20) NOT NULL,
  `free` tinyint(1) NOT NULL default '1',
  `clean` tinyint(1) NOT NULL default '1',
  `mini_bar` varchar(100) NOT NULL,
  `dnd` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=66 ;
