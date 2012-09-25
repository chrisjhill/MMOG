<?php
/**
 * Handles the logged information stored in the database.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       25/09/2012
 */
class Core_Log
{
	/**
	 * Add a new log to the database.
	 *
	 * <code>
	 * array(
	 *     'user_id'     => 123,
	 *     'country_id'  => 123,
	 *     'log_action'  => 'add-user',
	 *     'log_status'  => 'success',
	 *     'log_message' => 'User added successfully'
	 * )
	 * </code>
	 * 
	 * @access public
	 * @static
	 */
	public static function add($param) {
		// Is this in reference to a user?
		if (! isset($param['user_id'])) {
			// Is there a user logged in?
			if (Model_User_Auth::hasIdentity()) {
				// Yes, get their information and place it into the param array
				$user = Model_User_Auth::getIdentity();
				$param['user_id'] = $user->getInfo('user_id');
			}
		}

		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			INSERT INTO `log`
				(
					`round_id`,
					`user_id`,
					`log_action`,
					`log_status`,
					`log_message`,
					`log_url`,
					`log_ip`,
					`log_user_agent`
				)
			VALUES
				(
					:round_id,
					:user_id,
					:log_action,
					:log_status,
					:log_message,
					:log_url,
					:log_ip,
					:log_user_agent
				)
		");

		// Execute the query
		$statement->execute(array(
			':round_id'       => GAME_ROUND,
			':user_id'        => $param['user_id'],
			':log_action'     => $param['log_action'],
			':log_status'     => $param['log_status'],
			':log_message'    => $param['log_message'],
			':log_url'        => $_SERVER['REQUEST_URI'],
			':log_ip'         => $_SERVER['REMOTE_ADDR'],
			':log_user_agent' => $_SERVER['HTTP_USER_AGENT']
		));
	}
}