<?php
// Game locations
define('PATH_BASE',  '/Users/chris/Documents/Repo/Battle/');
define('PATH_CACHE', '/Users/chris/Documents/Repo/Battle/cache/');

// Database details
define('DB_LOCATION', 'localhost');
define('DB_NAME',     'battle');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');

// Battle settings
define('GAME_BASH_LIMIT',                    '20'); // Percent
define('GAME_ASTEROID_LIFE',                 '50');
define('GAME_ASTEROID_MAX_CAP',              '10'); // Percent
define('GAME_SALVAGE_PRIMARY_RECLAIMABLE',   '15'); // Percent
define('GAME_SALVAGE_SECONDARY_RECLAIMABLE', '15'); // Percent

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