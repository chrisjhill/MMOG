<?php
/**
 * The overview page for the country.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       22/09/2012
 */
class Controller_Headquaters extends Core_Controller
{
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
		Core_Language::load('page-headquaters');
		$lang = Core_Language::getLanguage();

		// Set default variables
		$this->view->addVariable('title', $lang['headquaters-title']);

		// Tell the planet to fetch the countries
		$planet = $this->view->getVariable('planet');
		$planet->setPlanetCountries();
		$this->view->addVariable('planet', $planet);
	}
}