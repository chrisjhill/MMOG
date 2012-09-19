<?php
/**
 * The index controller. Will display the main page.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       15/09/2012
 */
class Controller_Index extends Core_Controller
{
	/**
	 * Introduces the game.
	 *
	 * @access public
	 */
	public function indexAction() {
		// Add a variable to the view
		$this->view->addVariable('title', 'Welcome to the Game');
	}
}