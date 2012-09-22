<?php
/**
 * Information on a country. A country can be thought of as a "player" or "user".
 *
 * Contains information on the country including their squadron information.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       13/09/2012
 */
class Model_Country_Instance extends Core_Instance
{
	/**
	 * Information on this countries fleet.
	 *
	 * A countries entire fleet can be split into squadrons. E.g., one
	 * squadron could be docked, whilst another two are sent out on missions.
	 *
	 * @access private
	 * @var Model_Fleet_List
	 */
	private $_fleetList;

	/**
	 * Sets up the country information.
	 *
	 * @access public
	 * @param int $countryId
	 */
	public function __construct($countryId = 0) {
		$this->setInfo($countryId);
	}

	/**
	 * Get the countries information from the database and set it locally.
	 *
	 * @access protected
	 * @param int $countryId
	 */
	protected function setInfo($countryId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT c.country_id, c.round_id, c.user_id, c.alliance_id,
			       c.country_x_coord, c.country_y_coord, c.country_z_coord,
			       c.country_status, c.country_ruler_name, c.country_name,
			       c.country_resource_primary, c.country_resource_secondary,
			       c.country_asteroid_count, c.country_prism_count,
			       c.country_created, c.country_updated, c.country_removed
			FROM   `country` c
			WHERE  c.country_id = :country_id
			       AND
			       c.round_id   = :round_id
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':country_id' => $countryId,
			':round_id'   => GAME_ROUND
		));

		// Did we find the country?
		if ($statement->rowCount() >= 1) {
			// Yes, set the information
			$this->_info = $statement->fetch();
		}
	}

	/**
	 * Get all of the squadron information for the country and place it into
	 * a Model_Fleet_List so we can manipulate easily.
	 *
	 * @access public
	 */
	public function setFleet() {
		// Set the Model_Fleet_List
		$this->_fleetList = new Model_Fleet_List();

		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT f.country_id, f.squadron_id, f.squadron_status
			FROM   `fleet` f
			WHERE  f.country_id = :country_id
		");

		// Execute the query
		$statement->execute(array(
			':country_id' => $this->getInfo('country_id')
		));

		// Yes, loop over them
		while ($squadron = $statement->fetch()) {
			// And add a new squadron to the Model_Fleet_List
			$this->_fleetList->add(new Model_Fleet_Squadron($squadron));
		}
	}

	/**
	 * Return a squadron that the country controls.
	 * 
	 * @access public
	 * @param int $squadronId
	 * @return Model_Fleet_Squadron
	 */
	public function getSquadron($squadronId = 0) {
		// If we have not yet got the squadrons, then set them
		if (! $this->_fleetList) {
			$this->setFleet();
		}
		
		// Do we want all squadrons, or just a single squadron?
		if (! $squadronId) {
			return $this->_fleetList;
		}

		// We just want a single fleet
		return $this->_fleetList->exists($squadronId)
			? $this->_fleetList->get($squadronId)
			: false;
	}

	/**
	 * Return the full country name.
	 *
	 * @access public
	 * @return string
	 */
	public function getFullCountryName() {
		return $this->getinfo('country_ruler_name') . ' of ' . $this->getInfo('country_name');
	}

	/**
	 * Builds the coords of a country.
	 *
	 * @access public
	 * @param $coords array
	 * @return string
	 */
	public function getCoords($coords = array('x', 'y', 'z')) {
		// Build output
		$output = array();

		// Start to build the coords
		foreach ($coords as $coord) {
			$output[] = $this->getInfo('country_' . $coord . '_coord');
		}

		// And return the output
		return implode(':', $output);
	}
}