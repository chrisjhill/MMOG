<?php
// Directory separator quick access
define('DS', DIRECTORY_SEPARATOR);
define('MODULE', 'Web');

// Game paths
define('PATH_WEB',      '/');
define('PATH_BASE',     '/Users/chris/Documents/Repo/MMOG/');
define('PATH_CACHE',    PATH_BASE . MODULE . '/cache/');
define('PATH_LIBRARY',  PATH_BASE . 'Library/');
define('PATH_LANGUAGE', PATH_LIBRARY . 'Language/');
define('PATH_VIEW',     PATH_LIBRARY . 'View/');
define('PATH_EMAIL',    PATH_VIEW . MODULE . '/Email/');
define('PATH_HELPER',   PATH_VIEW . MODULE . '/Helper/');
define('PATH_LAYOUT',   PATH_VIEW . MODULE . '/Layout/');
define('PATH_SCRIPT',   PATH_VIEW . MODULE . '/Script/');
define('PATH_SNIPPET',  PATH_VIEW . MODULE . '/Snippet/');

// Database details
define('DB_LOCATION', 'localhost');
define('DB_NAME',     'battle');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');

// Generic game settings
define('GAME_VERSION', 'v0.10'); // We follow the major.minor.bugfix format
define('GAME_UPDATED', '6st October 2012, 09:00');
define('GAME_NAME',    'MMOG Demo ' . GAME_VERSION);
define('GAME_ROUND',   1);

// Battle settings
define('BATTLE_ASTEROID_LIFE',     50);
define('BATTLE_ASTEROID_MAX_CAP',  10); // Percent
define('BATTLE_SALVAGE_PRIMARY',   15); // Percent
define('BATTLE_SALVAGE_SECONDARY', 15); // Percent

// Country/Planet settings
define('SYSTEM_MAX_SIZE',      15);
define('PLANET_MAX_SIZE',      15);
define('PLANET_MEMBER',        1);
define('PLANET_LEADER',        2);
define('PLANET_MINISTER_WAR',  4);
define('PLANET_MINISTER_BANK', 8);
define('PLANET_BASH_LIMIT',    20); // Percent

// Types of ship
define('SHIP_TYPE_BASIC',   1);
define('SHIP_TYPE_EMP',     2);
define('SHIP_TYPE_STEAL',   4);
define('SHIP_TYPE_STEALTH', 8);
define('SHIP_TYPE_SALVAGE', 16);
define('SHIP_TYPE_POD',     32);

// Ship classes
define('SHIP_CLASS_FIGHTER',   1);
define('SHIP_CLASS_FRIGATE',   2);
define('SHIP_CLASS_CRUISER',   4);
define('SHIP_CLASS_DESTROYER', 8);
define('SHIP_CLASS_SALVAGE',   16);
define('SHIP_CLASS_POD',       32);

// Fleet status
define('FLEET_DOCKED',  1);
define('FLEET_MISSION', 2);