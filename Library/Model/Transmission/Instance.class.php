<?php
/**
 * Information on a single transmission. A transmission is essentially a mail.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       30/09/2012
 */
class Model_Transmission_Instance extends Core_Instance
{
	/**
	 * Sets up the transmission information.
	 *
	 * @access public
	 * @param  int    $transmissionId
	 */
	public function __construct($transmissionId) {
		$this->setInfo($transmissionId);
	}

	/**
	 * Get the transmission information from the database and set it locally.
	 *
	 * @access protected
	 * @param  int       $transmissionId
	 */
	protected function setInfo($transmissionId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT t.transmission_id, t.round_id,
			       t.transmission_from_country_id, t.transmission_to_country_id,
			       t.transmission_subject, t.transmission_message,
			       t.transmission_created, t.transmission_read, t.transmission_removed
			FROM   `transmission` t
			WHERE  t.round_id             = :round_id
			       AND
			       t.transmission_id      = :transmission_id
			       AND
			       t.transmission_removed IS NULL
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':round_id'        => GAME_ROUND,
			':transmission_id' => $transmissionId
		));

		// Did we find the country?
		if ($statement->rowCount() >= 1) {
			// Yes, set the information
			$this->_info = $statement->fetch();
		}
	}
}