<?php
/**
 * Finds the planet the user wishes to view.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       23/09/2012
 */
class Controller_Planet extends Core_Controller
{
	/**
	 * This method is called as soon as the class is instantiated.
	 *
	 * Sets the planet to explore and whether this is the countries own planet.
	 *
	 * @access public
	 */
	public function init() {
		// Set the layout
		$this->setLayout('ingame');

		// Load language file
		Core_Language::load('navigation-private');
		Core_Language::load('page-planet');
		$lang = Core_Language::getLanguage();

		// Set the title
		$this->view->addVariable('title', $lang['planet-title']);

		// Get the country and the planet
		$country = $this->view->getVariable('country');

		// Are the X and Y coords set?
		$xCoord = isset($_GET['x']) ? $_GET['x'] : $country->getInfo('country_x_coord');
		$yCoord = isset($_GET['y']) ? $_GET['y'] : $country->getInfo('country_y_coord');

		// Is this our country, or are we trying to explode?
		if (
			$country->getInfo('country_x_coord') == $xCoord &&
			$country->getInfo('country_y_coord') == $yCoord) {
			// This is the countries own planet
			$this->view->addVariable('planetOwn', true);

			// Get our own planet
			$planetExplore = $this->view->getVariable('planet');
		} else {
			// This is not our country
			// We have gone exploring!
			$this->view->addVariable('planetOwn', false);

			// Get the planet from the coordinates entered
			// Just because they have gone to a set or coords doesn't mean it exists
			try {
				$planetExplore = new Model_Planet_Instance(Model_Planet_CoordsToPlanetId::get($xCoord, $yCoord));
			} catch(Exception $e) {
				// Planet does not exist
				// Forward onto the non existant planet
				$this->forward('uninhabited');
			}
		}

		// We need the country list
		$planetExplore->setPlanetCountries();

		// Add it to the view
		$this->view->addVariable('planetExplore', $planetExplore);		
	}

	/**
	 * If the user is viewing their own planet then they will reach this action.
	 *
	 * Since we have already set all of the variables required in the init()
	 * method we do not need to do anything. We just need to forward onto the
	 * explore actions so we have a pretty URL (/planet/explore).
	 * 
	 * @access public
	 */
	public function indexAction() {
		// Make a nice URL
		$this->forward('explore');
	}

	/**
	 * This function just exists for a pretty URL.
	 * 
	 * @access public
	 */
	public function exploreAction() {
		// Do nothing, just here for a nice URL
	}

	/**
	 * The planet the user went to does not exist.
	 * 
	 * @access public
	 */
	public function uninhabitedAction() {
		// Do nothing
	}
}