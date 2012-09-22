<?php
/**
 * A collection of functions that could be written in multiple classes, so
 * are instead grouped together in this one utility class.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       21/09/2012
 */
class Model_Country_Utilities
{
	/**
	 * Only one country can have the same ruler and country name (otherwise)
	 * things might get confusing.
	 *
	 * @access public
	 * @param $countryRulerName string
	 * @param $countryName string
	 * @return boolean
	 * @throws Exception
	 */
	public function countryNameCombinationAlreadyExists($countryRulerName, $countryName) {
		// Make sure the user is logged in
		if (! Model_User_Auth::hasIdentity()) {
			throw new Exception('Unable to locate a user to check.');
		}

		// Get the users identity
		$user = Model_User_Auth::getIdentity();

		// Does this ruler and country name exist for anyone else?
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT c.country_id
			FROM   `country` c
			WHERE  c.country_ruler_name = :country_ruler_name
			       AND 
			       c.country_name       = :country_name
			       AND 
			       c.round_id           = :round_id
			       AND 
			       c.user_id           != :user_id
			LIMIT  1
		");

		// Execute the query
		$userId = $statement->execute(array(
			':country_ruler_name' => $countryRulerName,
			':country_name'       => $countryName,
			':round_id'           => GAME_ROUND,
			'user_id'             => $user->getInfo('user_id')
		));

		// And return if we found anything
		return $statement->rowCount() >= 1
			? true 
			: false;
	}
}