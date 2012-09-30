<?php
/**
 * Return a country ID from an X, Y, and Z coord.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       30/09/2012
 */
class Model_Country_CoordsToCountryId
{
	/**
	 * Return a country ID from an X, Y, and Z coord.
	 *
	 * @access public
	 * @param $xCoord int
	 * @param $yCoord int
	 * @param $zCoord int
	 * @return int
     * @static
	 * @throws Exception
	 */
	public static function get($xCoord, $yCoord, $zCoord) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT c.country_id
			FROM   `country` c
			WHERE  c.round_id        = :round_id
			       AND
			       c.country_x_coord = :country_x_coord
			       AND
			       c.country_y_coord = :country_y_coord
			       AND
			       c.country_z_coord = :country_z_coord
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':round_id'        => GAME_ROUND,
			':country_x_coord' => $xCoord,
			':country_y_coord' => $yCoord,
			':country_z_coord' => $zCoord
		));

		// Did we find a country?
		if ($statement->rowCount() <= 0) {
			// No, this country does not exist
			throw new Exception('country-does-not-exist');
		}

		// We found the country, return the ID
		$data = $statement->fetch();
		return $data['country_id'];
	}
}