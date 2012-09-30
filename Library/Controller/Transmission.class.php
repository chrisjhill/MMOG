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
	/**
	 * Set some controller defaults.
	 *
	 * @access public
	 */
	public function init() {
		// Set the layout
		$this->setLayout('ingame');

		// Load language file
		Core_Language::load('navigation-private');
		
	}

	/**
	 * Show a list of the transmissions in a countries inbound.
	 *
	 * @access public
	 */
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

	/**
	 * Send a transmission to another country.
	 *
	 * @access public
	 */
	public function createAction() {
		// Language declaration
		Core_Language::load('page-transmission-create');
		$lang = Core_Language::getLanguage();

		// Set some default variables
		$this->view->addVariable('title', $lang['transmission-title']);
		$this->view->addVariable('transmissionSendNotice', '');

		// Has the user submitted the form?
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			// Try and send the transmission
			try {
				// Create new transmission
				$transmission = new Model_Transmission_Create();
				$transmission->create(
					$this->view->getVariable('country'),
					$_POST['country_x_coord'],
					$_POST['country_y_coord'],
					$_POST['country_z_coord'],
					$_POST['transmission_subject'],
					$_POST['transmission_message']
				);
			} catch (Exception $e) {
				// Something went wrong
				$this->view->addVariable(
					'transmissionSendNotice',
					$this->view->notice(array(
						'status' => 'error',
						'title'  => $lang['error-title'],
						'body'   => $lang[$e->getMessage()]
					))
				);

				// Render the page now, no need to continue
				$this->view->render();
			}

			// Transmission was sent successfully
			$this->view->addVariable(
				'transmissionSendNotice',
				$this->view->notice(array(
					'status' => 'success',
					'title'  => $lang['success-title'],
					'body'   => $lang['transmission-success-sent']
				))
			);
		}
	}
}