<?php
/**
 * Create and send a transmission to another country.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       30/09/2012
 */
class Model_Transmission_Create
{
	/**
	 * Send a transmission to another country.
	 * 
	 * @param $country Model_Country_Instance
	 * @param $xCoord string
	 * @param $yCoord string
	 * @param $zCoord string
	 * @param $subject string
	 * @param $body string
	 * @return boolean
	 * @throws Exception
	 */
	public function create($country, $xCoord, $yCoord, $zCoord, $subject, $body) {
		// Has the user populated the subject and body?
		if (empty(trim($subject)) || empty(trim($body))) {
			throw new Exception('transmission-create-empty');
		}

		// Get the countryId from the coords
		$countryToId = Model_Country_CoordsToCountryId::get($xCoord, $yCoord, $zCoord);

		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			INSERT INTO `transmission`
				(
					`round_id`,
					`transmission_from_country_id`,
					`transmission_to_country_id`,
					`transmission_subject`,
					`transmission_message`
				)
			VALUES
				(
					:round_id,
					:transmission_from_country_id,
					:transmission_to_country_id,
					:transmission_subject,
					:transmission_message
				)
		");

		// Execute the query
		$statement->execute(array(
			':round_id'                     => GAME_ROUND,
			':transmission_from_country_id' => $country->getInfo('country_id'),
			':transmission_to_country_id'   => $countryToId,
			':transmission_subject'         => $subject,
			':transmission_message'         => $body
		));

		// All went well
		return true;
	}
}