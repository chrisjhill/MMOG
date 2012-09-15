DROP TABLE IF EXISTS `battle`;
CREATE TABLE `battle` (
  `battle_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL DEFAULT '0',
  `battle_string` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`battle_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `country`;
CREATE TABLE `country` (
  `country_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asteroid_count` int(10) unsigned NOT NULL DEFAULT '5',
  PRIMARY KEY (`country_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

DROP TABLE IF EXISTS `fleet`;
CREATE TABLE `fleet` (
  `country_id` int(10) unsigned NOT NULL,
  `fleet_id` int(10) unsigned NOT NULL,
  `fleet_status` int(10) unsigned NOT NULL,
  UNIQUE KEY `entity_id` (`country_id`,`fleet_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `fleet_ship`;
CREATE TABLE `fleet_ship` (
  `country_id` int(10) unsigned NOT NULL,
  `fleet_id` int(10) unsigned NOT NULL DEFAULT '1',
  `ship_id` int(10) unsigned NOT NULL DEFAULT '1',
  `ship_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `entity_id` (`country_id`,`fleet_id`,`ship_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `mission`;
CREATE TABLE `mission` (
  `mission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL DEFAULT '0',
  `fleet_id` int(10) unsigned NOT NULL DEFAULT '1',
  `mission_destination_country_id` int(10) unsigned NOT NULL DEFAULT '0',
  `mission_status` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `mission_eta` int(10) unsigned NOT NULL DEFAULT '10',
  `mission_wave_length` int(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`mission_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

DROP TABLE IF EXISTS `ship`;
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

INSERT INTO `country` VALUES(1, 100);
INSERT INTO `country` VALUES(2, 100);

INSERT INTO `fleet` VALUES(1, 1, 1);
INSERT INTO `fleet` VALUES(1, 2, 1);
INSERT INTO `fleet` VALUES(2, 1, 1);

INSERT INTO `fleet_ship` VALUES(1, 1, 1, 350);
INSERT INTO `fleet_ship` VALUES(1, 1, 2, 2000);
INSERT INTO `fleet_ship` VALUES(1, 1, 4, 600);
INSERT INTO `fleet_ship` VALUES(1, 1, 6, 1000);
INSERT INTO `fleet_ship` VALUES(1, 1, 7, 300);
INSERT INTO `fleet_ship` VALUES(1, 2, 1, 350);
INSERT INTO `fleet_ship` VALUES(1, 2, 3, 1500);
INSERT INTO `fleet_ship` VALUES(1, 2, 4, 200);
INSERT INTO `fleet_ship` VALUES(1, 2, 5, 5000);
INSERT INTO `fleet_ship` VALUES(1, 2, 7, 200);
INSERT INTO `fleet_ship` VALUES(2, 1, 1, 500);
INSERT INTO `fleet_ship` VALUES(2, 1, 2, 3000);
INSERT INTO `fleet_ship` VALUES(2, 1, 3, 1750);
INSERT INTO `fleet_ship` VALUES(2, 1, 4, 400);
INSERT INTO `fleet_ship` VALUES(2, 1, 5, 4000);
INSERT INTO `fleet_ship` VALUES(2, 1, 6, 4000);
INSERT INTO `fleet_ship` VALUES(2, 1, 7, 2000);

INSERT INTO `mission` VALUES(1, 2, 1, 1, 'A', 0, 4);

INSERT INTO `ship` VALUES(1, 2, 'Attacking ship 1', 1, 1, 1, 10, 15, 100, 25);
INSERT INTO `ship` VALUES(2, 3, 'Attacking ship 2', 1, 2, 2, 30, 10, 500, 100);
INSERT INTO `ship` VALUES(3, 4, 'Attacking ship 3', 1, 2, 4, 10, 20, 700, 100);
INSERT INTO `ship` VALUES(4, 1, 'EMP freezing ship', 2, 1, 4, 9, 14, 200, 75);
INSERT INTO `ship` VALUES(5, 5, 'Stealing ship', 4, 4, 2, 100, 400, 2000, 750);
INSERT INTO `ship` VALUES(6, 6, 'Salvage ship', 16, 4, 16, 150, 500, 2000, 750);
INSERT INTO `ship` VALUES(7, 7, 'Asteroid stealing ship', 32, 1, 32, 15, 75, 1000, 200);