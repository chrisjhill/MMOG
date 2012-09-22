<?php
/**
 * Creates a new planet for countries to inhibit.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       22/09/2012
 */
class Model_Planet_Create
{
	/**
	 * Create a new planet with random coords.
	 *
	 * @access public
	 * @return Model_Planet_Instance
	 */
	public function create() {
		// Generate some random coords for the planet to live
		// ...

		// Return a new Model_Planet_Instance obejct
		return new Model_Planet_Instance($x, $y);
	}
}