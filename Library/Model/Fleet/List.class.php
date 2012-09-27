<?php
/**
 * A storage area for an entities fleet.
 *
 * A fleet is made up of multiple squadrons.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       13/09/2012
 */
class Model_Fleet_List implements IteratorAggregate
{
	/**
	 * An array of the squadrons this country controls.
	 *
	 * @access private
	 * @var array of Model_Fleet_Squadron
	 */
	private $_fleet = array();

	/**
	 * Add a Model_Fleet_Squadron to this list.
	 *
	 * @access public
	 * @param Model_Fleet_Squadron $squadron
	 */
	public function add($squadron) {
		if (get_class($squadron) == 'Model_Fleet_Squadron') {
			$this->_fleet[$squadron->getInfo('squadron_id')] = $squadron;
		}
	}

	/**
	 * Does the squadron exist in this list?
	 *
	 * @access public
	 * @param int $squadronId
	 * @return boolean 
	 */
	public function exists($squadronId) {
		return isset($this->_fleet[$squadronId]);
	}

	/**
	 * Return a squadron.
	 *
	 * @access public
	 * @param int $squadronId
	 * @return Model_Fleet_Squadron
	 */
	public function get($squadronId) {
		return $this->exists($squadronId)
			? $this->_fleet[$squadronId]
			: false;
	}

	/**
	 * Allow scripts to iterate over the squadrons.
	 * 
	 * @access public
	 * @return Model_Fleet_Squadron
	 */
	public function getIterator() {
		return new ArrayIterator($this->_fleet);
	}
}