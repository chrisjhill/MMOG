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
 * @todo Outputting figures is really, really horrible, and almost guess work. Make nicer.
 * @todo Group together the countrys squadrons - they may have more than one defending!
 */
class Model_Battle_Report
{
	/**
	 * Details on the battle.
	 *
	 * <code>
	 * array(
	 *     'battle_id'     => 123,
	 *     'country_id'    => 321,
	 *     'battle_string' => '...'
	 * )
	 * </code>
	 *
	 * @access private
	 * @var array
	 */
	private $_battle;

	/**
	 * Information on what exactly happened in the battle.
	 * 
	 * @access private
	 * @var array
	 */
	private $_battleMatrix;

	/**
	 * Information on the defending country.
	 * 
	 * @access private
	 * @var Model_Country_Instance
	 */
	private $_defendingCountry;

	/**
	 * Information on the ships in this game.
	 *
	 * @access private
	 * @var Model_Fleet_Ship
	 */
	private $_ship;

	/**
	 * The report in HTML.
	 *
	 * @access private
	 * @var string
	 */
	private $_template;

	/**
	 * Output a battle report from a battle string held in the database.
	 *
	 * @access public
	 * @param $battleId int
	 * @param $countryId int
	 * @return string
	 */
	public function output($battleId, $countryId) {
		// Does this battle report exist in cache?
		// Grab an instance of the cache
		// We are not using a standard file, so pass false as the third parameter
		$cache = new Core_Cache('battlereport_' . $battleId . '.phtml', PATH_VIEW, false);
		// We want to use the cache, and give the life of cache a day
		$cache->setCache(true)->setCacheLife(86400);
		// Cache file already exists?
		if ($cache->cachedFileAvailable()) {
			// Yes, return instead of parsing
			return $cache->getCachedFile();
		}

		// We do not have this report in the cache
		// Get the battle information
		$this->_battle = $this->getBattleReport($battleId);

		// Could we find the battle report?
		if (! $this->_battle) {
			return '<p>Sorry, we could not locate the battle report.';
		}

		// Get the country that this battle report was for
		$this->_defendingCountry = new Model_Country_Instance($this->_battle['country_id']);

		// Get the ships
		$this->_ship = Model_Fleet_Ship::getInstance();

		// Parse the battle string
		$this->_battleMatrix = $this->parseBattleString($this->_battle['battle_string']);

        // Set the header
		$this->_template  = $this->generateHeader();
		$this->_template .= $this->generateBody();
		$this->_template .= $this->generateFooter();

        // Since we have generated the battle report we can now cache it
        $cache->saveFileToCache($this->_template);

        // And return the battle report
		return $this->_template;
	}

	/**
	 * Return the battle report stored in the database.
	 * 
	 * @access public
	 * @param $battleId int
	 * @return array
	 */
	public function getBattleReport($battleId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		// Prepare the SQL
		$statement = $database->prepare("
			SELECT b.battle_id, b.country_id, b.battle_string
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
	 * Generate the header of the battle report.
	 * 
	 * @access private
	 * @return string
	 */
	private function generateHeader() {
		$template = new Core_Snippet('BattleReport/header.phtml', PATH_SNIPPET);
		return $template->render();
	}

	/**
	 * Generate the body of the battle report.
	 * 
	 * @access private
	 * @return string
	 */
	private function generateBody() {
		// Store the HTML in a local variable
		$output = '';

		// Loop over each ship
		foreach ($this->_ship as $shipId => $shipInformation) {
	        // Add the ship
	        $template = new Core_Snippet('BattleReport/body.phtml', PATH_SNIPPET);
	        $output  .= $template
	        	// Ship name
	        	->addVariable('ship_name',               str_replace(' ', '&nbsp;', $shipInformation['ship_name']))
				// Defender
				->addVariable('ship_defender_total',     number_format($this->_battleMatrix[2][$shipId][0]))
				->addVariable('ship_defender_destroyed', number_format($this->_battleMatrix[2][$shipId][1]))
				->addVariable('ship_defender_frozen',    number_format($this->_battleMatrix[2][$shipId][2]))
				->addVariable('ship_defender_stolen',    number_format($this->_battleMatrix[2][$shipId][3]))
				// Attacker
				->addVariable('ship_attacker_total',     number_format($this->_battleMatrix[3][$shipId][0]))
				->addVariable('ship_attacker_destroyed', number_format($this->_battleMatrix[3][$shipId][1]))
				->addVariable('ship_attacker_frozen',    number_format($this->_battleMatrix[3][$shipId][2]))
				->addVariable('ship_attacker_stolen',    number_format($this->_battleMatrix[3][$shipId][3]))
				// You
				->addVariable('ship_you_total',          number_format(0))
				->addVariable('ship_you_destroyed',      number_format(0))
				->addVariable('ship_you_frozen',         number_format(0))
				->addVariable('ship_you_stolen',         number_format(0))
				// Render
				->render();
		}

		// And return
		return $output;
	}

	/**
	 * Generate the footer of the battle report.
	 * 
	 * @access private
	 * @return string
	 */
	private function generateFooter() {
        // Echo out the footer
        $template = new Core_Snippet('BattleReport/footer.phtml', PATH_SNIPPET);
        return $template
        	// Defender totals
        	->addVariable('ship_defender_total',       number_format($this->_battleMatrix[0][0]))
        	->addVariable('ship_defender_destroyed',   number_format($this->_battleMatrix[0][1]))
        	->addVariable('ship_defender_frozen',      number_format($this->_battleMatrix[0][2]))
        	->addVariable('ship_defender_stolen',      number_format($this->_battleMatrix[0][3]))
        	// Attacker values
        	->addVariable('ship_attacker_total',       number_format($this->_battleMatrix[1][0]))
        	->addVariable('ship_attacker_destroyed',   number_format($this->_battleMatrix[1][1]))
        	->addVariable('ship_attacker_frozen',      number_format($this->_battleMatrix[1][2]))
        	->addVariable('ship_attacker_stolen',      number_format($this->_battleMatrix[1][3]))
        	// You
        	->addVariable('ship_you_total',            number_format(0))
        	->addVariable('ship_you_destroyed',        number_format(0))
        	->addVariable('ship_you_frozen',           number_format(0))
        	->addVariable('ship_you_stolen',           number_format(0))
        	// Resource
        	->addVariable('salvage_primary',           number_format($this->_battleMatrix[0][4]))
        	->addVariable('salvage_secondary',         number_format($this->_battleMatrix[0][5]))
        	->addVariable('defender_asteroids_total',  number_format($this->_battleMatrix[1][4]))
        	->addVariable('defender_asteroids_stolen', number_format($this->_battleMatrix[1][5]))
        	->addVariable('you_asteroids_stolen',      number_format(0))
        	// Render
        	->render();
	}

	/**
	 * Convert the compressed battle string into an array.
	 * 
	 * <code>
	 * <ul>
	 *     <li>First line is the defending totals</li>
	 *     <li>Second line is the attacking totals</li>
	 *     <li>Third line is the defending ship stats</li>
	 *     <li>Forth line is the attacking ship stats</li>
	 *     <li>Fifth line and beyond is the individual fleet stats</li>
	 * </ul>
	 * </code>
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