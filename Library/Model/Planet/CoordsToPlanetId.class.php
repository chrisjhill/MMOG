<?php
/**
 * Return a planet ID from an X and Y coord.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       23/09/2012
 */
class Model_Planet_CoordsToPlanetId
{
	/**
	 * Get the ID of a planet from X and Y coords.
	 *
	 * @access public
	 * @param  int       $xCoord
	 * @param  int       $yCoord
	 * @throws Exception If the planet does not exist.
	 * @return int
     * @static
	 */
	public static function get($xCoord, $yCoord) {
		// Get the database connection
		$database  = Core_Database::getInstance();
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
		if ($statement->rowCount() <= 0) {
			// No, this planet does not exist
			throw new Exception('planet-does-not-exist');
		}

		// We found the planet, return the ID
		$data = $statement->fetch();
		return $data['planet_id'];
	}
}