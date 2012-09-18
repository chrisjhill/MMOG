<?php
/**
 * Information on a user. A user can be playing multiple games.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       18/09/2012
 */
class Model_User_Instance
{
	/**
	 * Information on the user.
	 *
	 * <code>
	 * array(
	 *     'user_id'         => 12345,
	 *     'user_email'      => 'coyote@acme.com',
	 *     'user_password'   => 'abc123',
	 *     'user_created'    => 1234567890,
	 *     'user_verified'   => 1234567890,
	 *     'user_last_login' => 1234567890,
	 *     'user_updated'    => 1234567890,
	 *     'user_removed'    => 1234567890
	 * )
	 * </code>
	 *
	 * @access private
	 * @var array
	 */
	private $_info = array();

	/**
	 * Class constructor. Requires an ID.
	 *
	 * @access public
	 * @param int $userId
	 */
	public function __construct($userId) {
		$this->setInfo($userId);
	}

	/**
	 * Get the user information from the database and set it locally.
	 *
	 * @access public
	 * @param int $userId
	 */
	public function setInfo($userId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		// Prepare the SQL
		$statement = $database->prepare("
			SELECT u.user_id, u.user_email, u.user_password,
			       u.user_created, u.user_verified, u.user_last_login, u.user_updated, u.user_removed
			FROM   `user` u
			WHERE  u.user_id = :user_id
			LIMIT  1
		");
		// Execute the query
		$statement->execute(array(
			':user_id' => $userId
		));

		// Did we find the user?
		if ($statement->rowCount() >= 1) {
			// Yes, set the information
			$this->_info = $statement->fetch();
		}
	}

	/**
	 * Return a peice of information on the user.
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