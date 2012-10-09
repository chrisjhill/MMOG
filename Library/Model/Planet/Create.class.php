<?php
/**
 * Creates a new planet for countries to inhibit.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       22/09/2012
 */
class Model_Planet_Create
{
	/**
	 * Create a new planet with random coords.
	 *
	 * @access public
	 * @param  boolean               $planetPrivate boolean
	 * @return Model_Planet_Instance
	 */
	public function create($planetPrivate = false) {
		// Get how many countries are currently signed up
		$countriesInGame = Model_Country_Count::generate(GAME_ROUND);

		// Work out where we should realistically start the search
		$xCoord = ceil($countriesInGame / (SYSTEM_MAX_SIZE * PLANET_MAX_SIZE));
		$xCoord = $xCoord <= 0 ? 1 : $xCoord;
		$yCoord = 1;

		// Get the database connection
		$database  = Core_Database::getInstance();

		// Generate some random coords for the planet to live
		$coords = $this->generateRandomCoords($database, $xCoord, $yCoord);

		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			INSERT INTO `planet`
				(
					`round_id`,
					`planet_x_coord`,
					`planet_y_coord`,
					`planet_password`
				)
			VALUES 
				(
					:round_id,
					:planet_x_coord,
					:planet_y_coord,
					:planet_password
				)
		");

		// Execute the query
		$statement->execute(array(
			':round_id'        => GAME_ROUND,
			':planet_x_coord'  => $coords['x'],
			':planet_y_coord'  => $coords['y'],
			':planet_password' => $planetPrivate ? substr(md5($_SERVER['REQUEST_TIME'] . mt_rand(0, 9999)), 0, 8) : null
		));

		// Return a new Model_Planet_Instance object
		return new Model_Planet_Instance($database->lastInsertId(), true);
	}

	/**
	 * Generate some random coords and see if they are available.
	 *
	 * This function is recursive, it will keep trying until it finds an available coord.
	 * 
	 * @param  PDO   $database
	 * @param  int   $xCoord
	 * @param  int   $yCoord
	 * @return array
	 * @recursive
	 */
	public function generateRandomCoords($database, $xCoord, $yCoord) {
		// Get the database connection
		$statement = $database->prepare("
			SELECT p.planet_id
			FROM   `planet` p
			WHERE  p.round_id       = :round_id
			       AND
			       p.planet_x_coord = :planet_x_coord
			       AND
			       p.planet_y_coord = :planet_y_coord
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':round_id'       => GAME_ROUND,
			':planet_x_coord' => $xCoord,
			':planet_y_coord' => $yCoord
		));

		// Did we find a planet?
		if ($statement->rowCount() >= 1) {
			// There was a planet
			// Work out the next coord in the series
			$yCoord++;

			// Have we reached the end of the system
			if ($yCoord >= SYSTEM_MAX_SIZE) {
				$xCoord++;
				$yCoord = 1;
			}

			// Start the search again
			return $this->generateRandomCoords($database, $xCoord, $yCoord);
		}

		// This planet is vacant, return the coords
		return array('x' => $xCoord, 'y' => $yCoord);	
	}
}