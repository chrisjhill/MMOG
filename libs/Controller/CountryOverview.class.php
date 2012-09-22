<?php
/**
 * The overview page for the country.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       22/09/2012
 */
class Controller_CountryOverview extends Core_Controller
{
	/**
	 * Display the generic overview page.
	 *
	 * @access public
	 */
	public function indexAction() {
		// Get the user and country
		$user    = Model_User_Auth::getIdentity();
		$country = new Model_Country_Instance($user->getInfo('country_id'));

		// var_dump($user);
		// var_dump($country);

		// Set some default variables
		$this->view->addVariable('title',   'Your account');
		$this->view->addVariable('user',    $user);
		$this->view->addVariable('country', $country);
	}
}