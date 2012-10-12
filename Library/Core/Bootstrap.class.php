<?php
/**
 * Is informed when certain actions are performed in the MVC. These are:
 *
 * 1. A request is initialised.
 * 2. A controller is initialised.
 * 3. We are about to shutdown (page has been rendered).
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       17/09/2012
 */
class Core_Bootstrap
{
	/**
	 * A request has come in, we know the controller name and the action name.
	 * 
	 * @access public
	 * @param  string $controllerName
	 * @param  string $actionName
	 * @static
	 */
	public static function initRequest($controllerName, $actionName) {
		// Has the user specified a new language?
		if (isset($_GET['language'])) {
			Core_Store::put('language', $_GET['language']);
		}

		// Include the generic words that all pages have
		Core_Language::load('general');
	}

	/**
	 * A controller has been initialised.
	 * 
	 * @access public
	 * @param  Core_Controller$controller
	 * @static
	 */
	public static function initController($controller) {
		// Is this a country controller?
		if (Model_User_Auth::hasIdentity()) {
			// Create instance
			$user    = Model_User_Auth::getIdentity();
			$country = new Model_Country_Instance($user->getInfo('country_id'));
			$planet  = new Model_Planet_Instance($country->getInfo('planet_id'));

			// And add to the view
			$controller->view->addVariable('user',    $user);
			$controller->view->addVariable('country', $country);
			$controller->view->addVariable('planet',  $planet);
		}

		$controller->view->addVariable('lang', isset($user) ? $user->getInfo('user_language') : 'en');
		// Set the layout
		$controller->setLayout('default');
		// Set the game name
		$controller->view->addVariable('titlePostfix', 'MMOG v.' . GAME_VERSION);
	}

	/**
	 * We have finished rendering a page and are about to shut down.
	 * 
	 * @access public
	 * @param  string $controllerName
	 * @param  string $actionName
	 * @static
	 */
	public static function initShutdown($controllerName, $actionName) {
		// Do nothing
	}
}