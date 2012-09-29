<?php
class Model_Country_Update
{
	/**
	 * Tries to update the ruler and country name.
	 * 
	 * @param $country Model_Cuntry_Instance
	 * @param $countryRulerName string
	 * @param $countryName string
	 * @return boolean
	 * @throws Exception
	 */
	public function updateRulerAndCountryName($country, $countryRulerName, $countryName) {
		// Does the ruler name and country name combination already exist?
		if (Model_Country_RulerAndCountryNameExist::check($countryRulerName, $countryName)) {
			throw new Exception('preferences-error-name-combo-taken');
		}

		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			UPDATE `country` c
			SET    c.country_ruler_name = :country_ruler_name,
			       c.country_name       = :country_name,
			       c.country_updated    = NOW()
			WHERE  c.round_id           = :round_id
			       AND
			       c.country_id         = :country_id
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':country_ruler_name' => $countryRulerName,
			':country_name'       => $countryName,
			':round_id'           => GAME_ROUND,
			':country_id'         => $country->getInfo('country_id')
		));

		// How did the update go?
		if ($statement->rowCount() <= 0) {
			throw new Exception('preferences-error-name-combo-error');
		}

		// Success
		return true;
	}
}