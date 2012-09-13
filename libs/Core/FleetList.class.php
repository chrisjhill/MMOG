<?php
/**
 * A storage area for an entities fleet.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       13/09/2012
 * @homepage    http://www.chrisjhill.co.uk
 * @twitter     @chrisjhill
 */
class FleetList
{
	/**
	 * An array of the fleets this entity controls.
	 *
	 * @access private
	 * @var array
	 */
	private $_fleet = array();

	/**
	 * Add a Fleet to this list.
	 *
	 * @access public
	 * @param Fleet $fleet
	 */
	public function add($fleet) {
		if (get_class($fleet) == 'Fleet') {
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
	 * @return Fleet
	 */
	public function get($fleetId) {
		return $this->exists($fleetId)
			? $this->_fleet[$fleetId]
			: false;
	}
}