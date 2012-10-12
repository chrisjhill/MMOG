<?php
/**
 * Handles a single conference thread.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       09/10/2012
 */
class Model_Conference_Thread_Instance extends Core_Instance
{
	/**
	 * Set up the thread information.
	 *
	 * @access public
	 * @param  int    $threadId
	 */
	public function __construct($threadId) {
		// Set thread information
		$this->setInfo($threadId);
	}

	/**
	 * Set the information on the planet.
	 *
	 * @access protected
	 * @param  int       $threadId
	 * @throws Exception If the thread is not found.
	 * @return boolean
	 */
	protected function setInfo($threadId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT t.thread_id, t.round_id, t.planet_id,
			       t.thread_subject,
			       t.thread_created, t.thread_updated, t.thread_removed,
			       (
			       	    SELECT COUNT(*)
			       	    FROM   `conference_post` p
			       	    WHERE  p.thread_id    = t.thread_id
			       	           AND
			       	           p.post_removed IS NULL
			       ) as 'thread_post_count',
			       (
			       	    SELECT   p.post_id
			       	    FROM     `conference_post` p
			       	    WHERE    p.thread_id = t.thread_id
			       	    ORDER BY p.post_updated DESC
			       	    LIMIT    1
			       ) as 'post_id'
			FROM   `conference_thread` t
			WHERE  t.round_id       = :round_id
			       AND
			       t.thread_id      = :thread_id
			       AND
			       t.thread_removed IS NULL
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':round_id'  => GAME_ROUND,
			':thread_id' => $threadId
		));

		// Did we find the country?
		if ($statement->rowCount() >= 1) {
			// Yes, set the information
			$this->_info = $statement->fetch();
			$this->_info['post'] = new Model_Conference_Post_Instance($this->getInfo('post_id'));
			return true;
		}

		// Something went wrong
		throw new Exception('conference-thread-not-found');
	}
}