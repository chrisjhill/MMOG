<?php
/**
 * Battle script.
 *
 * Developed as part of a <abbr title="Massively Multiplayer Online Game">MMOG</abbr>,
 * this engine is capable of taking an attacking and defending squadrons and battling them
 * against each other. The engine is capable of freezing, stealing and destroying the
 * opponents ships.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       10/09/2012
 *
 * @todo Work out how many asteroids each squadron stole.
 * @todo produceBattleReport() needs a bit of refactoring. It's a bit long winded.
 * @todo Update squadron upon end of battle.
 * @todo Insert news item for each country.
 */
class Model_Battle_Fight
{
    /**
     * The ID of this battle.
     *
     * This is only ever set once the battle is complete and we have the battle string.
     *
     * @private
     * @var int
     */
    private $_battleId;

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
     *     'defending' => array(
     *         0 => Model_Fleet_Squadron
     *     ),
     *     'attacking' => array(
     *         0 => Model_Fleet_Squadron,
     *         1 => Model_Fleet_Squadron
     *     )
     * )
     * </code>
     *
     * @access private
     * @var array
     */
    private $_squadron = array();

    /**
     * Stats on the outcome of each squadron.
     *
     * @access private
     * @var array
     */
    private $_squadronStats = array();
    
    /**
     * Information on the defending country.
     * 
     * @access private
     * @var Model_Country_Instance
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
     * The quantity of asteroids the attacker has stolen.
     * 
     * @access private
     * @var int
     */
    private $_asteroidsStolen = 0;

    /**
     * Sets all the parameters of the battle engine.
     *
     * This script would normally do a lot more, but has been stripped back
     * for this example.
     *
     * @access public
     * @param $defendingCountryId int
     */
    public function __construct($defendingCountryId) {
        // Set country informati_shipMatrixon
        $this->setDefendingCountryInformation($defendingCountryId);

        // Set ship statictics
        $this->setShipStatistics();
    }

    /**
     * Set the information about the defending country such as asteroid count.
     * 
     * @access private
     * @param $defendingCountryId int
     */
    private function setDefendingCountryInformation($defendingCountryId) {
        // Set information about the defending country
        // These would normally be stored in a database, but for this example they are hard-coded 
        $this->_defendingCountry = new Model_Country_Instance($defendingCountryId);

        // Get all of the squadrons this country has
        $squadronList = $this->_defendingCountry->getSquadron(0);

        // Loop over them, and if they are currently docked add them to the defender list
        foreach ($squadronList as $squadronId => $squadron) {
            if ($squadron->getInfo('squadron_status') & FLEET_DOCKED) {
                $this->_squadron['defending'][] = $squadron;
            }
        }
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
        $this->_ship = Model_Fleet_Ship::getInstance();
        
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
     * This function also works out the defending and attacking squadrons
     * and fires starts the battle sequence.
     *
     * @access public
     */
    public function initiateWave() {
        // Set attacker and defender information and their squadrons
        $battleStatus = $this->setSquadrons();

        // Can we actually have a battle?
        // There might not be any squadrons, or there may not be any attackers
        if (! $battleStatus) {
            return false;
        }

        // Group attackers and defenders together
        $this->setSquadronTotals();

        // Set the ship matrix
        $this->setShipClassLifeTotals();
        
        // Start battle
        $this->doBattle();
    }

    /**
     * Set the initial squadron stats for the attacking and defending countries.
     *
     * @todo Needs to grab data from a database.
     * @access private
     */
    private function setSquadrons() {
        // Get the missions
        $missions = Model_Fleet_Mission::getBattle($this->_defendingCountry->getInfo('country_id'));

        // Loop over each of the missions and set them
        foreach ($missions as $mission) {
            // Get the country information
            $country = new Model_Country_Instance($mission['country_id']);

            // Get the squadron this country has sent
            $squadron = $country->getSquadron($mission['squadron_id']);

            // Loop over them, and if they are currently docked add them to the defender list
            $this->_squadron[$mission['mission_status'] == 'A' ? 'attacking' : 'defending'][] = $squadron;
        }

        // The battle has an attacker, right?
        return count($this->_squadron['attacking']) >= 1;
    }

    /**
     * We group defenders as one and attackers as one. This means we do not have to worry
     * about individual squadrons during the battle function and can instead focus on the
     * logic. We can work out the individual squadrons stats afterwards.
     *
     * @access private
     */
    private function setSquadronTotals() {
        // Loop over each of the attackers and defenders
        foreach ($this->_squadron as $status => $squadrons) {
            // Loop over each squadron
            foreach ($squadrons as $index => $squadron) {
                // And loop over each ship that this squadron can contain
                foreach ($this->_ship as $shipId => $ship) {
                    // First things first, add to squadron stats
                    $this->_squadronStats[$squadron->getInfo('country_id')]
                                      [$squadron->getInfo('squadron_id')]
                                      [$shipId] = array('ship_destroyed' => 0, 'ship_frozen' => 0, 'ship_stolen' => 0);

                    // Create side to add this squadron to
                    $tempSide = '_' . $status . 'Ships';

                    // Add squadron to attacking or defending totals
                    if (! isset($this->{$tempSide}[$shipId])) {
                        // This ship does not currently exist, add it
                        $this->{$tempSide}[$shipId] = array(
                            'ship_total'     => (int)$squadron->getInfo($shipId),
                            'ship_frozen'    => 0,
                            'ship_stolen'    => 0,
                            'ship_destroyed' => 0
                        );
                    } else {
                        // This ship already exists in this squadron, add to total
                        $this->{$tempSide}[$shipId]['ship_total'] += (int)$squadron->getInfo($shipId);
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

        // Work out stats for each squadron
        $this->produceCountryStats();
        
        // Produce a battle report
        $this->produceBattleReport();
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
        $asteroidMaximumSteal = floor(($this->_defendingCountry->getInfo('country_asteroid_count') / 100) * BATTLE_ASTEROID_MAX_CAP);

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
        $asteroidsStolen = floor($totalAttackingAttack / BATTLE_ASTEROID_LIFE);

        // Is this more than the attacker is allowed to steal?
        if ($asteroidsStolen > $asteroidMaximumSteal) {
            $asteroidsStolen = $asteroidMaximumSteal;
        }

        // Set the asteroids stolen
        $this->_asteroidsStolen = $asteroidsStolen;
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
        $totalPrimarySalvage   = ($totalPrimarySalvage   / 100) * BATTLE_SALVAGE_PRIMARY;
        $totalSecondarySalvage = ($totalSecondarySalvage / 100) * BATTLE_SALVAGE_SECONDARY;
        
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
     * We know the overview of each ship, but not in terms of each country.
     *
     * We need to work out each countries stats on how many ships they had, were destroyed,
     * frozen and stolen so we can give them a report of their own ships.
     *
     * @access private
     */
    private function produceCountryStats() {
        // We first need to loop over each ship
        foreach ($this->_ship as $shipId => $shipInformation) {
            // Loop over the defenders and then the attackers
            foreach ($this->_squadron as $status => $squadrons) {
                // Did the $status actually have any of this ship, though?
                if ($this->getCountryShipNumber($status, $shipId, array()) <= 0) {
                    continue;
                }

                // Set which we are dealing with
                $attackerOrDefender = '_' . $status . 'Ships';

                // There might be a rounding error, remember which squadrons have been affected
                $shipTally      = array('ship_destroyed' => 0, 'ship_frozen' => 0, 'ship_stolen' => 0);
                $squadronsAffected = array();

                // Totals for this status ship
                $shipDestroyedTally = 0;
                $shipFrozenTally    = 0;
                $shipStolenTally    = 0;

                // And work out the stats for each squadron
                // Remember, each country might have multiple squadrons
                foreach ($squadrons as $index => $squadron) {
                    // Did the squadron actually contain this ship?
                    if ($squadron->getInfo($shipId) <= 0) {
                        continue;
                    }

                    // This squadron might need to be corrected for the rounding error
                    $squadronsAffected[] = $index;

                    // What percentage of this ship did this squadron have?
                    $squadronShipPercentage = ($squadron->getInfo($shipId) / $this->{$attackerOrDefender}[$shipId]['ship_total']) * 100;

                    // We can now work out how many of this squadrons ship have been affected
                    foreach (array('ship_destroyed', 'ship_frozen', 'ship_stolen') as $shipState) {
                        // Destroyed
                        $this->_squadronStats[$squadron->getInfo('country_id')]
                                          [$squadron->getInfo('squadron_id')]
                                          [$shipId]
                                          [$shipState] =
                            $this->{$attackerOrDefender}[$shipId][$shipState] > 0
                                ? ($this->{$attackerOrDefender}[$shipId][$shipState] / 100) * $squadronShipPercentage
                                : 0;

                        // Tally
                        $shipTally[$shipState] += $this->_squadronStats[$squadron->getInfo('country_id')]
                                                                    [$squadron->getInfo('squadron_id')]
                                                                    [$shipId]
                                                                    [$shipState];
                    }
                }

                // If we have accounted for less than we need, assign the remaining to a random squadron
                // "God doesn't play dice with the world", but in this case he (almost) does.
                // @todo This code has not been tested
                foreach (array('ship_destroyed', 'ship_frozen', 'ship_stolen') as $shipState) {
                    if ($shipDestroyedTally < $this->{$attackerOrDefender}[$shipId]['ship_destroyed']) {
                        // Pick a random squadron to assign this difference
                        $randomSquadron = array_rand($squadronsAffected);
                        $randomSquadron = $squadronsAffected[$randomSquadron];

                        // Add the difference to this squadron
                        $this->_squadronStats
                            [$this->_squadron[$status][$randomSquadron]->getInfo('country_id')]
                            [$this->_squadron[$status][$randomSquadron]->getInfo('squadron_id')]
                            [$shipId]
                            [$shipState]
                                += $this->{$attackerOrDefender}[$shipId][$shipState] - $shipDestroyedTally;
                    }
                }
            }
        }
    }

    /**
     * Produces output of what has just happened.
     *
     * Echo's out a table of stats - this would normally be inserted into a
     * database so the entities can view it.
     *
     * @access private
     */
    private function produceBattleReport() {
        // Set variables
        // The battle string
        $battleString          = '';
        $battleStringDefending = '';
        $battleStringAttacking = '';
        // Defending and attacking totals
        $defending     = array('ship_total' => 0, 'ship_destroyed' => 0, 'ship_frozen' => 0, 'ship_stolen' => 0);
        $attacking     = array('ship_total' => 0, 'ship_destroyed' => 0, 'ship_frozen' => 0, 'ship_stolen' => 0);
        $countrySquadrons = array();

        // Loop over each ship
        foreach ($this->_ship as $shipId => $shipInformation) {
            // Set the battle strings
            // Defender
            $battleStringDefending .=
                $shipId . ':' // The ship ID
                 . $this->_defendingShips[$shipId]['ship_total']     . '|'  // Total amount of this ship
                 . $this->_defendingShips[$shipId]['ship_destroyed'] . '|'  // Total destroyed
                 . $this->_defendingShips[$shipId]['ship_frozen']    . '|'  // Total frozen
                 . $this->_defendingShips[$shipId]['ship_stolen']    . ','; // Total stolen
            // Attacker
            $battleStringAttacking .=
                $shipId . ':' // The ship ID
                 . $this->_attackingShips[$shipId]['ship_total']     . '|'  // Total amount of this ship
                 . $this->_attackingShips[$shipId]['ship_destroyed'] . '|'  // Total destroyed
                 . $this->_attackingShips[$shipId]['ship_frozen']    . '|'  // Total frozen
                 . $this->_attackingShips[$shipId]['ship_stolen']    . ','; // Total stolen

            // Individual squadrons
            // Loop over the squadrons
            foreach ($this->_squadron as $status => $squadrons) {
                foreach ($squadrons as $index => $squadron) {
                    // Does this squadron actually contain any of this ship?
                    // Shorthand
                    $stats = $this->_squadronStats[$squadron->getInfo('country_id')][$squadron->getInfo('squadron_id')];

                    // And set the string
                    // Do we need to create the reference?
                    if (! isset($countrySquadrons[$squadron->getInfo('country_id')][$squadron->getInfo('squadron_id')])) {
                        // Yes, haven't created it yet
                        $countrySquadrons[$squadron->getInfo('country_id')][$squadron->getInfo('squadron_id')] = '';
                    }

                    // Add to the country squadron stats
                    $countrySquadrons[$squadron->getInfo('country_id')][$squadron->getInfo('squadron_id')] .=
                        $shipId                              . ':'  // The ship ID
                         . (int)$squadron->getInfo($shipId)     . '|'  // Total amount of this ship
                         . $stats[$shipId]['ship_destroyed'] . '|'  // Total destroyed
                         . $stats[$shipId]['ship_frozen']    . '|'  // Total frozen
                         . $stats[$shipId]['ship_stolen']    . ','; // Total stolen
                }
            }

            // And tally up the totals
            // Defending
            $defending = array(
                'ship_total'     => $defending['ship_total']     + $this->_defendingShips[$shipId]['ship_total'],
                'ship_destroyed' => $defending['ship_destroyed'] + $this->_defendingShips[$shipId]['ship_destroyed'],
                'ship_frozen'    => $defending['ship_frozen']    + $this->_defendingShips[$shipId]['ship_frozen'],
                'ship_stolen'    => $defending['ship_stolen']    + $this->_defendingShips[$shipId]['ship_stolen']
            );
            // Attacking
            $attacking = array(
                'ship_total'     => $attacking['ship_total']     + $this->_attackingShips[$shipId]['ship_total'],
                'ship_destroyed' => $attacking['ship_destroyed'] + $this->_attackingShips[$shipId]['ship_destroyed'],
                'ship_frozen'    => $attacking['ship_frozen']    + $this->_attackingShips[$shipId]['ship_frozen'],
                'ship_stolen'    => $attacking['ship_stolen']    + $this->_attackingShips[$shipId]['ship_stolen']
            );
        }

        // We now have grand totals and ship totals
        $battleString = 
            // Defender totals
            $defending['ship_total']                . '|'
                . $defending['ship_destroyed']      . '|'
                . $defending['ship_frozen']         . '|'
                . $defending['ship_stolen']         . '|'
                . $this->_salvagePrimaryReclaimed   . '|'
                . $this->_salvageSecondaryReclaimed . "\n" .
            // Attacker totals
            $attacking['ship_total']           . '|'
                . $attacking['ship_destroyed'] . '|'
                . $attacking['ship_frozen']    . '|'
                . $attacking['ship_stolen']    . '|'
                . $this->_defendingCountry->getInfo('country_asteroid_count') . '|'
                . $this->_asteroidsStolen      . "\n" .
            // Defender individual ship totals
            rtrim($battleStringDefending, ',') . "\n" .
            // Attacker individual ship totals
            rtrim($battleStringAttacking, ',');

        // Loop over each country squadron and set to string
        foreach ($countrySquadrons as $countryId => $squadrons) {
            foreach ($squadrons as $squadronId => $countrySquadronStats) {
                $battleString .= "\n" . $countryId . ',' . $squadronId . ',' . rtrim($countrySquadronStats, ',');
            }
        }

        // Save into the battle log
        // Get the database connection
        $database  = Core_Database::getInstance();
        $statement = $database->prepare("
            INSERT INTO `battle`
                (
                    `country_id`,
                    `battle_string`
                )
            VALUES
                (
                    :country_id,
                    :battle_string
                )
        ");

        // Execute the query
        $battleId = $statement->execute(array(
            ':country_id'    => $this->_defendingCountry->getInfo('country_id'),
            ':battle_string' => $battleString
        ));

        // Set the battle ID
        $this->_battleId = $database->lastInsertId();
    }

    /**
     * Return the ID for this battle.
     *
     * @access public
     * @return int
     */
    public function getBattleId() {
        return $this->_battleId;
    }
}