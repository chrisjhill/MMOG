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
		Core_Language::load('page-conference');
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
		Core_Language::load('page-conference');
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

	/**
	 * Viewing a thread.
	 * 
	 * @access public
	 */
	public function viewAction() {
		// Load language file
		Core_Language::load('page-conference-thread');
		$lang = Core_Language::getLanguage();

		// Get the thread
		try {
			$thread = new Model_Conference_Thread_Instance($_GET['thread']);
		} catch (Exception $e) {
			// We were unable to locate the thread
			// Just forward onto the index action
			$this->forward('index');
		}

		// We found the thread
		// Add the thread to the view
		$this->view->addVariable('thread', $thread);

		// Set the title
		$this->view->addVariable('title', $thread->getInfo('thread_subject') . ' | ' . $lang['conference-title']);

		// Get a list of all the posts that are in this thread
		$posts = new Model_Conference_Post_List($thread->getInfo('thread_id'));
		// And add them to the view
		$this->view->addVariable('posts', $posts);
	}
}