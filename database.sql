CREATE TABLE `battle` (
  `battle_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL DEFAULT '0',
  `battle_string` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`battle_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `country` (
  `country_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `round_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `alliance_id` int(10) unsigned NOT NULL DEFAULT '0',
  `country_x_coord` int(10) unsigned NOT NULL DEFAULT '0',
  `country_y_coord` int(10) unsigned NOT NULL DEFAULT '0',
  `country_z_coord` int(10) unsigned NOT NULL DEFAULT '0',
  `country_status` int(10) unsigned NOT NULL DEFAULT '1',
  `country_ruler_name` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Ruler',
  `country_name` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'The Land',
  `country_resource_primary` int(10) unsigned NOT NULL DEFAULT '10000',
  `country_resource_secondary` int(10) unsigned NOT NULL DEFAULT '2500',
  `country_asteroid_count` int(10) unsigned NOT NULL DEFAULT '5',
  `country_prism_count` int(10) unsigned NOT NULL DEFAULT '5',
  `country_score` int(10) unsigned NOT NULL DEFAULT '0',
  `country_created` int(10) unsigned NOT NULL DEFAULT '0',
  `country_updated` int(10) unsigned NOT NULL DEFAULT '0',
  `country_removed` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`country_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

INSERT INTO `country` VALUES(1, 1, 0, 0, 1, 1, 1, 0, 'Democracy', 'The Land', 10000, 2500, 100, 5, 0, 0, 0, 0);
INSERT INTO `country` VALUES(2, 1, 0, 0, 1, 1, 1, 0, 'Democracy', 'The Land', 10000, 2500, 100, 5, 0, 0, 0, 0);

CREATE TABLE `fleet` (
  `country_id` int(10) unsigned NOT NULL,
  `squadron_id` int(10) unsigned NOT NULL,
  `squadron_status` int(10) unsigned NOT NULL,
  UNIQUE KEY `entity_id` (`country_id`,`squadron_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `fleet` VALUES(1, 1, 1);
INSERT INTO `fleet` VALUES(1, 2, 1);
INSERT INTO `fleet` VALUES(2, 1, 1);

CREATE TABLE `mission` (
  `mission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL DEFAULT '0',
  `squadron_id` int(10) unsigned NOT NULL DEFAULT '1',
  `mission_destination_country_id` int(10) unsigned NOT NULL DEFAULT '0',
  `mission_status` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `mission_eta` int(10) unsigned NOT NULL DEFAULT '10',
  `mission_wave_length` int(1) unsigned NOT NULL DEFAULT '1',
  `mission_created` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`mission_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

INSERT INTO `mission` VALUES(1, 2, 1, 1, 'A', 0, 4, 0);

CREATE TABLE `ship` (
  `ship_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ship_order_of_fire` int(10) unsigned NOT NULL DEFAULT '0',
  `ship_name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ship_type` int(10) unsigned NOT NULL DEFAULT '1',
  `ship_class` int(10) unsigned NOT NULL DEFAULT '1',
  `ship_target` int(10) unsigned NOT NULL DEFAULT '1',
  `ship_life` int(10) unsigned NOT NULL DEFAULT '10',
  `ship_attack` int(10) unsigned NOT NULL DEFAULT '1',
  `ship_primary_cost` int(10) unsigned NOT NULL DEFAULT '100',
  `ship_secondary_cost` int(10) unsigned NOT NULL DEFAULT '25',
  PRIMARY KEY (`ship_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

INSERT INTO `ship` VALUES(1, 2, 'Attacking ship 1', 1, 1, 1, 10, 15, 100, 25);
INSERT INTO `ship` VALUES(2, 3, 'Attacking ship 2', 1, 2, 2, 30, 10, 500, 100);
INSERT INTO `ship` VALUES(3, 4, 'Attacking ship 3', 1, 2, 4, 10, 20, 700, 100);
INSERT INTO `ship` VALUES(4, 1, 'EMP freezing ship', 2, 1, 4, 9, 14, 200, 75);
INSERT INTO `ship` VALUES(5, 5, 'Stealing ship', 4, 4, 2, 100, 400, 2000, 750);
INSERT INTO `ship` VALUES(6, 6, 'Salvage ship', 16, 4, 16, 150, 500, 2000, 750);
INSERT INTO `ship` VALUES(7, 7, 'Asteroid stealing ship', 32, 1, 32, 15, 75, 1000, 200);

CREATE TABLE `squadron` (
  `country_id` int(10) unsigned NOT NULL,
  `squadron_id` int(10) unsigned NOT NULL DEFAULT '1',
  `ship_id` int(10) unsigned NOT NULL DEFAULT '1',
  `ship_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `entity_id` (`country_id`,`squadron_id`,`ship_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `squadron` VALUES(1, 1, 1, 350);
INSERT INTO `squadron` VALUES(1, 1, 2, 2000);
INSERT INTO `squadron` VALUES(1, 1, 4, 600);
INSERT INTO `squadron` VALUES(1, 1, 6, 1000);
INSERT INTO `squadron` VALUES(1, 1, 7, 300);
INSERT INTO `squadron` VALUES(1, 2, 1, 350);
INSERT INTO `squadron` VALUES(1, 2, 3, 1500);
INSERT INTO `squadron` VALUES(1, 2, 4, 200);
INSERT INTO `squadron` VALUES(1, 2, 5, 5000);
INSERT INTO `squadron` VALUES(1, 2, 7, 200);
INSERT INTO `squadron` VALUES(2, 1, 1, 500);
INSERT INTO `squadron` VALUES(2, 1, 2, 3000);
INSERT INTO `squadron` VALUES(2, 1, 3, 1750);
INSERT INTO `squadron` VALUES(2, 1, 4, 400);
INSERT INTO `squadron` VALUES(2, 1, 5, 4000);
INSERT INTO `squadron` VALUES(2, 1, 6, 4000);
INSERT INTO `squadron` VALUES(2, 1, 7, 2000);

CREATE TABLE `user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_created` int(10) unsigned NOT NULL DEFAULT '0',
  `user_verified` int(10) unsigned NOT NULL DEFAULT '0',
  `user_last_login` int(10) unsigned NOT NULL DEFAULT '0',
  `user_updated` int(10) unsigned NOT NULL DEFAULT '0',
  `user_removed` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

INSERT INTO `user` VALUES(1, '', '', 0, 0, 0, 0, 0);