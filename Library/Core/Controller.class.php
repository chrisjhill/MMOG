<?php
/**
 * Connects the Controllers to the Views.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       15/09/2012
 */
class Core_Controller
{
	/**
	 * The controller that we are managing.
	 *
	 * @access public
	 * @var    Core_Controller
	 */
	public $child;

	/**
	 * Instance of the view.
	 *
	 * @access public
	 * @var    Core_View
	 */
	public $view;

	/**
	 * Whether we need to render this controller.
	 *
	 * @access private
	 * @var    boolean
	 */
	private $_void = false;

	/**
	 * The constructor for the core controller.
	 *
	 * @access public
	 */
	public function __construct() {
		// Get the view instance
		$this->view = new Core_View();
	}

	/**
	 * Do we want to use the cache for this file?
	 *
	 * @access public
	 */
	public function cache() {
		// Does the child allow caching?
		if (isset($this->child->enableCache) && $this->child->enableCache) {
			// Create a new cache instance
			$cache = new Core_Cache($this->view->controller . DS . $this->view->action . '.phtml', PATH_VIEW);

			// Set the cache settings
			$cache->setCache(true)
			      ->setCacheLife($this->child->cacheLife);

			// Is there a cache available?
			$this->view->cache = $cache;
		}
	}

	/**
	 * Change the layout from the default.
	 *
	 * @access public
	 * @param  string    $layout
	 * @throws Exception If the layout does not exist.
	 * @return string
	 */
	public function setLayout($layout) {
		// Does this layout exist?
		if (! file_exists(PATH_LAYOUT . $layout . '.phtml')) {
			throw new Exception('Layout does not exist.');
		}

		// The layout exists, so set it
		$this->view->layout = $layout;
	}

	/**
	 * If the user wishes to forward the user to another controller.
	 *
	 * Since we no longer want to render this controller we set it as void. Then, when
	 * the rendering is called it will be ignored.
	 *
	 * This function will keep the same URL, that will not be changed between forwards.
	 *
	 * @access public
	 * @param  string $controller
	 * @param  string $action
	 * @throws Exception
	 */
	public function forward($action = 'index', $controller = '') {
		// Is this an controller forward or an action forward?
		// Controller forward = A new controller
		// Action redirect    = Same controller, different action
		if ($controller == '' || $controller == str_replace('Controller_', '', get_called_class())) {
			Core_Router::loadAction($this->child, $action);
			$this->child->render();
		} else {
			// Controller redirect
			// No longer render this controller, thank you
			$this->_void = true;

			// And start a new router to the desired controller/action
			Core_Router::loadController($controller, $action);
		}
	}

	/**
	 * Redirect the user to a new page.
	 *
	 * This will perform a header redirect, so we will change the URL, and we can also
	 * pass variables.
	 * 
	 * @access public
	 * @param  array  $param
	 */
	public function redirect($param) {
		header('Location: ' . $this->view->url($param)); exit();
	}

	/**
	 * The time has come to render the page.
	 *
	 * @access public
	 */
	public function render() {
		// Do we still wish to render this controller?
		if (! $this->_void) {
			$this->view->render();
		}
	}
}