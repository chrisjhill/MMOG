<?php
/**
 * Handles sending and reading mails from other countries.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       29/09/2012
 */
class Controller_Transmission extends Core_Controller
{
	public function init() {
		// Set the layout
		$this->setLayout('ingame');

		// Load language file
		Core_Language::load('navigation-private');
		
	}

	public function indexAction() {
		// Language declaration
		Core_Language::load('page-transmission-inbound');
		$lang = Core_Language::getLanguage();

		// Set some default variables
		$this->view->addVariable('title', $lang['transmission-title']);

		// Get the transmission
		$this->view->addVariable(
			'transmissions',
			new Model_Transmission_List(array('country' => $this->view->getVariable('country')))
		);
	}
}