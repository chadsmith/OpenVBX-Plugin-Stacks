CREATE TABLE IF NOT EXISTS `subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list` int(11) NOT NULL,
  `value` varchar(15) NOT NULL,
  `joined` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `list` (`list`,`value`,`joined`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `subscribers_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant` (`tenant`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `subscribers_stacks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant` bigint(20) NOT NULL,
  `list` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `messages` text NOT NULL,
  `pointers` text,
  PRIMARY KEY (`id`),
  KEY `tenant` (`tenant`,`list`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
