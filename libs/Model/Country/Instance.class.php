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
class Model_Country_Instance
{
	/**
	 * Information on the country.
	 *
	 * <code>
	 * array(
	 *     'country_id'                 => 12345,
	 *     'round_id'                   => 1,
	 *     'user_id'                    => 54321
	 *     'alliance_id'                => 24135,
	 *     'country_x_coord'            => 1,
	 *     'country_y_coord'            => 2,
	 *     'country_z_coord'            => 3,
	 *     'country_status'             => 1,
	 *     'country_ruler_name'         => 'Ruler',
	 *     'country_name'               => 'The Land',
	 *     'country_resource_primary'   => 12345,
	 *     'country_resource_secondary' => 54321,
	 *     'country_asteroid_count'     => 100,
	 *     'country_prism_count'        => 200,
	 *     'country_score'              => 1234567890,
	 *     'country_created'            => 1234567890,
	 *     'country_updated'            => 0,
	 *     'country_removed'            => 0
	 * )
	 * </code>
	 *
	 * @access private
	 * @var array
	 */
	private $_info = array();

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
	 * Class constructor. Requires an ID.
	 *
	 * @access public
	 * @param int $countryId
	 */
	public function __construct($countryId) {
		$this->setInfo($countryId);
	}

	/**
	 * Get the countries information from the database and set it locally.
	 *
	 * @access public
	 * @param int $countryId
	 */
	public function setInfo($countryId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		// Prepare the SQL
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
		// Prepare the SQL
		$statement = $database->prepare("
			SELECT f.country_id, f.squadron_id, f.squadron_status
			FROM   `fleet` f
			WHERE  f.country_id = :country_id
		");
		// Execute the query
		$statement->execute(array(
			':country_id' => $this->_info['country_id']
		));

		// Yes, loop over them
		while ($squadron = $statement->fetch()) {
			// And add a new squadron to the Model_Fleet_List
			$this->_fleetList->add(new Model_Fleet_Squadron($squadron));
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
}