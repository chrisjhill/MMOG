<?php
/**
 * Contains information on a single squadron.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       13/09/2012
 */
class Model_Fleet_Squadron
{
	/**
	 * Information on this squadron.
	 *
	 * @access private
	 * @var array
	 */
	private $_squadron = array();

	/**
	 * Class constructor.
	 *
	 * @access public
	 * @param array $squadron
	 */
	public function __construct($squadron) {
		// We have the initial squadron information
		// But not information on the contents of the squadron
		$this->_squadron = $squadron;

		// And set the ships this squadron contains
		$this->setShips();
	}

	/**
	 * Set the ships in this squadron
	 *
	 * @access public
	 */
	public function setShips() {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT s.ship_id, s.ship_quantity
			FROM   `squadron` s
			WHERE  s.country_id  = :country_id
			       AND
			       s.squadron_id = :squadron_id
		");

		// Execute the query
		$statement->execute(array(
			':country_id'  => $this->_squadron['country_id'],
			':squadron_id' => $this->_squadron['squadron_id']
		));

		// Loop over the ships in this squadron
		while ($ship = $statement->fetch()) {
			$this->_squadron[$ship['ship_id']] = $ship['ship_quantity'];
		}
	}

	/**
	 * Return how many we have of a certain ship.
	 *
	 * @access public
	 * @param int $shipId
	 * @return int
	 */
	public function getShip($shipId) {
		return isset($this->_squadron[$shipId])
			? $this->_squadron[$shipId]
			: 0;
	}

	/**
	 * Return a piece of information about this squadron.
	 *
	 * Note: This function can return more than just a ship, such as its status.
	 *
	 * @access public
	 * @param string $index
	 * @return string
	 */
	public function getInfo($index) {
		return isset($this->_squadron[$index])
			? $this->_squadron[$index]
			: false;
	}
}