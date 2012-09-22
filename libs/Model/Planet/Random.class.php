<?php
/**
 * Selects a random planet for a country to join.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       22/09/2012
 */
class Model_Planet_Random
{
	/**
	 * Find an existing planet that isn't already at their maximum capacity.
	 *
	 * Returns false if we were unable to find a row.
	 * 
	 * @access public
	 * @return mixed
	 */
	public function existingPlanet() {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT   p.planet_id, p.planet_x_coord, p.planet_y_coord
			FROM     `planet` p
			WHERE    p.planet_country_count < :planet_country_count
			ORDER BY RAND()
			LIMIT    1
		");

		// Execute the query
		$statement->execute(array(
			':planet_country_count' => PLANET_MAX_SIZE
		));

		// Did we find a random slot?
		if ($statement->rowCount() >= 1) {
			// Get the planet details
			$planet = $statement->fetch();

			// Return a Model_Planet_Instance object
			return new Model_Planet_Instance($planet['planet_id']);
		}

		// We could not find any random planet
		return false;
	}
}