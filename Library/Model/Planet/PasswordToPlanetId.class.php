<?php
/**
 * Finds a planet via a password.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       29/09/2012
 */
class Model_Planet_PasswordToPlanetId
{
	/**
	 * Try and find a planet based off a password.
	 *
	 * @access public
	 * @param $planetPassword string
	 * @return Model_Planet_Password
	 * @static
	 */
	public static function find($planetPassword) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT p.planet_id
			FROM   `planet` p
			WHERE  p.round_id        = :round_id
			       AND
			       p.planet_password = :planet_password
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':round_id'        => GAME_ROUND,
			':planet_password' => $planetPassword
		));

		// Did we find a planet?
		if ($statement->rowCount() <= 0) {
			// No, this planet does not exist
			throw new Exception('preferences-error-unknown-password');
		}

		// We found the planet, return the planet
		$data = $statement->fetch();
		return new Model_Planet_Instance($data['planet_id']);
	} 
}