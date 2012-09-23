<?php
/**
 * Handles logging users in and out of the system.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       17/09/2012
 */
class Controller_Authent extends Core_Controller
{
	/**
	 * Holding action that simply forwards onto the login action.
	 *
	 * @access public
	 */
	public function indexAction() {
		$this->forward('login');
	}

	/**
	 * Allows the user 
	 *
	 * @access public
	 * @param $string string
	 * @return string
	 */
	public function loginAction() {
		// Load language file
		Core_Language::load('page-login');
		$lang = Core_Language::getLanguage();

		// Set some default variables
		$this->view->addVariable('loginMessage', '');
		$this->view->addVariable('title', $lang['login-title']);

		// Has the user submitted the form?
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			// Create login model
			$login = new Model_User_Login($_POST['user_email'], $_POST['user_password'], GAME_ROUND);

			// Try and login
			// Throws an Exception if the password is formatted incorrectly
			try {
				// Try and login
				$user = $login->login();
			} catch (Exception $e) {
				// Something went wrong
				$this->view->addVariable(
					'loginMessage',
					$this->view->notice(array(
						'status' => 'error',
						'title'  => $lang['login-error-title'],
						'body'   => $lang[$e->getMessage()]
					))
				);

				// Render the page now, no need to continue
				$this->view->render();
			}

			// Set the identity of the user
			Model_User_Auth::putIdentity($user);

			// Set the language the user wants to use
			Core_Store::put('language', $user->getInfo('user_language'));

			// And forward onto the main overview main
			$this->redirect(array('controller' => 'headquaters'));
		}
	}

	/**
	 * Log the user out of the site.
	 * 
	 * @access public
	 */
	public function logoutAction() {
		// Remove the session
		session_unset();
		session_destroy();

		// And redirect them back to the index page
		$this->redirect(array('controller' => 'index', 'action' => 'index', 'variables' => array('goodbye' => true)));
	}
}