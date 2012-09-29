<?php
/**
 * Handles saving country information, changing planets and passwords.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       28/09/2012
 */
class Controller_Preferences extends Core_Controller
{
	/**
	 * Controller initiated
	 * @return [type] [description]
	 */
	public function init() {
		// Set the layout
		$this->setLayout('ingame');

		// Load language file
		Core_Language::load('navigation-private');
		Core_Language::load('page-preferences');
		$lang = Core_Language::getLanguage();

		// Set some default variables
		$this->view->addVariable('title', $lang['preferences-title']);
		$this->view->addVariable('rulerAndCountryNameChangeNotice', '');
		$this->view->addVariable('planetRelocateNotice', '');
		$this->view->addVariable('changePasswordNotice', '');
	}		

	/**
	 * The other actions in this controller forward onto this action.
	 *
	 * @access public
	 */
	public function indexAction() {
		// Do nothing
	}

	/**
	 * Handles the changing of ruler and country name.
	 *
	 * @access public
	 */
	public function changeRulerAndCountryNameAction() {
		// Only need to render if the user has submitted the form
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			$this->forward('index');
		}
		
		// Get the language
		$lang = Core_Language::getLanguage();

		// Get the country
		$country = $this->view->getVariable('country');

		// Try and perform the update
		try {
			// Create country update instance
			$countryUpdate = new Model_Country_Update();
			$countryUpdate->updateRulerAndCountryName(
				$country,
				$_POST['country_ruler_name'],
				$_POST['country_name']
			);
		} catch (Exception $e) {
			// Unable to update the ruler and country name
			// Set the notice
			$this->view->addVariable(
				'rulerAndCountryNameChangeNotice',
				$this->view->notice(array(
					'status' => 'error',
					'title'  => $lang['error-title'],
					'body'   => $lang[$e->getMessage()]
				))
			);

			// And render
			$this->forward('index');
		}

		// Everything went well
		// Reload the country information
		$this->view->addVariable('country', new Model_Country_Instance($country->getInfo('country_id')));

		// Set the success message
		$this->view->addVariable(
			'rulerAndCountryNameChangeNotice',
			$this->view->notice(array(
				'status' => 'success',
				'title'  => $lang['success-title'],
				'body'   => $lang['preferences-success-name-change']
			))
		);
		
		// And forward
		$this->forward('index');	
	}

	/**
	 * Handles the changing of planet.
	 *
	 * @access public
	 */
	public function changePlanetAction() {
		// Only need to render if the user has submitted the form
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			$this->forward('index');
		}
		
		// Get the language
		$lang = Core_Language::getLanguage();

		// Get the country
		$country = $this->view->getVariable('country');

		// Try and relocate the planet
		try {
			// Create country update instance
			$countryUpdate = new Model_Country_Update();
			$countryUpdate->planetRelocate($country, $_POST['planet_password']);
		} catch (Exception $e) {
			// Unable to update the ruler and country name
			// Set the notice
			$this->view->addVariable(
				'planetRelocateNotice',
				$this->view->notice(array(
					'status' => 'error',
					'title'  => $lang['error-title'],
					'body'   => $lang[$e->getMessage()]
				))
			);

			// And render
			$this->forward('index');
		}

		// Everything went well
		// Reload the country information
		$this->view->addVariable('country', new Model_Country_Instance($country->getInfo('country_id')));

		// Set the success message
		$this->view->addVariable(
			'planetRelocateNotice',
			$this->view->notice(array(
				'status' => 'success',
				'title'  => $lang['success-title'],
				'body'   => $lang['preferences-success-relocated']
			))
		);
		
		// And forward
		$this->forward('index');
	}

	/**
	 * Handles the changing of the users password.
	 *
	 * @access public
	 */
	public function changePasswordAction() {
		// Only need to render if the user has submitted the form
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			$this->forward('index');
		}

		// We share some of the exception messages with the login
		Core_Language::load('page-login');
		$lang = Core_Language::getLanguage();

		// Try and update the users password
		try {
			// Get the user object
			$user = $this->view->getVariable('user');

			// And try the update
			$userUpdate = new Model_User_Update();
			$userUpdate->changePassword($user, $_POST['user_password_current'], $_POST['user_password']);
		} catch (Exception $e) {
			// Unable to update the ruler and country name
			// Set the notice
			$this->view->addVariable(
				'changePasswordNotice',
				$this->view->notice(array(
					'status' => 'error',
					'title'  => $lang['error-title'],
					'body'   => $lang[$e->getMessage()]
				))
			);

			// And render
			$this->forward('index');
		}

		// Password changed
		$this->view->addVariable(
			'changePasswordNotice',
			$this->view->notice(array(
				'status' => 'success',
				'title'  => $lang['success-title'],
				'body'   => $lang['preferences-success-changed-password']
			))
		);
		
		// And forward
		$this->forward('index');
	}
}