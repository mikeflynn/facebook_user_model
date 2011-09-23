CREATE TABLE headoftheclass.`users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fb_id` bigint(20) unsigned NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `profile_url` varchar(1000) NOT NULL,
  `email` varchar(200) NOT NULL,
  `photo` varchar(350) DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `interests` text,
  `birthday` varchar(255) DEFAULT NULL,
  `points` int(11) unsigned NOT NULL DEFAULT '0',
  `gender` varchar(10) NOT NULL DEFAULT 'male',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fb_id` (`fb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;