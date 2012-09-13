<?php
/**
 * Information on an entity. An entity can be thought of as a "player" or "user".
 *
 * Contains information on the entity including their fleet information.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       13/09/2012
 * @homepage    http://www.chrisjhill.co.uk
 * @twitter     @chrisjhill
 */
class Entity
{
	/**
	 * Information on the entity.
	 *
	 * <code>
	 * array(
	 *     'entity_id'      => 12345,
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
	 * @param int $entityId
	 */
	public function __construct($entityId) {
		$this->setInfo($entityId);
	}

	/**
	 * Get the entities information from the database and set it locally.
	 *
	 * @access public
	 * @param int $entityId
	 */
	public function setInfo($entityId) {
		// Get the database connection
		$database  = Database::getInstance();
		// Prepare the SQL
		$statement = $database->prepare("
			SELECT e.entity_id, e.asteroid_count
			FROM   `entity` e
			WHERE  e.entity_id = :entity_id
			LIMIT  1
		");
		// Execute the query
		$statement->execute(array(
			':entity_id' => $entityId
		));

		// Did we find the entity?
		if ($statement->rowCount() >= 1) {
			// Yes, set the information
			$this->_info = $statement->fetch();
		}
	}

	/**
	 * Get all of the fleet information for the entity and place it into
	 * a FleetList so we can manipulate easily.
	 *
	 * @access public
	 */
	public function setFleet() {
		// Set the FleetList
		$this->_fleetList = new FleetList();

		// Get the database connection
		$database  = Database::getInstance();
		// Prepare the SQL
		$statement = $database->prepare("
			SELECT f.entity_id, f.fleet_id, f.fleet_status
			FROM   `fleet` f
			WHERE  f.entity_id = :entity_id
		");
		// Execute the query
		$statement->execute(array(
			':entity_id' => $this->_info['entity_id']
		));

		// Did we find any fleets?
		if ($statement->rowCount() >= 1) {
			// Yes, loop over them
			while ($fleet = $statement->fetch()) {
				// And add a new Fleet to the FleetList
				$this->_fleetList->add(new Fleet($fleet));
			}
		}
	}

	/**
	 * Return a peice of information on the entity.
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
	 * Return a fleet that the entity controls.
	 * 
	 * @access public
	 * @param int $fleetId
	 * @return Fleet
	 */
	public function getFleet($fleetId) {
		// If we have not yet got the fleets, then set them
		if (! $this->_fleetList) {
			$this->setFleet();
		}

		// We have fetched the fleets, so now return it
		return $this->_fleetList->exists($fleetId)
			? $this->_fleetList->get($fleetId)
			: false;
	}
}