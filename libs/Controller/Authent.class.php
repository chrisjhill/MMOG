<?php
/**
 * Handles logging users in and out of the system.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       17/09/2012
 */
class Controller_Authent extends Core_Controller
{
	/**
	 * Holding action that simply forwards onto the login action.
	 *
	 * @access public
	 */
	public function indexAction() {
		$this->forward('login');
	}

	/**
	 * Allows the user 
	 *
	 * @access public
	 * @param $string string
	 * @return string
	 */
	public function loginAction() {
		// Set some default variables
		$this->view->addVariable('loginMessage', '');
		$this->view->addVariable('title', 'Login to your account');

		// Has the user submitted the form?
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			// Create login model
			$login = new Model_User_Login($_POST['user_email'], $_POST['user_password'], GAME_ROUND);

			// Try and login
			// Throws an Exception if the password is formatted incorrectly
			try {
				// Try and login
				$user = $login->login();
			} catch (Exception $e) {
				// Something went wrong
				$this->view->addVariable(
					'loginMessage',
					$this->view->notice(array(
						'status' => 'error',
						'title'  => 'An error occurred',
						'body'   => $e->getMessage()
					))
				);

				// Render the page now, no need to continue
				$this->view->render();
			}

			// Set the identity of the user
			Model_User_Auth::putIdentity($user);

			// And forward onto the main overview main
			$this->forward('index', 'CountryOverview');
		}
	}
}