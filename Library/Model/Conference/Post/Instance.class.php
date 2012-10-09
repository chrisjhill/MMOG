<?php
/**
 * Handles a single conference post.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       09/10/2012
 */
class Model_Conference_Post_Instance extends Core_Instance
{
	/**
	 * Set up the post information.
	 *
	 * @access public
	 * @param  int    $postId
	 */
	public function __construct($postId) {
		// Set post information
		$this->setInfo($postId);
	}

	/**
	 * Set the information on the post.
	 *
	 * @access protected
	 * @param  int       $postId
	 */
	protected function setInfo($postId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT p.post_id, p.thread_id, p.country_id,
			       p.post_message,
			       p.post_created, p.post_updated, p.post_removed
			FROM   `conference_post` p
			WHERE  p.post_id      = :post_id
			       AND
			       p.post_removed IS NULL
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':post_id'  => $postId
		));

		// Did we find the post?
		if ($statement->rowCount() >= 1) {
			// Yes, set the information
			$this->_info = $statement->fetch();
			$this->_info['country'] = new Model_Country_Instance($this->getInfo('country_id'));
		}
	}
}