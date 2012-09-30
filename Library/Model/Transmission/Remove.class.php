<?php
/**
 * Remove a single transmission.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       30/09/2012
 */
class Model_Transmission_Remove
{
	/**
	 * Remove a single transmission.
	 * 
	 * @param $country Model_Country_Instance
	 * @param int $transmissionId
	 * @return boolean
	 */
	public function remove($country, $transmissionId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			DELETE FROM `transmission`
			WHERE       `round`                      = :round_id
			            AND
			            `transmission_to_country_id` = :country_id
			            AND
			            `transmission_id`            = :transmission_id
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':round_id'        => GAME_ROUND,
			':country_id'      => $country->getInfo('country_id'),
			':transmission_id' => $transmissionId
		));

		// Did we succeed?
		if ($statement->rowCount() <= 0) {
			throw new Exception('transmission-inbound-remove');
		}

		// All went well
		return true;
	}
}