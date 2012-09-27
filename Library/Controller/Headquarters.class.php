<?php
/**
 * The overview page for the country.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       22/09/2012
 */
class Controller_Headquarters extends Core_Controller
{
	/**
	 * Controller initiated.
	 * 
	 * @access public
	 */
	public function init() {
		// Set the layout
		$this->setLayout('ingame');

		// Load language file
		Core_Language::load('navigation-private');
		Core_Language::load('page-headquarters');
	}

	/**
	 * Set whether we want the bootstrap to automatically fetch us the base classes.
	 *
	 * @access public
	 * @var boolean
	 */
	public $setDefaultCountryClasses = true;

	/**
	 * Display the generic overview page.
	 *
	 * @access public
	 */
	public function indexAction() {
		// Load language file
		$lang = Core_Language::getLanguage();

		// Set default variables
		$this->view->addVariable('title', $lang['headquarters-title']);

		// Tell the planet to fetch the countries
		$planet = $this->view->getVariable('planet');
		$planet->setPlanetCountries();
		$this->view->addVariable('planet', $planet);
	}
}