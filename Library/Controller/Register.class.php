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
		// Is the user logged in?
		if (Model_User_Auth::hasIdentity()) {
			// Get the user
			$user = Model_User_Auth::getIdentity();

			// Does the user already have a country for this round?
			if ($user->getInfo('country_id')) {
				// The user is already registered for this round
				// Forward them to their overview page
				$this->forward('index', 'headquaters');
			}

			// The user has an identity, but no country
			$this->forward('country');
		}

		// Set some default variables
		$this->view->addVariable('registerMessage', '');
		$this->view->addVariable('title', 'Register an account');

		// Has the user submitted the form?
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_email'])) {
			// Create a new user
			$register = new Model_User_Create($_POST['user_email'], GAME_ROUND);

			// Try and register
			// Throws an Exception if there are any errors
			try {
				// Try and register
				$user = $register->create();
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
		// Is the user logged in?
		if (! Model_User_Auth::hasIdentity()) {
			$this->forward('user');
		} else {
			// Get the user
			$user = Model_User_Auth::getIdentity();

			// Does the user already have a country for this round?
			if ($user->getInfo('country_id')) {
				// The user is already registered for this round
				// Forward them to their overview page
				$this->forward('index', 'headquaters');
			}
		}

		// Set some variables
		$this->view->addVariable('registerMessage', '');
		$this->view->addVariable('title', 'Register a country');

		// Has the user submitted the form?
		// Since we may have come from the previous action we need to also check a field exists
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['country_name'])) {
			// Create a new country
			$country = new Model_Country_Create($_POST['country_ruler_name'], $_POST['country_name']);

			// Do we want to create a private planet?
			if (isset($_POST['planet_private']) && $_POST['planet_private'] = '1') {
				$country->setPrivatePlanet();
			}

			// Try and register
			// Throws an Exception if there are any errors
			try {
				// Try and register
				$countryId = $country->create($user);
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

			// Set the updated user object
			Model_User_Auth::putIdentity(new Model_User_Instance($user->getInfo('user_id')));

			// And forward onto the create country page
			$this->redirect(array('controller' => 'headquaters', 'action' => 'index'));
		}
	}
}