<?php
/**
 * Information on a user. A user can be playing multiple games.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       18/09/2012
 */
class Model_User_Instance extends Core_Instance
{
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
	 * @access protected
	 * @param int $userId
	 */
	protected function setInfo($userId) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT u.user_id, u.user_email,
			       u.user_language,
			       u.user_created, u.user_verified, u.user_last_login, u.user_updated, u.user_removed,
			       c.country_id
			FROM   `user` u
			           LEFT JOIN `country` c ON c.user_id = u.user_id AND c.round_id = :round_id
			WHERE  u.user_id = :user_id
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':user_id'  => $userId,
			':round_id' => GAME_ROUND
		));

		// Did we find the user?
		if ($statement->rowCount() >= 1) {
			// Yes, set the information
			$this->_info = $statement->fetch();
		}
	}
}