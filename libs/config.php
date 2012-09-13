<?php
// Database details
define('DB_LOCATION', 'localhost');
define('DB_NAME',     'battle');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');

// Types of ship
define('SHIP_BASIC',   1);
define('SHIP_EMP',     2);
define('SHIP_STEAL',   4);
define('SHIP_SALVAGE', 8);
define('SHIP_POD',     16);

// Fleet status
define('FLEET_DOCKED',  1);
define('FLEET_MISSION', 2);