SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

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
  `VAT` int(10) NOT NULL,
  `version` varchar(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

CREATE TABLE IF NOT EXISTS `minibar` (
  `digit` int(1) NOT NULL,
  `label` varchar(50) NOT NULL,
  `price` decimal(8,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `models` (
  `room_model` varchar(20) NOT NULL,
  `price` decimal(6,2) NOT NULL default '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `rate` (
  `name` varchar(20) NOT NULL,
  `prefix` varchar(20) NOT NULL,
  `rate` decimal(8,5) NOT NULL default '0.00000',
  `rate_offset` decimal(8,5) NOT NULL default '0.00000'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `register` (
  `id` int(11) NOT NULL auto_increment,
  `room_id` varchar(15) NOT NULL,
  `guest_id` varchar(10) NOT NULL,
  `date_ci` datetime NOT NULL,
  `date_co` datetime NOT NULL,
  `status` tinyint(1) NOT NULL,
  `billing_file` varchar(100) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=102 ;

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
