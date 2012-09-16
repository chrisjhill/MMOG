<?php
/**
 * Returns information on a single mission, or a group of missions at a country.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       16/09/2012
 */
class Model_Mission
{
	/**
	 * Get mission for a single fleet.
	 *
	 * @access public
	 * @param int $countryId
	 * @param int $fleetId
	 */
	public function getFleet($countryId, $fleetId) {
        // Get the missions that are due to wage battle on this country
        // They need to have arrived (ETA 0), have waves remaining, and not have a status of Returning
        $database  = Core_Database::getInstance();
        $statement = $database->prepare("
            SELECT m.mission_id, m.country_id, m.fleet_id, m.mission_destination_country_id,
                   m.mission_status, m.mission_eta, m.mission_wave_length,
                   m.mission_created
            FROM   `mission` m
            WHERE  m.country_id = :country_id
                   AND
                   m.fleet_id   = :fleet_id
        ");

        // Execute the query
        $statement->execute(array(
            ':country_id' => $countryId,
            ':fleet_id'   => $fleetId
        ));

        // Return the missions
        return Model_Mission::prepareMissions($statement);
	}

	/**
	 * Get mission by country ID.
	 *
	 * Get all of the missions for a country.
	 *
	 * @access public
	 * @param int $countryId
	 */
	public function getCountry($countryId) {
        // Get the missions that are due to wage battle on this country
        // They need to have arrived (ETA 0), have waves remaining, and not have a status of Returning
        $database  = Core_Database::getInstance();
        $statement = $database->prepare("
            SELECT m.mission_id, m.country_id, m.fleet_id, m.mission_destination_country_id,
                   m.mission_status, m.mission_eta, m.mission_wave_length,
                   m.mission_created
            FROM   `mission` m
            WHERE  m.country_id = :country_id
        ");

        // Execute the query
        $statement->execute(array(
            ':country_id' => $countryId
        ));

        // Return the missions
        return Model_Mission::prepareMissions($statement);
	}

	/**
	 * Get mission by country ID.
	 *
	 * Get an overview on all the incoming and outgoing fleets to a country.
	 * Used in the battle and radar code.
	 *
	 * @access public
	 * @param int $countryId
	 * @return array
	 */
	public function getBattle($countryId) {
        // Get the missions that are due to wage battle on this country
        // They need to have arrived (ETA 0), have waves remaining, and not have a status of Returning
        $database  = Core_Database::getInstance();
        $statement = $database->prepare("
            SELECT m.mission_id, m.country_id, m.fleet_id, m.mission_destination_country_id,
                   m.mission_status, m.mission_eta, m.mission_wave_length,
                   m.mission_created
            FROM   `mission` m
            WHERE  m.mission_destination_country_id = :country_id
                   AND
                   m.mission_eta                    = 0
                   AND 
                   m.mission_wave_length            > 0
                   AND
                   m.mission_status                != 'R'
        ");

        // Execute the query
        $statement->execute(array(
            ':country_id' => $countryId
        ));

        // Return the missions
        return Model_Mission::prepareMissions($statement);
	}

	/**
	 * Standardise the missions that are returned.
	 *
	 * @access public
	 * @param $statement PDO
	 * @return array
	 */
	public function prepareMissions($statement) {
        // Were there any fleets?
        if ($statement->rowCount() <= 0) {
            // There were no fleets, we can't have a battle
            return false;
        }

        // Loop over each of the missions and set them
        $missions = array();
        while ($mission = $statement->fetch()) {
        	$missions[] = $mission;
        }

        // And return the missions
        return $missions;
	}
}