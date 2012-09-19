<?php
/**
 * Handles users registering an account and also a country.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       19/09/2012
 */
class Controller_Register extends Core_Controller
{
	/**
	 * Index action does not do anything, just forward onto the user action.
	 * 
	 * @access public
	 */
	public function indexAction() {
		$this->forward('user');
	}

	/**
	 * Allows users to register an account.
	 *
	 * @access public
	 */
	public function userAction() {
		// Set some default variables
		$this->view->addVariable('registerMessage', '');
		$this->view->addVariable('title', 'Register an account');

		// Has the user submitted the form?
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			// Create register model
			$register = new Model_User_Create($_POST['user_email'], GAME_ROUND);

			// Try and register
			// Throws an Exception if there are any errors
			try {
				// Try and register
				$user = $register->register();
			} catch (Exception $e) {
				// Something went wrong
				$this->view->addVariable(
					'registerMessage',
					$this->view->notice(array(
						'status' => 'error',
						'title'  => 'An error occurred',
						'body'   => $e->getMessage()
					))
				);

				// Render the page now, no need to continue
				$this->view->render();
			}

			// The user has successfully registered
			// Set the identity of the user
			Model_User_Auth::putIdentity($user);

			// Set a success message
			$this->view->addVariable(
				'registerMessage',
				$this->view->notice(array(
					'status' => 'success',
					'title'  => 'Your account is created!',
					'body'   => 'What would you like your Ruler and Country to be named?'
				))
			);

			// And forward onto the create country page
			$this->forward('country');
		}
	}

	/**
	 * Allows users to register a country.
	 *
	 * @access public
	 */
	public function countryAction() {
		// Set some variables
		$this->view->addVariable('title', 'Register a country');

		// Has the user submitted the form?
		// Since we may have come from the previous action we need to also check a field exists
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['country_name'])) {
			// The user has submitted the country register form
		}
	}
}