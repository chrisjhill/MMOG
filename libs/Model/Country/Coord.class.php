<?php
/**
 * Handle generation of planet coords and planet creation.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       22/09/2012
 */
class Model_Country_Coord
{
	/**
	 * Do we need to create a private planet?
	 *
	 * @access  private
	 * @var boolean
	 */
	private $_planetPrivate = false;

	/**
	 * The countries X coord.
	 *
	 * @access private
	 * @var int
	 */
	private $_countryXCoord;

	/**
	 * The countries Y coord.
	 *
	 * @access private
	 * @var int
	 */
	private $_countryYCoord;

	/**
	 * The countries Z coord.
	 *
	 * @access private
	 * @var int
	 */
	private $_countryZCoord;

	/**
	 * Generate coords for a new country or when moving to a new planet.
	 *
	 * @access public
	 * @return array
	 */
	public function generateCoords() {
		// We join a public planet by default
		// But we also need to make sure there are planets for inhabiting
		// Give a percentage chance to create a new planet
		$planetCreationPercentage = 100 / (PLANET_MAX_SIZE / 2);

		// Work out if we need to create a new planet
		$planetCreateNew = mt_rand(1, 100) < $planetCreationPercentage;

		// Join an existing public planet if we are not creating a private planet and
		// .. we are not randomly creating a new planet.
		if (! $this->_planetPrivate && ! $planetCreateNew) {
			$planet = new Model_Planet_Random();
			$planet = $planet->existingPlanet();
		}

		// Do we need to create a new planet?
		// Even if we tried to join a planet there is no guarentee we found one
		if (! $planet) {
			$planet = Model_Planet_Create();
			$planet = $planet->create();
		}

		// Set coord information
		$this->_countryXCoord = $planet->getInfo('planet_x_coord');
		$this->_countryYCoord = $planet->getInfo('planet_y_coord');
		$this->_countryZCoord = $planet->getNextAvailableSlot();
	}

	/**
	 * We want to create a private planet.
	 *
	 * @access public
	 */
	public function setPrivatePlanet() {
		$this->_planetPrivate = true;
	}

	/**
	 * Return a coordinate.
	 * 
	 * @param $coord string
	 * @return int
	 */
	public function getCoord($coord) {
		return $this->{'_country' . ucfirst($coord) . 'Coord'};
	}
}