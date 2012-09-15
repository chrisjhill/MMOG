<?php
/**
 * A storage area for an entities fleet.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       13/09/2012
 */
class Core_FleetList implements IteratorAggregate
{
	/**
	 * An array of the fleets this country controls.
	 *
	 * @access private
	 * @var array of Core_Fleet
	 */
	private $_fleet = array();

	/**
	 * Add a Fleet to this list.
	 *
	 * @access public
	 * @param Core_Fleet $fleet
	 */
	public function add($fleet) {
		if (get_class($fleet) == 'Core_Fleet') {
			$this->_fleet[$fleet->getInfo('fleet_id')] = $fleet;
		}
	}

	/**
	 * Does the fleet exist in this list?
	 *
	 * @access public
	 * @param int $fleetId
	 * @return boolean 
	 */
	public function exists($fleetId) {
		return isset($this->_fleet[$fleetId]);
	}

	/**
	 * Return a fleet.
	 *
	 * @access public
	 * @param int $fleetId
	 * @return Core_Fleet
	 */
	public function get($fleetId) {
		return $this->exists($fleetId)
			? $this->_fleet[$fleetId]
			: false;
	}

	/**
	 * Allow scripts to iterate over the fleet list.
	 * 
	 * @access public
	 * @return Core_Fleet
	 */
	public function getIterator() {
		return new ArrayIterator($this->_fleet);
	}
}