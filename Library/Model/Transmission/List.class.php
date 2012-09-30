<?php
/**
 * Create a list of transmissions.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       30/09/2012
 */
class Model_Transmission_List implements IteratorAggregate
{
	/**
	 * The transmissions the country can view.
	 *
	 * @access private
	 * @var array
	 */
	private $_transmission = array();

	/**
	 * Set up the planet information.
	 *
	 * We can either retrieve transmissions that are going to a country, or from
	 * a country.
	 *
	 * <code>
	 *     'country'            => Model_Country_Instance,
	 *     'toTransmissions'    => true // Set to false to get transmission sent from this country
	 *     'order_by'           => 't.transmission_created',
	 *     'order_by_direction' => 'DESC',
	 *     'limit'              => 10
	 * </code>
	 *
	 * @access public
	 * @param $param array
	 */
	public function __construct($param) {
		// Set some defaults
		$defaults = array(
			'toTransmissions'    => true,
			'order_by'           => 't.transmission_created',
			'order_by_direction' => 'DESC',
			'limit'              => 10
		);

		// And merge these in with the parameters
		$param = array_merge($defaults, $param);

		// And set the information
		$this->setInfo($param);
	}

	/**
	 * Set the transmissions.
	 *
	 * @access protected
	 * @param $param array
	 */
	protected function setInfo($param) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT t.transmission_id
			FROM   `transmission` t
			WHERE  t.round_id             = :round_id
			       AND
			       t.transmission_" . ($param['toTransmissions'] ? 'to' : 'from') . "_country_id  = :country_id
			       AND
			       t.transmission_removed IS NULL
			ORDER BY :order_by :order_by_direction
			LIMIT    :limit
		");

		// Execute the query
		// Since limit is an integer we cannot use the standard execute
		$statement->bindValue(':round_id',           GAME_ROUND);
		$statement->bindValue(':country_id',         $param['country']->getInfo('country_id'));
		$statement->bindValue(':order_by',           $param['order_by']);
		$statement->bindValue(':order_by_direction', $param['order_by_direction']);
		$statement->bindValue(':limit',              $param['limit'], PDO::PARAM_INT);
		$statement->execute();

		// Create a variable to store country instances so we don't keep hitting the database
		$countries = array();

		// Loop over the transmissions
		while ($transmission = $statement->fetch()) {
			// Yes, set the information
			$this->_transmission[$transmission['transmission_id']] = new Model_Transmission_Instance($transmission['transmission_id']);

			// Set the country information
			$countryToId   = $this->_transmission[$transmission['transmission_id']]->getInfo('transmission_to_country_id');
			$countryFromId = $this->_transmission[$transmission['transmission_id']]->getInfo('transmission_from_country_id');

			// Do we have the country it was to?
			if (! isset($countries[$countryToId])) {
				$countries[$countryToId] = new Model_Country_Instance($countryToId);
			}

			// Do we have the country it was from?
			if (! isset($countries[$countryFromId])) {
				$countries[$countryFromId] = new Model_Country_Instance($countryFromId);
			}

			// Set to the information array
			$this->_transmission[$transmission['transmission_id']]->_transmission['transmission_to']   = $countries[$countryToId];
			$this->_transmission[$transmission['transmission_id']]->_transmission['transmission_from'] = $countries[$countryFromId];
		}
	}

	/**
	 * Allow scripts to iterate over the transmissions.
	 * 
	 * @access public
	 * @return Model_Transmission_Instance
	 */
	public function getIterator() {
		return new ArrayIterator($this->_transmission);
	}
}