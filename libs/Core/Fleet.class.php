<?php
/**
 * Contains information on a single country fleet.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       13/09/2012
 * @homepage    http://www.chrisjhill.co.uk
 * @twitter     @chrisjhill
 */
class Fleet
{
	/**
	 * Information on this fleet.
	 *
	 * @access private
	 * @var array
	 */
	private $_fleet = array();

	/**
	 * Class constructor.
	 *
	 * @access public
	 * @param array $fleet
	 */
	public function __construct($fleet) {
		// We have the initial fleet information
		// But not information on the contents of the fleet
		$this->_fleet = $fleet;

		// And set the ships this fleet contains
		$this->setShips();
	}

	/**
	 * Set the ships in this fleet
	 *
	 * @access public
	 */
	public function setShips() {
		// Get the database connection
		$database  = Database::getInstance();
		// Prepare the SQL
		$statement = $database->prepare("
			SELECT s.ship_id, s.ship_quantity
			FROM   `fleet_ship` s
			WHERE  s.country_id = :country_id
			       AND
			       s.fleet_id   = :fleet_id
		");
		// Execute the query
		$statement->execute(array(
			':country_id' => $this->_fleet['country_id'],
			':fleet_id'  => $this->_fleet['fleet_id']
		));

		// Loop over the ships in this fleet
		while ($ship = $statement->fetch()) {
			$this->_fleet[$ship['ship_id']] = $ship['ship_quantity'];
		}
	}

	/**
	 * Return how many we have of a certain ship.
	 *
	 * @access public
	 * @param int $shipId
	 * @return int
	 */
	public function getShip($shipId) {
		return isset($this->_fleet[$shipId])
			? $this->_fleet[$shipId]
			: 0;
	}

	/**
	 * Return a piece of information about this fleet.
	 *
	 * @access public
	 * @param string $index
	 * @return string
	 */
	public function getInfo($index) {
		return isset($this->_fleet[$index])
			? $this->_fleet[$index]
			: false;
	}
}