<?php
/**
 * Handles updating user information.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       29/09/2012
 */
class Model_User_Update
{
	/**
	 * Update the users password.
	 *
	 * User needs to supply their current password to make sure it's really them.
	 *
	 * @access public
	 * @param  Model_User_Instance $user
	 * @param  string              $currentPassword
	 * @param  string              $newPassword
	 * @throws Exception           If the password is in the incorrect format.
	 * @return boolean
	 */
	public function changePassword($user, $currentPassword, $newPassword) {
		// Make sure the password is less than 72 characters
		if (strlen($currentPassword) <= 4 || strlen($currentPassword) >= 72) {
			throw new Exception('login-error-incorrect-format');
		}

		// Is the current password correct?
		// If incorrect this function will throw its own '' Exception
		$userLogin = new Model_User_Login($user->getInfo('user_email'), $currentPassword);
		$userLogin->login();

		// Current password is correct
		// Create a new hashing instance
		$hashAlgorithm = new Core_Password(8, false);

		// And create a hashed password
		$passwordHash = $hashAlgorithm->HashPassword($newPassword);

		// Get the database connection
		$database  = Core_Database::getInstance();

		// Update the users password
		$statement = $database->prepare("
			UPDATE `user` u
			SET    u.user_password = :user_password,
			       u.user_updated  = NOW()
			WHERE  u.user_id       = :user_id
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':user_password' => $passwordHash,
			':user_id'       => $user->getInfo('user_id')
		));

		// Log this event
		Core_Log::add(array(
			'user_id'     => $user->getInfo('user_id'),
			'log_action'  => 'update-password',
			'log_status'  => 'success',
			'log_message' => 'User successfully updated their password.'
		));

		// And return
		return true;
	}
}