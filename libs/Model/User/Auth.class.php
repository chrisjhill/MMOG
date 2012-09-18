<?php
/**
 * Handles keeping a track of who is logged in and whether we need to reload their
 * data to ensure it is fresh.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       18/09/2012
 *
 * @todo Move the identity storage to a Model_Storage?
 */
class Model_User_Auth
{
	/**
	 * Does the user have an identity?
	 * 
	 * @access public
	 * @return boolean
	 */
	public function hasIdentity() {
		return isset($_SESSION['identity']) && $_SESSION['identity'];
	}

	/**
	 * Set the identity of the user.
	 *
	 * To save having to fetch the users data on each reload when it rarely changes
	 * we will only get it once we seem it to be stale, in this case after 60 seconds.
	 * 
	 * @access public
	 * @param $user Model_User_Instance
	 */
	public function putIdentity($user, $stale = 60) {
		// Set the identity, serialized, and also when this data becomes stale
		$_SESSION['identity'] = array(
			'instance' => serialize($user),
			'stale'    => $_SERVER['REQUEST_TIME'] + $stale
		);
	}

	/**
	 * Return the identity of the user logged in.
	 *
	 * We automatically refresh this data
	 * 
	 * @access public
	 * @return Model_User_Instance
	 */
	public function getIdentity() {
		// Do we need to refresh the users information?
		if ($_SERVER['REQUEST_TIME'] > $_SESSION['identity']['stale']) {
			// Reload data
			// Get the old data and unserialize it
			$user = unserialize($_SESSION['identity']['instance']);

			// Create a new user model
			$user = new Model_User_Instance($user['user_id']);

			// Save this identity
			Model_User_Auth::putIdentity($user);
		}

		// If we have refreshed stale data then return that, otherwise from the session
		return isset($user)
			? $user
			: unserialize($_SESSION['identity']['instance']);
	}

	/**
	 * Remove the identity, logging the user out.
	 * 
	 * @access public
	 */
	public function removeIdentity() {
		// Reset the identity and also remove the session
		$_SESSION['identity'] = null;
		unset($_SESSION['identity']);
	}
}