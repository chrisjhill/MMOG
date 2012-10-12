<?php
/**
 * Create a list of posts in a thread.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       09/10/2012
 */
class Model_Conference_Post_List implements IteratorAggregate
{
	/**
	 * The posts that are in this thread.
	 *
	 * @access private
	 * @var    array
	 */
	private $_post = array();

	/**
	 * Get a list of posts that are in the thread.
	 *
	 * @access public
	 * @param  int    $threadId
	 */
	public function __construct($threadId) {
		$this->setInfo($threadId);
	}

	/**
	 * Set the posts.
	 *
	 * @access protected
	 * @param  int $threadId
	 */
	protected function setInfo($threadId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT   p.post_id
			FROM     `conference_post` p
			WHERE    p.thread_id    = :thread_id
			         AND
			         p.post_removed IS NULL
			ORDER BY p.post_created ASC
		");

		// Execute the query
		$statement->execute(array(
			':thread_id' => $threadId
		));

		// Loop over the posts
		while ($post = $statement->fetch()) {
			$this->_post[] = new Model_Conference_Post_Instance($post['post_id']);
		}
	}

	/**
	 * Allow scripts to iterate over the posts.
	 * 
	 * @access public
	 * @return Model_Conference_Post_Instance
	 */
	public function getIterator() {
		return new ArrayIterator($this->_post);
	}
}