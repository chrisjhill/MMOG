<?php
/**
 * Allow countries to talk to each other.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       06/10/2012
 */
class Controller_Conference extends Core_Controller
{
	/**
	 * Initiate the controller.
	 *
	 * @access public
	 */
	public function init() {
		// Set the layout
		$this->setLayout('ingame');

		// Load language file
		Core_Language::load('navigation-private');
		Core_Language::load('page-conference');

		// Set variables
		$this->view->addVariable('threadCreateNotice', '');
	}

	/**
	 * Display a list of threads that countries have created.
	 *
	 * @access public
	 */
	public function indexAction() {
		// Load language file
		$lang = Core_Language::getLanguage();

		// Set default variables
		$this->view->addVariable('title', $lang['conference-title']);

		// We need the planet
		$planet = $this->view->getVariable('planet');

		// Get a list of threads for this planet
		$threads = new Model_Conference_Thread_List($planet->getInfo('planet_id'));
		$this->view->addVariable('threads', $threads);
	}

	/**
	 * Tries to create a new thread.
	 * 
	 * @access public
	 */
	public function createAction() {
		// Load language file
		$lang = Core_Language::getLanguage();

		// Try and create a new thread
		// But only if the user gas submitted the form
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			try {
				$threadCreate = new Model_Conference_Thread_Create();
				$threadCreate->create(
					$this->view->getVariable('country'),
					$this->view->getVariable('planet'),
					$_POST['thread_subject'],
					$_POST['thread_message']
				);
			} catch(Exception $e) {
				// Unable to create a new conference thread
				// Set the notice
				$this->view->addVariable(
					'threadCreateNotice',
					$this->view->notice(array(
						'status' => 'error',
						'title'  => $lang['error-title'],
						'body'   => $lang[$e->getMessage()]
					))
				);

				// And render
				$this->forward('index');
			}
		}

		// This action has finished, forward onto the index action to render
		$this->forward('index');
	}
}