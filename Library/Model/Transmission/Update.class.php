<?php
/**
 * Handles updating information on a single transmission.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       1/10/2012
 */
class Model_Transmission_Update
{
	/**
	 * Set the time when the transmission was read.
	 *
	 * @access public
	 * @param $transmissionId int
	 * @return boolean
	 */
	public function read($transmissionId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			UPDATE `transmission` t
			SET    t.transmission_read = NOW()
			WHERE  t.round_id        = :round_id
			       AND
			       t.transmission_id = :transmission_id
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':round_id'        => GAME_ROUND,
			':transmission_id' => $transmissionId
		));

		// Success
		return true;
	}
}