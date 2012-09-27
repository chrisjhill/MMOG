<?php
/**
 * Provides the basic functionality to object instances.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       22/09/2012
 * @abstract
 */
abstract class Core_Instance
{
	/**
	 * Storage for an instance of an object.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_info;

	/**
	 * Set the instance information.
	 *
	 * @access protected
	 * @param $abstractId int
	 * @abstract
	 */
	abstract protected function setInfo($abstractId);

	/**
	 * Return a piece of information on the country.
	 *
	 * @access public
	 * @param string $index
	 * @return string
	 */
	public function getInfo($index) {
		return isset($this->_info[$index])
			? $this->_info[$index]
			: false;
	}
}