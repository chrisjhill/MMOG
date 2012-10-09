<?php
/**
 * Create a list of threads.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       06/10/2012
 */
class Model_Conference_Thread_List implements IteratorAggregate
{
	/**
	 * The threads that are in this conference.
	 *
	 * @access private
	 * @var array
	 */
	private $_thread = array();

	/**
	 * Get a list of threads that are in the conference.
	 *
	 * @access public
	 * @param $planetId int
	 */
	public function __construct($planetId) {
		$this->setInfo($planetId);
	}

	/**
	 * Set the threads.
	 *
	 * @access protected
	 * @param $planetId int
	 */
	protected function setInfo($planetId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT   t.thread_id, t.round_id, t.planet_id,
			         t.thread_subject,
			         t.thread_created, t.thread_updated, t.thread_removed
			FROM     `conference_thread` t
			WHERE    t.round_id       = :round_id
			         AND
			         t.planet_id      = :planet_id
			         AND
			         t.thread_removed IS NULL
			ORDER BY t.thread_updated DESC
		");

		// Execute the query
		$statement->execute(array(
			':round_id'  => GAME_ROUND,
			':planet_id' => $planetId
		));

		// Loop over the transmissions
		while ($thread = $statement->fetch()) {
			$this->_thread[] = new Model_Conference_Thread_Instance($thread['thread_id']);
		}
	}

	/**
	 * Allow scripts to iterate over the transmissions.
	 * 
	 * @access public
	 * @return Model_Transmission_Instance
	 */
	public function getIterator() {
		return new ArrayIterator($this->_thread);
	}
}