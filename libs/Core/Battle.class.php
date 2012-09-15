<?php
/**
 * Battle script.
 *
 * Developed as part of a <abbr title="Massively Multiplayer Online Game">MMOG</abbr>,
 * this engine is capable of taking an attacking and defending fleet and battling them
 * against each other. The engine is capable of freezing, stealing and destroying the
 * opponents ships.
 * 
 * @todo Work out losses/gains/etc. for each fleet (currently only for attacking/defending).
 * @todo Run off a database instead of being hard coded.
 * @todo produceWaveReport() needs major rework. Needs to be stored "nicely" in db.
 * @todo Store the battle reports in a database.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       10/09/2012
 */
class Core_Battle
{
    /**
     * Contains information on the ship statistics.
     *
     * Note: The index is the order of fire.
     *
     * <code>
     * array(
     *     0 => array(
     *         'name'          => The name of the ship. 
     *         'type'          => Whether this is a basic, EMP, Steal, Salvage or a Pod.
     *         'class'         => What class of ship it is.
     *         'target'        => Which class of ship it is designed to target.
     *         'life'          => How much damage this ship can sustain before being inoperable.
     *         'attack'        => How much damage this ship can deal.
     *         'primaryCost'   => How much primary material this ship costs to purchase.
     *         'secondaryCost' => How much secondary material this ship costs to purchase.
     *     ),
     *     1 => array(
     *        ...
     *     ),
     *     2 => array(
     *        ...
     *     )
     * )
     * </code>
     *
     * @access private
     * @var array
     */
    private $_ship = array();
    
    /**
     * Contains information on which class has which ships.
     * 
     * We set this information once for speed, so we do not have to loop over
     * over each ship every time when we only need a couple.
     *
     * Also contains informaton on attacking and defending ship class totals so
     * we can work out how to spread the total attack power.
     *
     * Note: Ship classes are stored as bitwise operators, so although the docs
     * will state _shipMatrix['Frigate'], that would actually be _shipMatrix[1]
     * for readability reasons.
     *
     * <code>
     * array(
     *     'class' => array(
     *         'Frigate' => array(
     *             0 => 1,
     *             1 => 2
     *         ),
     *         'Destroyer' => array(
     *             0 => 1,
     *             1 => 2
     *         )
     *     ),
     *     'defending' => array(
     *         'Frigate'   => 12345,
     *         'Destroyer' => 54321
     *     ),
     *     'attacking' => array(
     *         'Frigate'   => 56789,
     *         'Destroyer' => 98765
     *     )
     * )
     * </code>
     *
     * @access private
     * @var array
     */
    private $_shipMatrix = array();

    /**
     * <code>
     * array(
     *     'attacking' => array(
     *         0 => array(
     *             'country_id' => 12345,
     *             'fleet_id'   => 1,
     *             'fleet'      => array(0 => 12345, 1 => 67890, 2 => 13579, 3 => 24680)
     *         ),
     *         1 => array(
     *             'country_id' => 67890,
     *             'fleet_id'   => 3,
     *             'fleet'      => array(0 => 12345, 3 => 24680)
     *         )
     *     ),
     *     'defending' => array(
     *         0 => array(
     *             'country_id' => 54321,
     *             'fleet_id'   => 1,
     *             'fleet'      => array(3 => 24680)
     *         )
     *     )
     * )
     * </code>
     *
     * @access private
     * @var array
     */
    private $_fleet = array();
    
    /**
     * Information on the defending country.
     * 
     * @access private
     * @var Core_Country
     */
    private $_defendingCountry = array();
    
    /**
     * The defending entities ships.
     * 
     * <code>
     * array(
     *     0 => 1234,
     *     1 => 5678,
     *     2 => 9012,
     *     3 => 3456
     * )
     * </code>
     * 
     * @access private
     * @var array
     */
    private $_defendingShips = array();
    
    /**
     * Information on the attacking country such as how many
     * asteroids they have stolen.
     * 
     * <code>
     * array(
     *     'asteroid_count'   => 12345
     * )
     * </code>
     * 
     * @access private
     * @var array
     */
    private $_attackingCountry = array();

    /**
     * The attacking entities ships.
     * 
     * <code>
     * array(
     *     0 => 1234,
     *     1 => 5678,
     *     2 => 9012,
     *     3 => 3456
     * )
     * </code>
     * 
     * @access private
     * @var array
     */
    private $_attackingShips = array();

    /**
     * How much primary salvage the defending country managed to reclaim.
     *
     * @access private
     * @var int
     */
    private $_salvagePrimaryReclaimed = 0;

    /**
     * How much secondary salvage the defending country managed to reclaim.
     *
     * @access private
     * @var int
     */
    private $_salvageSecondaryReclaimed = 0;

    /**
     * Sets all the parameters of the battle engine.
     *
     * This script would normally do a lot more, but has been stripped back
     * for this example.
     *
     * @access public
     */
    public function __construct() {
        // Set ship statictics
        $this->setShipStatistics();
    }

    /**
     * Set all of the ship information.
     * 
     * <code>
     * array(
     *     'name'          => The name of the ship. 
     *     'class'         => What class of ship it is.
     *     'target'        => Which class of ship it is designed to target.
     *     'life'          => How much damage this ship can sustain before being inoperable.
     *     'attack'        => How much damage this ship can deal.
     *     'primaryCost'   => How much primary material this ship costs to purchase.
     *     'secondaryCost' => How much secondary material this ship costs to purchase.
     * )
     * </code>
     *
     * @access private
     */
    private function setShipStatistics() {
        // These are the ship statistics
        // @todo These would normally be stored in a database, but for this example they are hard-coded.
        $this->_ship = Core_Ship::getInstance();
        
        // Set the ship classes
        foreach ($this->_ship as $shipId => $shipInformation) {
            // Set which ships are in which class
            $this->_shipMatrix['ship_class'][$shipInformation['ship_class']][] = $shipId;
            
            // Set attacking and defending class life total
            $this->_shipMatrix['defending'][$shipInformation['ship_class']] = 0;
            $this->_shipMatrix['attacking'][$shipInformation['ship_class']] = 0;
        }
    }

    /**
     * Sets all of the defending country information.
     *
     * This function also works out the defending and attacking fleets
     * and fires starts the battle sequence.
     *
     * @access public
     */
    public function initiateWave() {
        // Set country informati_shipMatrixon
        $this->setDefendingCountryInformation();
        
        // Set attacker and defender information and their fleets
        $battleStatus = $this->setFleets();

        // Can we actually have a battle?
        // There might not be any fleets, or there may not be any attackers
        if (! $battleStatus) {
            return false;
        }

        // Group attackers and defenders together
        $this->setFleetTotals();

        // Set the ship matrix
        $this->setShipClassLifeTotals();
        
        // Start battle
        $this->doBattle();
    }

    /**
     * Set the information about the defending country such as asteroid count.
     * 
     * @access private
     */
    private function setDefendingCountryInformation() {
        // Set information about the defending country
        // These would normally be stored in a database, but for this example they are hard-coded 
        $this->_defendingCountry = new Core_Country(1);

        // Get all of the fleets this country has
        $fleetList = $this->_defendingCountry->getFleet(0);

        // Loop over them, and if they are currently docked add them to the defender list
        foreach ($fleetList as $fleetId => $fleet) {
            $this->_fleet['defending'][] = $fleet;
        }
    }

    /**
     * Set the initial fleet stats for the attacking and defending countries.
     *
     * @todo Needs to grab data from a database.
     * @access private
     */
    private function setFleets() {
        // Get the missions that are due to wage battle on this country
        // They need to have arrived (ETA 0), have waves remaining, and not have a status of Returning
        $database  = Core_Database::getInstance();
        $statement = $database->prepare("
            SELECT m.country_id, m.fleet_id, m.mission_status
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
            ':country_id' => $this->_defendingCountry->getInfo('country_id')
        ));

        // Were there any fleets?
        if ($statement->rowCount() <= 0) {
            // There were no fleets, we can't have a battle
            return false;
        }

        // Loop over each of the missions and set them
        while ($mission = $statement->fetch()) {
            // Get the country information
            $country = new Core_Country($mission['country_id']);

            // Get the fleet this country has sent
            $fleet = $country->getFleet($mission['fleet_id']);

            // Loop over them, and if they are currently docked add them to the defender list
            $this->_fleet[$mission['mission_status'] == 'A' ? 'attacking' : 'defending'][] = $fleet;
        }

        // The battle has an attacker, right?
        return count($this->_fleet['attacking']) >= 1;
    }

    /**
     * We group defenders as one and attackers as one. This means we do not have to worry
     * about individual fleets during the battle function and can instead focus on the
     * logic. We can work out the individual fleet stats afterwards.
     *
     * @access private
     */
    private function setFleetTotals() {
        // Loop over each of the attackers and defenders
        foreach ($this->_fleet as $status => $fleets) {
            // Loop over each fleet
            foreach ($fleets as $index => $fleet) {
                // And loop over each ship that this fleet can contain
                foreach ($this->_ship as $shipId => $ship) {
                    // Create side to add this fleet to
                    $tempSide = '_' . $status . 'Ships';

                    // Add fleet to attacking or defending totals
                    if (! isset($this->{$tempSide}[$shipId])) {
                        // This ship does not currently exist, add it
                        $this->{$tempSide}[$shipId] = array(
                            'ship_total'     => (int)$fleet->getInfo($shipId),
                            'ship_frozen'    => 0,
                            'ship_stolen'    => 0,
                            'ship_destroyed' => 0
                        );
                    } else {
                        // This ship already exists in this fleet, add to total
                        $this->{$tempSide}[$shipId]['ship_total'] += (int)$fleet->getInfo($shipId);
                    }
                }
            }
        }
    }

    /**
     * How much life each ship class has.
     *
     * We tally this up because we can then spread damage evenly across each ship
     * in that class without having to work it out each time.
     *
     * @access private
     */
    private function setShipClassLifeTotals() {
        // Loop over each ship that we have
        foreach ($this->_ship as $shipId => $shipInformation) {
            // We want to add the life total of all the ships remaining after this wave
            // So we want the total minus any ships stolen and destroyed
            // Frozen ships are unfrozen after each wave, so we still need to count them
            $this->_shipMatrix['defending'][$shipInformation['ship_class']] += ($this->getCountryShipNumber('defending', $shipId, array('stolen', 'destroyed')) * $this->_ship[$shipId]['ship_life']);
            $this->_shipMatrix['attacking'][$shipInformation['ship_class']] += ($this->getCountryShipNumber('attacking', $shipId, array('stolen', 'destroyed')) * $this->_ship[$shipId]['ship_life']);
        }
    }

    /**
     * Start the battle process.
     * 
     * This is a complex process. We need to loop over each ship in their
     * order of fire. All ships have a different order of fire - you cannot
     * have two or more ships firing at the same time.
     * 
     * We first start by making sure that the defending or attacking country
     * have this ship - no point in continuing if nothing will happen. We 
     * also make sure that even if they have these ships that one of them
     * can actually target another ship.
     * 
     * We then work out the total attack of the ships by evaluating how many
     * they have remaining (deducting frozen, stolen, and destroyed) and then
     * and multiplying it by their attack. We then loop over each ship they can
     * attack and work out what percentage that ship is in relation to the other
     * ships in their class. This percentage is based on life.
     * 
     * E.g., 1 ship with 500 life will receive 50% of the attack firepower
     * against 500 ships with 1 life.
     * 
     * Now we know what percentage of the attack to use against this ship we can
     * then work out how many it will kill and make sure that doesn't exceed the
     * amount that have already been destroyed/frozen/stolen, and then document
     * that in the attacking and defending entities ship information.
     * 
     * After the wave has finished (i.e., all ships have fired) we produce a battle
     * report of what happened.
     * 
     * @access private
     */
    private function doBattle() {
        // Loop over each ship
        foreach ($this->_ship as $shipId => $shipInformation) {
            // We do not want to do the Asteroid class just yet
            if ($shipInformation['ship_target'] & SHIP_CLASS_POD || $shipInformation['ship_target'] & SHIP_CLASS_SALVAGE) {
                continue;
            }
            
            // Does either side have any of this ship?
            if ($this->getCountryShipNumber('defending', $shipId, array('frozen', 'stolen', 'destroyed')) <= 0 && $this->getCountryShipNumber('attacking', $shipId, array('frozen', 'stolen', 'destroyed')) <= 0) {
                continue;
            }
            
            // Does either side have any ships that this ship targets?
            if ($this->_shipMatrix['defending'][$shipInformation['ship_target']] <= 0 && $this->_shipMatrix['attacking'][$shipInformation['ship_target']] <= 0) {
                continue;
            }
            
            // Start gathering the stats
            $totalDefendingAttack = $this->getCountryShipNumber('defending', $shipId, array('frozen', 'stolen', 'destroyed')) * $shipInformation['ship_attack'];
            $totalAttackingAttack = $this->getCountryShipNumber('attacking', $shipId, array('frozen', 'stolen', 'destroyed')) * $shipInformation['ship_attack'];
            
            // So we do not have to repeat ourselves 3 times below, set some variables that contain the different logic
            if ($this->_ship[$shipId]['ship_type'] & SHIP_TYPE_EMP) {
                // This ship freezes other ships so they cannot attack this wave
                $shipToDeductStats = array('frozen', 'stolen', 'destroyed');
                $shipToAddToStats  = 'frozen';
            } else if ($this->_ship[$shipId]['ship_type'] & SHIP_TYPE_STEAL) {
                // This ship steals other ships to fight against the opponant on the next wave
                $shipToDeductStats = array('stolen', 'destroyed');
                $shipToAddToStats  = 'stolen';
            } else {
                // This ship destroys other ships
                $shipToDeductStats = array('stolen', 'destroyed');
                $shipToAddToStats  = 'destroyed';
            }
            
            // Loop over each ship in the target class
            foreach ($this->_shipMatrix['ship_class'][$shipInformation['ship_target']] as $void => $shipIdTargeted) {
                // What percentage is this ship in relation to the rest of the class?
                // We want to get it at the start of the wave to apply the percentage evenly
                $defendingLifePercentage = 0;
                $defendingAttackTotal    = 0;
                $attackingLifePercentage = 0;
                $attackingAttackTotal    = 0;
        
                // Does the country actually have any ships in the class we are targeting?
                // After this we will know the percentage of attack to apply to this ship
                if ($this->_shipMatrix['defending'][$shipInformation['ship_target']] > 0) {
                    // Defenders have some ships in this target class
                    $attackingLifePercentage = (($this->getCountryShipNumber('defending', $shipIdTargeted, array('frozen', 'stolen', 'destroyed')) * $this->_ship[$shipIdTargeted]['ship_life']) / $this->_shipMatrix['defending'][$shipInformation['ship_target']]) * 100;
                }
                if ($this->_shipMatrix['attacking'][$shipInformation['ship_target']] > 0) {
                    // Attackers have some ships in this target class
                    $defendingLifePercentage = (($this->getCountryShipNumber('attacking', $shipIdTargeted, array('frozen', 'stolen', 'destroyed')) * $this->_ship[$shipIdTargeted]['ship_life']) / $this->_shipMatrix['attacking'][$shipInformation['ship_target']]) * 100;
                }
        
                // Work out the attack total based on that percentage
                if ($defendingLifePercentage > 0) { $defendingAttackTotal = round(($totalDefendingAttack / 100) * $defendingLifePercentage); }
                if ($attackingLifePercentage > 0) { $attackingAttackTotal = round(($totalAttackingAttack / 100) * $attackingLifePercentage); }
        
                // How many ships will that destroy?
                // This does not take into account that the opponant may not have this many ships...
                // only that we could destroy that amount with the attack we have.
                $defendingDestroyed = floor($defendingAttackTotal / $this->_ship[$shipIdTargeted]['ship_life']);
                $attackingDestroyed = floor($attackingAttackTotal / $this->_ship[$shipIdTargeted]['ship_life']);
        
                // The ship we are attacking with is a ship that can destroy the opponants ships
                // We can only destroy those ships that have not already been destroyed or stolen
                // We can still destroy ships that have been frozen
                // Work out how many that is
                if ($defendingDestroyed > $this->getCountryShipNumber('attacking', $shipIdTargeted, $shipToDeductStats)) {
                    // Defenders destroyed more ships than the attackers have remaining
                    // Bring that down to the maximum possible
                    $defendingDestroyed = $this->getCountryShipNumber('attacking', $shipIdTargeted, $shipToDeductStats);
                }
                if ($attackingDestroyed > $this->getCountryShipNumber('defending', $shipIdTargeted, $shipToDeductStats)) {
                    // Attackers destroyed more ships than the defenders have remaining
                    // Bring that down to the maximum possible
                    $attackingDestroyed = $this->getCountryShipNumber('defending', $shipIdTargeted, $shipToDeductStats);
                }
        
                // Add the ships that we just destroyed to their destroyed total in this wave
                $this->_attackingShips[$shipIdTargeted]['ship_' . $shipToAddToStats] += $defendingDestroyed;
                $this->_defendingShips[$shipIdTargeted]['ship_' . $shipToAddToStats] += $attackingDestroyed;
            }
        }
        
        // Asteroid stealing, and salvage reclaiming
        $this->doAsteroidStealing();
        $this->doSalvageReclaiming();
        
        // Produce a battle report
        $this->produceWaveReport();
    }

    /**
     * Get the number of ships based on several factors.
     * 
     * The benefit of this class is that we are not continually adding/subtracting
     * in the main battle function with potential for errros. This function will
     * give consistent results far better and make the code neater overall.
     *
     * @param $attackerOrDefender Which ship number do we need?
     * @param $shipId Which ships are we referring to?
     * @param $shipsToDeduct Do we want to deduct frozen/stolen/destroyed from the total?
     * @access private
     * @return int
     */
    private function getCountryShipNumber($attackerOrDefender, $shipId, $shipsToDeduct) {
        // Add Ships to the end of attacking or defending
        $attackerOrDefender = '_' . $attackerOrDefender . 'Ships';

        // The starting number of ships
        $shipCount = $this->{$attackerOrDefender}[$shipId]['ship_total'];

        // And then deduct as necessary
        foreach ($shipsToDeduct as $index => $typeToDeduct) {
            $shipCount = $shipCount - $this->{$attackerOrDefender}[$shipId]['ship_' . $typeToDeduct];
        }

        // And return the number of ships after all of the deductiosn
        // But first we need to make sure it is 0 or greater. The reason...
        // this can happen is because you could have 100 ships, 100 could
        // be frozen, but 100 could also be destroyed giving you -100.
        return $shipCount >= 0
            ? $shipCount
            : 0;
    }

    /**
     * Steals asteroids from the defending country.
     *
     * We can only steal a maximum percentage of asteroids per wave.
     *
     * @access private
     */
    private function doAsteroidStealing() {
        // What is the potential maximum asteroids we can steal?
        $asteroidMaximumSteal = floor(($this->_defendingCountry->getInfo('asteroid_count') / 100) * GAME_ASTEROID_MAX_CAP);

        // How many asteroids can the attacking country actually steal?
        // Set the total attack variable
        $totalAttackingAttack = 0;

        // Need to loop over each ship to see if it can steal asteroids
        foreach ($this->_ship as $shipId => $shipInformation) {
            // Is this an asteroid stealing ship?
            if ($shipInformation['ship_type'] & SHIP_TYPE_POD) {
                // Add how many asteroids this ship can steal
                $totalAttackingAttack += $this->getCountryShipNumber('attacking', $shipId, array('frozen', 'stolen', 'destroyed')) * $shipInformation['ship_attack'];
            }
        }

        // How many asteroids can the attacking country steal?
        $asteroidsStolen = floor($totalAttackingAttack / GAME_ASTEROID_LIFE);

        // Is this more than the attacker is allowed to steal?
        if ($asteroidsStolen > $asteroidMaximumSteal) {
            $asteroidsStolen = $asteroidMaximumSteal;
        }

        // Set the asteroids stolen
        $this->_attackingCountry['asteroid_count'] = $asteroidsStolen;
    }

    /**
     * Reclaims salvage from destroyed ships.
     *
     * We can only reclaim a maximum amount of salvage per wave.
     *
     * @access private
     */
    private function doSalvageReclaiming() {
        // Total amount of salvage possible
        $totalPrimarySalvage   = 0;
        $totalSecondarySalvage = 0;
        
        // Total amount of salvage that the defender can reclaim
        $totalSalvageShipsReclaimable = 0;
        
        // Loop through each ship and tally up the salvage possible
        foreach ($this->_ship as $shipId => $shipInformation) {
            // How many ships were destroyed?
            $totalDestroyedShips = $this->_defendingShips[$shipId]['ship_destroyed'] + $this->_attackingShips[$shipId]['ship_destroyed'];
    
            // Were any of these ships actually destroyed?
            if ($totalDestroyedShips >= 1) {
                // Add to the salvage
                $totalPrimarySalvage   += $totalDestroyedShips * $shipInformation['ship_primary_cost'];
                $totalSecondarySalvage += $totalDestroyedShips * $shipInformation['ship_secondary_cost'];
            }
    
            // If this is a salvage ship then add up how much it can hold
            if ($shipInformation['ship_type'] & SHIP_TYPE_SALVAGE) {
                $totalSalvageShipsReclaimable += $this->getCountryShipNumber('defending', $shipId, array('frozen', 'stolen', 'destroyed')) * $shipInformation['ship_attack'];
            }
        }
        
        // Was there any salvage?
        if ($totalPrimarySalvage <= 0) {
            return false;
        }
        
        // Get a percentage that is reclaimable
        $totalPrimarySalvage   = ($totalPrimarySalvage   / 100) * GAME_SALVAGE_PRIMARY_RECLAIMABLE;
        $totalSecondarySalvage = ($totalSecondarySalvage / 100) * GAME_SALVAGE_SECONDARY_RECLAIMABLE;
        
        // We want to get an even spread of primary to secondary
        $percentageAsPrimary = ($totalPrimarySalvage / ($totalPrimarySalvage + $totalSecondarySalvage)) * 100;
        
        // Work out the maximum we can get for primary and secondary based on this percentage
        $totalSalvageShipsPrimaryReclaimable   = ($totalSalvageShipsReclaimable / 100) * $percentageAsPrimary;
        $totalSalvageShipsSecondaryReclaimable = $totalSalvageShipsReclaimable - $totalSalvageShipsPrimaryReclaimable;
        
        // Do these exceed the amount of salvage there actually is?
        // Primary resource
        if ($totalSalvageShipsPrimaryReclaimable > $totalPrimarySalvage) {
            $totalSalvageShipsPrimaryReclaimable = $totalPrimarySalvage;
        }
        // Secondary resource
        if ($totalSalvageShipsSecondaryReclaimable > $totalSecondarySalvage) {
            $totalSalvageShipsSecondaryReclaimable = $totalSecondarySalvage;
        }
        
        // And save the salvage
        $this->_salvagePrimaryReclaimed   = $totalSalvageShipsPrimaryReclaimable;
        $this->_salvageSecondaryReclaimed = $totalSalvageShipsSecondaryReclaimable;
    }

    /**
     * Produces output of what has just happened.
     *
     * Echo's out a table of stats - this would normally be inserted into a
     * database so the entities can view it.
     *
     * @access private
     */
    private function produceWaveReport() {
        // Set variables
        $defendingTotal     = 0;
        $defendingDestroyed = 0;
        $defendingFrozen    = 0;
        $defendingStolen    = 0;
        // Attacking
        $attackingTotal     = 0;
        $attackingDestroyed = 0;
        $attackingFrozen    = 0;
        $attackingStolen    = 0;
        
        // Echo out the header
        echo '
            <div id="wave1" class="wave_report">
                <table>
                    <tr>
                        <td></td>
                        <th colspan="4" style="text-align:center">Defending</th>
                        <th colspan="4" style="text-align:center">Attacking</th>
                    </tr>
                    <tr>
                        <th>Ship</th>
                        <th style="width:85px">Total</th>
                        <th style="width:85px">Destroyed</th>
                        <th style="width:85px">Frozen</th>
                        <th style="width:85px;border-right:2px solid #DDD">Stolen</th>
                        <th style="width:85px">Total</th>
                        <th style="width:85px">Destroyed</th>
                        <th style="width:85px">Frozen</th>
                        <th style="width:85px">Stolen</th>
                    </tr>';
        
        // Loop over each ship
        foreach ($this->_ship as $shipId => $shipInformation) {
            // Tally up
            // Defending
            $defendingTotal     += $this->_defendingShips[$shipId]['ship_total'];
            $defendingDestroyed += $this->_defendingShips[$shipId]['ship_destroyed'];
            $defendingFrozen    += $this->_defendingShips[$shipId]['ship_frozen'];
            $defendingStolen    += $this->_defendingShips[$shipId]['ship_stolen'];
            // Attacking
            $attackingTotal     += $this->_attackingShips[$shipId]['ship_total'];
            $attackingDestroyed += $this->_attackingShips[$shipId]['ship_destroyed'];
            $attackingFrozen    += $this->_attackingShips[$shipId]['ship_frozen'];
            $attackingStolen    += $this->_attackingShips[$shipId]['ship_stolen'];
            
            // Echo out
            echo '
                <tr>
                    <th>' . $shipInformation['ship_name'] . '</th>
                    <td>' . number_format($this->_defendingShips[$shipId]['ship_total'])     . '</td>
                    <td>' . number_format($this->_defendingShips[$shipId]['ship_destroyed']) . '</td>
                    <td>' . number_format($this->_defendingShips[$shipId]['ship_frozen'])    . '</td>
                    <td style="border-right:2px solid #DDD">' . number_format($this->_defendingShips[$shipId]['ship_stolen']) . '</td>
                    <td>' . number_format($this->_attackingShips[$shipId]['ship_total'])     . '</td>
                    <td>' . number_format($this->_attackingShips[$shipId]['ship_destroyed']) . '</td>
                    <td>' . number_format($this->_attackingShips[$shipId]['ship_frozen'])    . '</td>
                    <td>' . number_format($this->_attackingShips[$shipId]['ship_stolen'])    . '</td>
                </tr>';
        }
        
        // Echo out the footer
        echo '
                <tr>
                    <th>Totals</th>
                    <th>' . number_format($defendingTotal)     . '</th>
                    <th>' . number_format($defendingDestroyed) . '</th>
                    <th>' . number_format($defendingFrozen)    . '</th>
                    <th style="border-right:2px solid #DDD">' . number_format($defendingStolen) . '</th>
                    <th>' . number_format($attackingTotal)     . '</th>
                    <th>' . number_format($attackingDestroyed) . '</th>
                    <th>' . number_format($attackingFrozen)    . '</th>
                    <th>' . number_format($attackingStolen)    . '</th>
                </tr>
                <tr>
                    <th>&nbsp;</th>
                    <th colspan="4">Defenders salvaged ' . number_format($this->_salvagePrimaryReclaimed) . ' primary and ' . number_format($this->_salvageSecondaryReclaimed) . ' secondary.</th>
                    <th colspan="4">Attackers have stolen ' . $this->_attackingCountry['asteroid_count'] . ' asteroids.</td>
                </tr>
            </table>
        </div>';
    }
}