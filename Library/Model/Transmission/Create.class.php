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
	 * @param  Model_Country_Instance $country
	 * @param  string                 $xCoord
	 * @param  string                 $yCoord
	 * @param  string                 $zCoord
	 * @param  string                 $subject
	 * @param  string                 $body
	 * @throws Exception              If the subject or message is empty.
	 * @return boolean
	 */
	public function create($country, $xCoord, $yCoord, $zCoord, $subject, $body) {
		// Has the user populated the subject and body?
		if (empty($subject) || empty($body)) {
			throw new Exception('transmission-error-empty');
		}

		// Get the countryId from the coords
		// If we cannot find a country then an error will be thrown
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