<?php
/**
 * Generates how many countries there are in the round.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       22/09/2012
 */
class Model_Country_Count
{
	/**
	 * Returns how many countries there are in the round.
	 * 
	 * @access public
     * @param $roundId int
	 * @return int
     * @static
	 */
	public static function generate($roundId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT COUNT(*) as 'country_count' 
			FROM   `country` c
			WHERE  c.round_id = :round_id
		");

		// Execute the query
		$statement->execute(array(
			':round_id' => $roundId
		));

		// Get the data
		$data = $statement->fetch();

		// And return the number of countries
		return $data['country_count'];
	}
}