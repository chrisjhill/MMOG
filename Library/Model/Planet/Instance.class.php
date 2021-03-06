<?php
/**
 * Handles a single planet.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       22/09/2012
 */
class Model_Planet_Instance extends Core_Instance implements IteratorAggregate
{
	/**
	 * The countries that inhabit this planet.
	 *
	 * @access private
	 * @var    array
	 */
	private $_inhabitants = array();

	/**
	 * Set up the planet information.
	 *
	 * @access public
	 * @param  int     $planetId
     * @param  boolean $autoloadCountries
	 */
	public function __construct($planetId, $autoloadCountries = false) {
		// Set planet information
		$this->setInfo($planetId);

		// Set countries on this planet
		if ($autoloadCountries) {
			$this->setPlanetCountries();
		}
	}

	/**
	 * Set the information on the planet.
	 *
	 * @access protected
	 * @param  int       $planetId
	 */
	protected function setInfo($planetId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT p.planet_id, p.round_id,
			       p.planet_x_coord, p.planet_y_coord,
			       p.planet_country_count, p.planet_name, p.planet_image_url, p.planet_password,
			       p.planet_created
			FROM   `planet` p
			WHERE  p.planet_id = :planet_id
			       AND 
			       p.round_id  = :round_id
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':round_id'  => GAME_ROUND,
			':planet_id' => $planetId
		));

		// Did we find the country?
		if ($statement->rowCount() >= 1) {
			// Yes, set the information
			$this->_info = $statement->fetch();
		}
	}

	/**
	 * Set the countries that like on this planet.
	 *
	 * @access public
	 */
	public function setPlanetCountries() {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT   c.country_id
			FROM     `country` c
			WHERE    c.round_id        = :round_id
			         AND 
			         c.country_x_coord = :planet_x_coord
			         AND 
			         c.country_y_coord = :planet_y_coord
			ORDER BY c.country_z_coord ASC
		");

		// Execute the query
		$statement->execute(array(
			':round_id'        => GAME_ROUND,
			':planet_x_coord'  => $this->getInfo('planet_x_coord'),
			':planet_y_coord'  => $this->getInfo('planet_y_coord')
		));

		// Loop over each country
		while ($country = $statement->fetch()) {
			// Add to the country array
			$this->_inhabitants[] = new Model_Country_Instance($country['country_id']);
		}
	}

	/**
	 * Return the next available Z coord on this planet.
	 *
	 * @access public
	 * @throws Exception If the planet is already full.
	 * @return mixed
	 */
	public function getNextAvailableZCoord() {
		// Are all the countries occupied?
		if (count($this->_info) >= PLANET_MAX_SIZE) {
			throw new Exception('This planet is already fully occupied.');
		}

		// Set where we are in the loop
		$i = 1;

		// Loop through each country
		foreach ($this->_inhabitants as $country) {
			// Is this countries Z coord the one we were expecting?
			if ($i != $country->getInfo('country_z_coord')) {
				return $i;
			}

			// No, go to the next country
			$i++;
		}

		// If we got this far then all places up to now are taken
		// Since the planet isn't at their limit the next slot will be free
		return $i++;
	}

	/**
	 * Allow scripts to iterate over the countries in this planet.
	 * 
	 * @access public
	 * @return Model_Country_Instance
	 */
	public function getIterator() {
		return new ArrayIterator($this->_inhabitants);
	}
}