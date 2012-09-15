<?php
/**
 * Output a battle report for a country.
 *
 * A battle report is always generated in reference to a specific country, so they can
 * see the total defenders, attackers, and then their own ships.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       15/09/2012
 *
 * @todo Move the HTML into template sections.
 * @todo Outputting figures is really, really horrible, and almost guess work. Make nicer.
 * @todo Group together the countrys fleets - they may have more than one defending!
 */
class Core_BattleReport
{
	/**
	 * Output a battle report from a battle string held in the database.
	 *
	 * @access public
	 * @param $battleId int
	 * @param $countryId int
	 * @return string
	 */
	public function output($battleId, $countryId) {
		// Get the battle information
		$battleReport = Core_BattleReport::getBattleReport($battleId);

		// Could we find the battle report?
		if (! $battleReport) {
			return '<p>Sorry, we could not locate the battle report.';
		}

		// Get the country that this battle report was for
		$defendingCountry = new Core_Country($battleReport['country_id']);

		// Get the ships
		$ship = Core_Ship::getInstance();

		// Parse the battle string
		$battleReport = Core_BattleReport::parseBattleString($battleReport['battle_string']);

        // Set the header
		$template     = new Core_Template('battle_report/header.tpl');
		$reportString = $template->render();

		// Loop over each ship
		foreach ($ship as $shipId => $shipInformation) {
	        // Add the ship
	        $template      = new Core_Template('battle_report/body.tpl');
	        $reportString .= $template
	        	// Ship name
	        	->addVariable('ship_name', str_replace(' ', '&nbsp;', $ship[$shipId]['ship_name']))
				// Defender
				->addVariable('ship_defender_total',     number_format($battleReport[2][$shipId][0]))
				->addVariable('ship_defender_destroyed', number_format($battleReport[2][$shipId][1]))
				->addVariable('ship_defender_frozen',    number_format($battleReport[2][$shipId][2]))
				->addVariable('ship_defender_stolen',    number_format($battleReport[2][$shipId][3]))
				// Attacker
				->addVariable('ship_attacker_total',     number_format($battleReport[3][$shipId][0]))
				->addVariable('ship_attacker_destroyed', number_format($battleReport[3][$shipId][1]))
				->addVariable('ship_attacker_frozen',    number_format($battleReport[3][$shipId][2]))
				->addVariable('ship_attacker_stolen',    number_format($battleReport[3][$shipId][3]))
				// You
				->addVariable('ship_you_total',          number_format(0))
				->addVariable('ship_you_destroyed',      number_format(0))
				->addVariable('ship_you_frozen',         number_format(0))
				->addVariable('ship_you_stolen',         number_format(0))
				// Render
				->render();
		}

        // Echo out the footer
        $template      = new Core_Template('battle_report/footer.tpl');
        $reportString .= $template
        	// Defender totals
        	->addVariable('ship_defender_total',       number_format($battleReport[0][0]))
        	->addVariable('ship_defender_destroyed',   number_format($battleReport[0][1]))
        	->addVariable('ship_defender_frozen',      number_format($battleReport[0][2]))
        	->addVariable('ship_defender_stolen',      number_format($battleReport[0][3]))
        	// Attacker values
        	->addVariable('ship_attacker_total',       number_format($battleReport[1][0]))
        	->addVariable('ship_attacker_destroyed',   number_format($battleReport[1][1]))
        	->addVariable('ship_attacker_frozen',      number_format($battleReport[1][2]))
        	->addVariable('ship_attacker_stolen',      number_format($battleReport[1][3]))
        	// You
        	->addVariable('ship_you_total',            number_format(0))
        	->addVariable('ship_you_destroyed',        number_format(0))
        	->addVariable('ship_you_frozen',           number_format(0))
        	->addVariable('ship_you_stolen',           number_format(0))
        	// Resource
        	->addVariable('salvage_primary',           number_format($battleReport[0][4]))
        	->addVariable('salvage_secondary',         number_format($battleReport[0][5]))
        	->addVariable('defender_asteroids_total',  number_format($battleReport[1][4]))
        	->addVariable('defender_asteroids_stolen', number_format($battleReport[1][5]))
        	->addVariable('you_asteroids_stolen',      number_format(0))
        	// Render
        	->render();

        // And return the battle report
		return $reportString;
	}

	public function getBattleReport($battleId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		// Prepare the SQL
		$statement = $database->prepare("
			SELECT b.country_id, b.battle_string
			FROM   `battle` b
			WHERE  b.battle_id = :battle_id
			LIMIT  1
		");
		// Execute the query
		$statement->execute(array(
			':battle_id' => $battleId
		));

		// Did we find the country?
		if ($statement->rowCount() >= 1) {
			// Yes, set the information
			return $statement->fetch();
		}

		// An error occurred
		return false;
	}

	/**
	 * Convert the compressed battle string into an array.
	 * 
	 * First line is the defending totals
	 * Second line is the attacking totals
	 * Third line is the defending ship stats
	 * Forth line is the attacking ship stats
	 * Fifth line and beyond is the individual fleet stats
	 * 
	 * @access public
	 * @param $battleString string
	 * @return array
	 */
	public function parseBattleString($battleString) {
		// First, explode on new lines
		$battleString = explode("\n", $battleString);

		// Loop over the battle string
		foreach ($battleString as $lineIndex => $lineString) {
			// Which section are we parsing?
			// Defending and attacking totals
			if ($lineIndex <= 1) {
				// Just need to explode on pipes
				$battleStringReturn[$lineIndex] = explode('|', $lineString);
			}

			// Defending and attacking ship stats
			// Individual fleet stats
			else {
				// Split on comma
				$lineString = explode(',', $lineString);

				// If the string is for a country and fleet then set them
				if ($lineIndex >= 4) {
					$battleStringReturn[$lineIndex]['country_id'] = $lineString[0];
					$battleStringReturn[$lineIndex]['fleet_id']   = $lineString[1];
					unset($lineString[0], $lineString[1]);
				}

				// Now we have only ships
				// Loop over each one
				foreach ($lineString as $index => $shipString) {
					// Explode the string on colon so we have ship ID and total apart
					$shipString = explode(':', $shipString);

					// And add to the return report
					$battleStringReturn[$lineIndex][$shipString[0]] = explode('|', $shipString[1]);
				}
			}
		}

		// Parsting complete
		return $battleStringReturn;
	}
}