<?php
/**
 * Information on a country. A country can be thought of as a "player" or "user".
 *
 * Contains information on the country including their fleet information.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       13/09/2012
 */
class Model_Country
{
	/**
	 * Information on the country.
	 *
	 * <code>
	 * array(
	 *     'country_id'     => 12345,
	 *     'asteroid_count' => 54321
	 * )
	 * </code>
	 *
	 * @access private
	 * @var array
	 */
	private $_info = array();

	/**
	 * Information on this entities fleet.
	 *
	 * An entities entire fleet can be split into separate fleets. E.g., one
	 * fleet could be docked, whilst another two are sent out on missions.
	 *
	 * @access private
	 * @var FleetList
	 */
	private $_fleetList;

	/**
	 * Class constructor. Requires an ID.
	 *
	 * @access public
	 * @param int $countryId
	 */
	public function __construct($countryId) {
		$this->setInfo($countryId);
	}

	/**
	 * Get the entities information from the database and set it locally.
	 *
	 * @access public
	 * @param int $countryId
	 */
	public function setInfo($countryId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		// Prepare the SQL
		$statement = $database->prepare("
			SELECT c.country_id, c.asteroid_count
			FROM   `country` c
			WHERE  c.country_id = :country_id
			LIMIT  1
		");
		// Execute the query
		$statement->execute(array(
			':country_id' => $countryId
		));

		// Did we find the country?
		if ($statement->rowCount() >= 1) {
			// Yes, set the information
			$this->_info = $statement->fetch();
		}
	}

	/**
	 * Get all of the fleet information for the country and place it into
	 * a FleetList so we can manipulate easily.
	 *
	 * @access public
	 */
	public function setFleet() {
		// Set the FleetList
		$this->_fleetList = new Model_FleetList();

		// Get the database connection
		$database  = Core_Database::getInstance();
		// Prepare the SQL
		$statement = $database->prepare("
			SELECT f.country_id, f.fleet_id, f.fleet_status
			FROM   `fleet` f
			WHERE  f.country_id = :country_id
		");
		// Execute the query
		$statement->execute(array(
			':country_id' => $this->_info['country_id']
		));

		// Yes, loop over them
		while ($fleet = $statement->fetch()) {
			// And add a new Fleet to the FleetList
			$this->_fleetList->add(new Model_Fleet($fleet));
		}
	}

	/**
	 * Return a peice of information on the country.
	 *
	 * @access public
	 * @param string $index
	 * @return string
	 */
	public function getInfo($index) {
		return isset($this->_info[$index])
			? $this->_info[$index]
			: false;
	}

	/**
	 * Return a fleet that the country controls.
	 * 
	 * @access public
	 * @param int $fleetId
	 * @return Fleet
	 */
	public function getFleet($fleetId = 0) {
		// If we have not yet got the fleets, then set them
		if (! $this->_fleetList) {
			$this->setFleet();
		}
		
		// Do we want all fleets, or just a single fleet?
		if (! $fleetId) {
			return $this->_fleetList;
		}

		// We just want a single fleet
		return $this->_fleetList->exists($fleetId)
			? $this->_fleetList->get($fleetId)
			: false;
	}
}