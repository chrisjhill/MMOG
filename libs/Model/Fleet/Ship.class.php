<?php
/**
 * Contains information on the ships in the game.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       14/09/2012
 */
class Model_Fleet_Ship
{
	/**
	 * The ships that are available.
	 * 
	 * <code>
	 * array(
	 *     'ship_id'             => 123,
	 *     'ship_order_of_fire'  => 1,
	 *     'ship_name'           => 'Le Ship',
	 *     'ship_class'          => 1,
	 *     'ship_target'         => 2,
	 *     'ship_life'           => 10,
	 *     'ship_attack'         => 1,
	 *     'ship_primary_cost'   => 100,
	 *     'ship_secondary_cost' => 25
	 * )
	 * </code>
	 * 
	 * @access public
	 * @var array
	 * @static
	 */
	public static $_ship;

	/**
	 * Returns the ship information, or sets them if it doesn't exist.
	 *
	 * @access public
	 * @return array
	 */
	public function getInstance() {
		// Have we already got the ship information?
		if (! self::$_ship) {
			// No, go and get them
			self::setShip();
		}

		// Return the ship information
		return self::$_ship;
	}

	/**
	 * Set the ships that are stored in the database.
	 * 
	 * @access public
	 */
	public function setShip() {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT   s.ship_id, s.ship_order_of_fire, s.ship_name, s.ship_type, s.ship_class, s.ship_target, s.ship_life, s.ship_attack, s.ship_primary_cost, s.ship_secondary_cost
			FROM     `ship` s
			ORDER BY s.ship_order_of_fire ASC
		");

		// Execute the query
		$statement->execute();

		// Did we find any ships?
		while ($ship = $statement->fetch()) {
			self::$_ship[$ship['ship_id']] = $ship;
		}
	}
}