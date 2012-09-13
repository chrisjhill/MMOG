<?php
/**
 * Battle script.
 *
 * Developed as part of a <abbr title="Massively Multiplayer Online Game">MMOG</abbr>,
 * this engine is capable of taking an attacking and defending fleet and battling them
 * against each other. The engine is capable of freezing, stealing and destroying the
 * opponents ships.
 * 
 * @todo Build Fleet class so we can remove some of the logic.
 * @todo Work out losses/gains/etc. for each fleet (currently only for attacking/defending).
 * @todo Run off a database instead of being hard coded.
 * @todo produceWaveReport() needs major rework. Needs to be stored "nicely" in db.
 * @todo Resources probably needs to be rewritten so that you can have more than 2 types.
 * @todo Ship stats needs to be moved into its own class.
 * @todo Stats such as asteroid life, max asteroid cap, salvage percentages need to be moved.
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
     * @var array
     * @access private
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
     * @todo This variable needs a better name, since it is no longer just classes.
     * @var array
     * @access private
     */
    private $_shipClass = array();

    /**
     * <code>
     * array(
     *     'attacking' => array(
     *         0 => array(
     *             'entity_id' => 12345,
     *             'fleet_id'  => 1,
     *             'fleet'     => array(0 => 12345, 1 => 67890, 2 => 13579, 3 => 24680)
     *         ),
     *         1 => array(
     *             'entity_id' => 67890,
     *             'fleet_id'  => 3,
     *             'fleet'     => array(0 => 12345, 3 => 24680)
     *         )
     *     ),
     *     'defending' => array(
     *         0 => array(
     *             'entity_id' => 54321,
     *             'fleet_id'  => 1,
     *             'fleet'     => array(3 => 24680)
     *         )
     *     )
     * )
     * </code>
     */
    private $_fleet = array();
    
    /**
     * Information on the defending entity such as how many
     * asteroids and what their population is.
     * 
     * <code>
     * array(
     *     'asteroid_count'    => 12345,
     *     'salvage_primary'   => 67890,
     *     'salvage_secondary' => 13570
     * )
     * </code>
     * 
     * @var array
     * @access private
     */
    private $_defendingEntity = array();
    
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
     * @var array
     * @access private
     */
    private $_defendingShips = array();
    
    /**
     * Information on the attacking entity such as how many
     * asteroids they have stolen.
     * 
     * <code>
     * array(
     *     'asteroid_count'   => 12345
     * )
     * </code>
     * 
     * @var array
     * @access private
     */
    private $_attackingEntity = array();

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
     * @var array
     * @access private
     */
    private $_attackingShips = array();
    
    /**
     * Much much life each asteroid has before it is claimed by the
     * attacking entity.
     *
     * @var int
     * @access private
     */
    private $_asteroidLife = 50;
    
    /**
     * The maximum percentage of the defending entities asteroid
     * count that the attacking entity can collect. You do not want
     * this percentage to be too high as then the defending entity
     * will not be able to support itself and will probably quit the
     * game. A recommended percentage is around 10% per wave.
     * 
     * @var int
     * @access private
     */
    private $_maxAsteroidCap = 10;

    /**
     * How much salvage of the primary resource the defending entity
     * has collected from the wave of battle on a percentage scale. A
     * recommended percntage is around 15%.
     *
     * Salvage is from destroyed ships that can no longer be used. The
     * defending entity collects the usable salvage which is added to their
     * store for use. Salvage is good because it means that after the entity
     * has been attacked they can rebuild quicker.
     *
     * Note: An entity can only collect salvage if they have salvage collection
     * ships in the aresnal.
     *
     * @var int
     * @access private
     */
    private $_salvagePrimary = 15;
    
    /**
     * How much salvage of the secondary resource the defending entity
     * has collected from the wave of battle on a percentage scale. A
     * recommended percntage is around 15%.
     *
     * Salvage is from destroyed ships that can no longer be used. The
     * defending entity collects the usable salvage which is added to their
     * store for use. Salvage is good because it means that after the entity
     * has been attacked they can rebuild quicker.
     *
     * Note: An entity can only collect salvage if they have salvage collection
     * ships in the aresnal.
     *
     * @var int
     * @access private
     */
    private $_salvageSecondary = 15;
    
    /**
     * Everything that happens in this engine is debugged so we can inspect it
     * at a later date to ensure that there are no bugs.
     *
     * @var array
     * @access private 
     */
    private $_debug = array();

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
        $this->_ship = array(
            4 => array('name' => 'EMP freezing ship',      'type' => 'EMP',     'class' => 'Fighter', 'target' => 'Cruiser',  'life' => 9,   'attack' => 14,  'primaryCost' => 200,  'secondaryCost' => 75),
            0 => array('name' => 'Attacking ship 1',       'type' => 'Basic',   'class' => 'Fighter', 'target' => 'Fighter',  'life' => 10,  'attack' => 15,  'primaryCost' => 100,  'secondaryCost' => 25),
            1 => array('name' => 'Attacking ship 2',       'type' => 'Basic',   'class' => 'Frigate', 'target' => 'Frigate',  'life' => 30,  'attack' => 10,  'primaryCost' => 500,  'secondaryCost' => 100),
            2 => array('name' => 'Attacking ship 3',       'type' => 'Basic',   'class' => 'Frigate', 'target' => 'Cruiser',  'life' => 10,  'attack' => 20,  'primaryCost' => 700,  'secondaryCost' => 100),
            3 => array('name' => 'Attacking ship 4',       'type' => 'Basic',   'class' => 'Fighter', 'target' => 'Fighter',  'life' => 130, 'attack' => 154, 'primaryCost' => 200,  'secondaryCost' => 75),
            5 => array('name' => 'Stealing ship',          'type' => 'Steal',   'class' => 'Cruiser', 'target' => 'Frigate',  'life' => 100, 'attack' => 400, 'primaryCost' => 2000, 'secondaryCost' => 750),
            6 => array('name' => 'Salvage ship',           'type' => 'Salvage', 'class' => 'Cruiser', 'target' => 'Salvage',  'life' => 150, 'attack' => 500, 'primaryCost' => 2000, 'secondaryCost' => 750),
            7 => array('name' => 'Asteroid stealing ship', 'type' => 'Pod',     'class' => 'Fighter', 'target' => 'Asteroid', 'life' => 15,  'attack' => 75,  'primaryCost' => 1000, 'secondaryCost' => 200)
        );
        
        // Set the ship classes
        foreach ($this->_ship as $shipId => $shipInformation) {
            // Set which ships are in which class
            $this->_shipClass['class'][$shipInformation['class']][] = $shipId;
            
            // Set attacking and defending class life total
            $this->_shipClass['defending'][$shipInformation['class']] = 0;
            $this->_shipClass['attacking'][$shipInformation['class']] = 0;
        }
    }

    /**
     * Sets all of the defending entity information.
     *
     * This function also works out the defending and attacking fleets
     * and fires starts the battle sequence.
     *
     * @access public
     */
    public function initiateWave() {
        // Set entity information
        $this->setDefendingEntityInformation();
        
        // Set ship numbers and ship class information
        $this->setFleets();
        //$this->resetShipClassLifeTotals();
        $this->setShipClassLifeTotals();
        
        // Start battle
        $this->doBattle();
        
        // Debug
        $this->_debug[] = 'Battle has finished';
    }

    /**
     * Set the information about the defending entity such as asteroid count.
     * 
     * @access private
     */
    private function setDefendingEntityInformation() {
        // Set information about the defending entity
        // These would normally be stored in a database, but for this example they are hard-coded 
        $this->_defendingEntity = array(
            'asteroid_count'    => 500,
            'salvage_primary'   => 0,
            'salvage_secondary' => 0
        );
    }

    /**
     * Set the initial fleet stats for the attacking and defending countries.
     *
     * @todo Needs to grab data from a database.
     * @access private
     */
    private function setFleets() {
        // Set some ship stats
        // This is how it would be pulled from the database
        $fleets = array(
            array(
                'entity_id' => 123,
                'fleet_id'  => 1,
                'side'      => 'defending',
                'fleet'     => array(0 => 350, 1 => 2000, 3 => 600, 5 => 1000, 6 => 300)
            ),
            array(
                'entity_id' => 123,
                'fleet_id'  => 1,
                'side'      => 'defending',
                'fleet'     => array(0 => 350, 2 => 1500, 3 => 200, 4 => 5000, 6 => 200)
            ),
            array(
                'entity_id' => 321,
                'fleet_id'  => 2,
                'side'      => 'attacking',
                'fleet'     => array(0 => 500, 1 => 3000, 2 => 1750, 3 => 400, 4 => 4000, 5 => 4000, 6 => 0, 7 => 2000)
            )
        );

        // Loop over each of the fleets
        foreach ($fleets as $index => $fleet) {
            // Set the fleet
            $this->_fleet[$fleet['side']][] = $fleet;

            // And loop over and add each ship so to the totals
            foreach ($this->_ship as $shipId => $ship) {
                // Create side to add this fleet to
                $tempSide = '_' . $fleet['side'] . 'Ships';

                // Add fleet to attacking or defending totals
                if (! isset($this->{$tempSide}[$shipId])) {
                    // This ship does not currently exist, add it
                    $this->{$tempSide}[$shipId] = array(
                        'ship_total'     => isset($fleet['fleet'][$shipId])
                                                ? $fleet['fleet'][$shipId]
                                                : 0,
                        'ship_frozen'    => 0,
                        'ship_stolen'    => 0,
                        'ship_destroyed' => 0
                    );
                } else {
                    // This ship already exists in this fleet, add to total
                    $this->{$tempSide}[$shipId]['ship_total'] += isset($fleet['fleet'][$shipId])
                        ? $fleet['fleet'][$shipId]
                        : 0;
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
            $this->_shipClass['defending'][$shipInformation['class']] += ($this->getEntityShipNumber('defending', $shipId, array('stolen', 'destroyed')) * $this->_ship[$shipId]['life']);
            $this->_shipClass['attacking'][$shipInformation['class']] += ($this->getEntityShipNumber('attacking', $shipId, array('stolen', 'destroyed')) * $this->_ship[$shipId]['life']);
        }
    }

    /**
     * Start the battle process.
     * 
     * This is a complex process. We need to loop over each ship in their
     * order of fire. All ships have a different order of fire - you cannot
     * have two or more ships firing at the same time.
     * 
     * We first start by making sure that the defending or attacking entity
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
        // Debug
        $this->_debug[] = 'Battle initiated';
        
        // Loop over each ship
        foreach ($this->_ship as $shipId => $shipInformation) {
            // Debug
            $this->_debug[] = '<strong>Turn of ' . $shipInformation['name'] . ' (#' . $shipId . ')</strong>';
            
            // We do not want to do the Asteroid class just yet
            if ($shipInformation['target'] == 'Asteroid' || $shipInformation['target'] == 'Salvage') {
                $this->_debug[] = 'This ship targets Asteroids or Salvage, skipping it for now';
                continue;
            }
            
            // Does either side have any of this ship?
            if ($this->getEntityShipNumber('defending', $shipId, array('frozen', 'stolen', 'destroyed')) <= 0 && $this->getEntityShipNumber('attacking', $shipId, array('frozen', 'stolen', 'destroyed')) <= 0) {
                $this->_debug[] = 'Neither side has any ' . $shipInformation['name'] . ' ships';
                continue;
            }
            
            // Does either side have any ships that this ship targets?
            if ($this->_shipClass['defending'][$shipInformation['target']] <= 0 && $this->_shipClass['attacking'][$shipInformation['target']] <= 0) {
                $this->_debug[] = 'Neither side targets any ' . $shipInformation['target'] . ' class';
                continue;
            }
            
            // Start gathering the stats
            $totalDefendingAttack = $this->getEntityShipNumber('defending', $shipId, array('frozen', 'stolen', 'destroyed')) * $shipInformation['attack'];
            $totalAttackingAttack = $this->getEntityShipNumber('attacking', $shipId, array('frozen', 'stolen', 'destroyed')) * $shipInformation['attack'];
            
            // So we do not have to repeat ourselves 3 times below, set some variables that contain the different logic
            if ($this->_ship[$shipId]['type'] == 'EMP') {
                // This ship freezes other ships so they cannot attack this wave
                $shipToDeductStats = array('frozen', 'stolen', 'destroyed');
                $shipToAddToStats  = 'frozen';
            } else if ($this->_ship[$shipId]['type'] == 'Steal') {
                // This ship steals other ships to fight against the opponant on the next wave
                $shipToDeductStats = array('stolen', 'destroyed');
                $shipToAddToStats  = 'stolen';
            } else {
                // This ship destroys other ships
                $shipToDeductStats = array('stolen', 'destroyed');
                $shipToAddToStats  = 'destroyed';
            }
            
            // Debug
            $this->_debug[] = 'Defenders will deal ' . number_format($totalDefendingAttack) . ' damage through ' . number_format($this->getEntityShipNumber('defending', $shipId, array('frozen', 'stolen', 'destroyed'))) . ' ' . $shipInformation['name'] . 's';
            $this->_debug[] = 'Attackers will deal ' . number_format($totalAttackingAttack) . ' damage through ' . number_format($this->getEntityShipNumber('attacking', $shipId, array('frozen', 'stolen', 'destroyed'))) . ' ' . $shipInformation['name'] . 's';
            
            // Loop over each ship in the target class
            foreach ($this->_shipClass['class'][$shipInformation['target']] as $void => $shipIdTargeted) {
                // Debug
                $this->_debug[] = 'We are now dealing damage to the ' . $this->_ship[$shipIdTargeted]['name'] . ' ship';
        
                // What percentage is this ship in relation to the rest of the class?
                // We want to get it at the start of the wave to apply the percentage evenly
                $defendingLifePercentage = 0;
                $defendingAttackTotal    = 0;
                $attackingLifePercentage = 0;
                $attackingAttackTotal    = 0;
        
                // Does the entity actually have any ships in the class we are targeting?
                // After this we will know the percentage of attack to apply to this ship
                if ($this->_shipClass['defending'][$shipInformation['target']] > 0) {
                    // Defenders have some ships in this target class
                    $attackingLifePercentage = (($this->getEntityShipNumber('defending', $shipIdTargeted, array('frozen', 'stolen', 'destroyed')) * $this->_ship[$shipIdTargeted]['life']) / $this->_shipClass['defending'][$shipInformation['target']]) * 100;
                }
                if ($this->_shipClass['attacking'][$shipInformation['target']] > 0) {
                    // Attackers have some ships in this target class
                    $defendingLifePercentage = (($this->getEntityShipNumber('attacking', $shipIdTargeted, array('frozen', 'stolen', 'destroyed')) * $this->_ship[$shipIdTargeted]['life']) / $this->_shipClass['attacking'][$shipInformation['target']]) * 100;
                }
        
                // Work out the attack total based on that percentage
                if ($defendingLifePercentage > 0) { $defendingAttackTotal = round(($totalDefendingAttack / 100) * $defendingLifePercentage); }
                if ($attackingLifePercentage > 0) { $attackingAttackTotal = round(($totalAttackingAttack / 100) * $attackingLifePercentage); }
        
                // Debug
                $this->_debug[] = 'Defenders are shooting ' . number_format($defendingAttackTotal) . ' (' . $defendingLifePercentage . '%) of their firepower at this ship which has ' . $this->getEntityShipNumber('attacking', $shipIdTargeted, array('stolen', 'destroyed')) . ' @ ' . $this->_ship[$shipIdTargeted]['life'] . ' life';
                $this->_debug[] = 'Attackers are shooting ' . number_format($attackingAttackTotal) . ' (' . $attackingLifePercentage . '%) of their firepower at this ship which has ' . $this->getEntityShipNumber('defending', $shipIdTargeted, array('stolen', 'destroyed')) . ' @ ' . $this->_ship[$shipIdTargeted]['life'] . ' life';
        
                // How many ships will that destroy?
                // This does not take into account that the opponant may not have this many ships...
                // only that we could destroy that amount with the attack we have.
                $defendingDestroyed = floor($defendingAttackTotal / $this->_ship[$shipIdTargeted]['life']);
                $attackingDestroyed = floor($attackingAttackTotal / $this->_ship[$shipIdTargeted]['life']);
        
                // The ship we are attacking with is a ship that can destroy the opponants ships
                // We can only destroy those ships that have not already been destroyed or stolen
                // We can still destroy ships that have been frozen
                // Work out how many that is
                if ($defendingDestroyed > $this->getEntityShipNumber('attacking', $shipIdTargeted, $shipToDeductStats)) {
                    // Defenders destroyed more ships than the attackers have remaining
                    // Bring that down to the maximum possible
                    $defendingDestroyed = $this->getEntityShipNumber('attacking', $shipIdTargeted, $shipToDeductStats);
                }
                if ($attackingDestroyed > $this->getEntityShipNumber('defending', $shipIdTargeted, $shipToDeductStats)) {
                    // Attackers destroyed more ships than the defenders have remaining
                    // Bring that down to the maximum possible
                    $attackingDestroyed = $this->getEntityShipNumber('defending', $shipIdTargeted, $shipToDeductStats);
                }
        
                // Add the ships that we just destroyed to their destroyed total in this wave
                $this->_attackingShips[$shipIdTargeted]['ship_' . $shipToAddToStats] += $defendingDestroyed;
                $this->_defendingShips[$shipIdTargeted]['ship_' . $shipToAddToStats] += $attackingDestroyed;
        
                // Debug
                $this->_debug[] = 'Defenders have ' . $shipToAddToStats . ' ' . number_format($defendingDestroyed) . ' ships';
                $this->_debug[] = 'Attackers have ' . $shipToAddToStats . ' ' . number_format($attackingDestroyed) . ' ships';
            }
        }
        
        // Asteroid stealing, and salvage reclaiming
        $this->doAsteroidStealing();
        $this->doSalvageReclaiming();
        
        // Debug
        $this->_debug[] = '<strong>Wave has finished</strong>';
        
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
    private function getEntityShipNumber($attackerOrDefender, $shipId, $shipsToDeduct) {
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
     * Steals asteroids from the defending entity.
     *
     * We can only steal a maximum percentage of asteroids per wave.
     *
     * @access private
     */
    private function doAsteroidStealing() {
        // Debug
        $this->_debug[] = '<strong>Start of asteroid stealing logic</strong>';

        // What is the potential maximum asteroids we can steal?
        $asteroidMaximumSteal = floor(($this->_defendingEntity['asteroid_count'] / 100) * $this->_maxAsteroidCap);

        // Debug
        $this->_debug[] = 'Maximum asteroids that can be stolen: ' . $asteroidMaximumSteal;

        // How many asteroids can the attacking entity actually steal?
        // Set the total attack variable
        $totalAttackingAttack = 0;

        // Need to loop over each ship to see if it can steal asteroids
        foreach ($this->_ship as $shipId => $shipInformation) {
            // Is this an asteroid stealing ship?
            if ($shipInformation['type'] == 'Pod') {
                // Add how many asteroids this ship can steal
                $totalAttackingAttack += $this->getEntityShipNumber('attacking', $shipId, array('frozen', 'stolen', 'destroyed')) * $shipInformation['attack'];
            }
        }

        // How many asteroids can the attacking entity steal?
        $asteroidsStolen = floor($totalAttackingAttack / $this->_asteroidLife);

        // Is this more than the attacker is allowed to steal?
        if ($asteroidsStolen > $asteroidMaximumSteal) {
            $asteroidsStolen = $asteroidMaximumSteal;
        }

        // Set the asteroids stolen
        $this->_attackingEntity['asteroid_count'] = $asteroidsStolen;

        // Debug
        $this->_debug[] = 'Attackers stole ' . $this->_attackingEntity['asteroid_count'] . ' asteroids through ' . number_format($totalAttackingAttack) . ' firepower';
    }

    /**
     * Reclaims salvage from destroyed ships.
     *
     * We can only reclaim a maximum amount of salvage per wave.
     *
     * @access private
     */
    private function doSalvageReclaiming() {
        // Debug
        $this->_debug[] = '<strong>Start of salvage stealing logic</strong>';

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
                $totalPrimarySalvage   += $totalDestroyedShips * $shipInformation['primaryCost'];
                $totalSecondarySalvage += $totalDestroyedShips * $shipInformation['secondaryCost'];

                // Debug
                $this->_debug[] = 'Added ' . number_format($totalDestroyedShips * $shipInformation['primaryCost']) .
                    ' to primary total and ' . number_format($totalDestroyedShips * $shipInformation['secondaryCost']) .
                    ' to secondary salvage total after ' . number_format($totalDestroyedShips) . 
                    ' ' . $shipInformation['name'] . ' were destroyed';
            }
    
            // If this is a salvage ship then add up how much it can hold
            if ($shipInformation['type'] == 'Salvage') {
                $totalSalvageShipsReclaimable += $this->getEntityShipNumber('defending', $shipId, array('frozen', 'stolen', 'destroyed')) * $shipInformation['attack'];
            }
        }
        
        // Was there any salvage?
        if ($totalPrimarySalvage <= 0) {
            // Debug
            $this->_debug[] = 'There is no salvage available';
            return false;
        }
        
        // Get a percentage that is reclaimable
        $totalPrimarySalvage   = ($totalPrimarySalvage   / 100) * $this->_salvagePrimary;
        $totalSecondarySalvage = ($totalSecondarySalvage / 100) * $this->_salvageSecondary;
        
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
        $this->_defendingEntity['salvage_primary']   = $totalSalvageShipsPrimaryReclaimable;
        $this->_defendingEntity['salvage_secondary'] = $totalSalvageShipsSecondaryReclaimable;

        // Debug
        $this->_debug[] = 'Total possible salvage space: ' . number_format($totalSalvageShipsReclaimable);
        $this->_debug[] = 'Salvaged ' . number_format($this->_defendingEntity['salvage_primary']) . ' primary';
        $this->_debug[] = 'Salvaged ' . number_format($this->_defendingEntity['salvage_secondary']) . ' secondary';
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
                    <th>' . $shipInformation['name'] . '</th>
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
                    <th colspan="4">Defenders salvaged ' . number_format($this->_defendingEntity['salvage_primary']) . ' primary and ' . number_format($this->_defendingEntity['salvage_secondary']) . ' secondary.</th>
                    <th colspan="4">Attackers have stolen ' . $this->_attackingEntity['asteroid_count'] . ' asteroids.</td>
                </tr>
            </table>
        </div>

        <hr />

        <h3>Debug information</h3>';

        $this->debug();
    }
    
    /**
     * Output the debug information so we can see exactly what happened.
     *
     * This could be inserted into a database next to the battle report above so
     * we can make sure everything correctly happened.
     *
     * @access public
     */
    public function debug() {
        echo '<ol>';
        foreach ($this->_debug as $debugId => $debugString) {
            echo '<li>' . $debugString . '</li>';
        }
        echo '</ol>';
    }
}