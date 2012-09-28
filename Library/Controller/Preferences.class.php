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

		// Set the page title
		$this->view->addVariable('title', $lang['preferences-title']);
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
		// Do update
		
		// And forward
		$this->forward('index');	
	}

	/**
	 * Handles the changing of planet.
	 *
	 * @access public
	 */
	public function changePlanetAction() {
		// Do update
		
		// And forward
		$this->forward('index');
	}

	/**
	 * Handles the chanhging of the users password.
	 *
	 * @access public
	 */
	public function changePasswordAction() {
		// Do update
		
		// And forward
		$this->forward('index');
	}
}