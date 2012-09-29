<?php
/**
 * Handles updating the countries information.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       29/09/2012
 */
class Model_Country_Update
{
	/**
	 * Tries to update the ruler and country name.
	 *
	 * @access public
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

		// Log this event
		Core_Log::add(array(
			'user_id'     => $country->getInfo('user_id'),
			'country_id'  => $country->getInfo('country_id'),
			'log_action'  => 'country-name-change',
			'log_status'  => 'success',
			'log_message' => 'User successfully updated their names to ' . $countryRulerName . ' of ' . $countryName
		));

		// Success
		return true;
	}

	/**
	 * Relocate the country to a new planet.
	 *
	 * @access public
	 * @param $country Model_Country_Insrance
	 * @param $planetPassword string
	 * @return boolean
	 * @throws Exception
	 */
	public function planetRelocate($country, $planetPassword) {
		// Has the user checked the 'I am sure' checkbox?
		if (! isset($_POST['planet_change_sure']) || $_POST['planet_change_sure'] != '1') {
			throw new Exception('preferences-error-country-not-sure');
		}

		// Get a planet to move the user to
		if (empty($planetPassword)) {
			// Get some new coords from a random planet
			$planet = new Model_Planet_Random();
			$planet = $planet->existingPlanet();
		} else {
			// User has specified a planet password, can we find that password?
			$planet = Model_Planet_PasswordToPlanetId::find($planetPassword);
		}

		// Is the planet the same as the countries current?
		if (
			$planet->getInfo('planet_x_coord') == $country->getInfo('country_x_coord') &&
			$planet->getInfo('planet_y_coord') == $country->getInfo('country_y_coord')
		) {
			throw new Exception('preferences-error-same-planet');
		}

		// We have new coords
		// Add one country to the new planet
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			UPDATE `planet` p
			SET    p.planet_country_count = p.planet_country_count + 1
			WHERE  p.round_id             = :round_id
			       AND
			       p.planet_x_coord       = :planet_x_coord
			       AND
			       p.planet_y_coord       = :planet_y_coord
			LIMIT  1
		");
		// Execute the query
		$statement->execute(array(
			':round_id' => GAME_ROUND,
			':planet_x_coord' => $planet->getInfo('planet_x_coord'),
			':planet_y_coord' => $planet->getInfo('planet_y_coord')
		));

		// Move the country to this new planet
		$statement = $database->prepare("
			UPDATE `country` c
			SET    c.country_x_coord = :planet_x_coord,
			       c.country_y_coord = :planet_y_coord,
			       c.country_z_coord = :country_z_coord
			WHERE  c.round_id        = :round_id
			       AND
			       c.country_id      = :country_id
			LIMIT  1
		");
		// Execute the query
		$statement->execute(array(
			':planet_x_coord'  => $planet->getInfo('planet_x_coord'),
			':planet_y_coord'  => $planet->getInfo('planet_y_coord'),
			':country_z_coord' => $planet->getNextAvailableZCoord(),
			':round_id'        => GAME_ROUND,
			':country_id'      => $country->getInfo('country_id')
		));

		// Deduct one country from their current planet
		$statement = $database->prepare("
			UPDATE `planet` p
			SET    p.planet_country_count = p.planet_country_count - 1
			WHERE  p.round_id             = :round_id
			       AND
			       p.planet_x_coord       = :country_x_coord
			       AND
			       p.planet_y_coord       = :country_y_coord
			LIMIT  1
		");
		// Execute the query
		$statement->execute(array(
			':round_id' => GAME_ROUND,
			':country_x_coord' => $country->getInfo('country_x_coord'),
			':country_y_coord' => $country->getInfo('country_y_coord')
		));

		// Log this event
		Core_Log::add(array(
			'user_id'     => $country->getInfo('user_id'),
			'country_id'  => $country->getInfo('country_id'),
			'log_action'  => 'planet-relocate',
			'log_status'  => 'success',
			'log_message' => 'User successfully relocated their planet to ' . $planet->getInfo('planet_x_coord') . ':' . $planet->getInfo('planet_y_coord')
		));
	}
}