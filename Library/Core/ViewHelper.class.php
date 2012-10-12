<?php
/**
 * Provides interaction to the Core_View to View Helpers.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       12/10/2012
 */
class Core_ViewHelper
{
	/**
	 * The view instance fo View Helpers to interact with.
	 *
	 * @access public
	 * @var    Core_View
	 * @static
	 */
	public static $view;

	/**
	 * Sets the View to the class.
	 *
	 * @access public
	 * @param  Core_View $view
	 */
	public function setView($view) {
		self::$view = $view;
	}
}