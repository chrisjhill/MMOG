<?php
/**
 * Handles creating countries.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       21/09/2012
 */
class Model_Country_Create extends Model_Country_Coord
{
	/**
	 * The ruler name of the country.
	 *
	 * @access private
	 * @var string
	 */
	private $_countryRulerName;

	/**
	 * The name of the country.
	 *
	 * @access private
	 * @var string
	 */
	private $_countryName;

	/**
	 * Class constructor, set the variabes.
	 *
	 * @access public
	 * @param $countryRulerName string
	 * @param $countryName string
	 */
	public function __construct($countryRulerName, $countryName) {
		$this->_countryRulerName = trim($countryRulerName);
		$this->_countryName      = trim($countryName);
	}

	/**
	 * Create the country.
	 *
	 * @access public
	 * @param $user Model_User_Instance
	 * @return int
	 * @throws Exception
	 */
	public function create($user) {
		// Has the user actually entered a ruler and a country name?
		if (empty($this->_countryRulerName) || empty($this->_countryName)) {
			throw new Exception('register-error-empty');
		}

		// Does the ruler name and country name combination already exist?
		if (Model_Country_Utilities::countryNameCombinationAlreadyExists($this->_countryRulerName, $this->_countryName)) {
			throw new Exception('register-error-name-combo-taken');
		}

		// Generate coords
		$this->generateCoords();

		// Insert the country into the darabase
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			INSERT INTO `country`
			(
				`round_id`,
				`user_id`,
				`country_x_coord`,
				`country_y_coord`,
				`country_z_coord`,
				`country_status`,
				`country_ruler_name`,
				`country_name`
			)
			VALUES
			(
				:round_id,
				:user_id,
				:country_x_coord,
				:country_y_coord,
				:country_z_coord,
				:country_status,
				:country_ruler_name,
				:country_name
			)
		");

		// Execute the query
		$statement->execute(array(
			':round_id'           => GAME_ROUND,
			':user_id'            => $user->getInfo('user_id'),
			':country_x_coord'    => $this->getCoord('x'),
			':country_y_coord'    => $this->getCoord('y'),
			':country_z_coord'    => $this->getCoord('z'),
			':country_status'     => PLANET_MEMBER,
			':country_ruler_name' => $this->_countryRulerName,
			':country_name'       => $this->_countryName
		));

		// Return the user ID
		return $database->lastInsertId();
	}
}