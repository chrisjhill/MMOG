<?php
/**
 * Handles the main functionality of the view including the parsing,
 * caching, variable storage.
 *
 * Also controls which layout will be shown and provides the means for
 * View Helpers to be called.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       15/09/2012
 */
class Core_View
{
	/**
	 * Which layout we are going to use for this view.
	 *
	 * @access public
	 * @var string
	 */
	public $layout = 'default';

	/**
	 * The controller that we need to render.
	 *
	 * @access public
	 * @var string
	 */
	public $controller = 'index';

	/**
	 * The action that we need to render.
	 *
	 * @access public
	 * @var string
	 */
	public $action = 'index';

	/**
	 * Information on whether to cache the view or not.
	 * 
	 * @access public
	 * @var Core_Cache
	 */
	public $cache;

	/**
	 * The variables that we want to pass to this view.
	 *
	 * @access public
	 * @var array
	 */
	public $_variables = array();

	/**
	 * Add a variable to the view.
	 *
	 * These variables will be made available to the view. Any variable that has already
	 * been defined will be overwritten.
	 *
	 * @access public
	 * @param $variable string
	 * @param $value string
	 */
	public function addVariable($variable, $value) {
		$this->_variables[$variable] = $value;
	}

	/**
	 * Returns a set variable if it exists.
	 *
	 * @access public
	 * @param $variable string
	 * @return mixed
	 */
	public function getVariable($variable) {
		return isset($this->_variables[$variable])
			? $this->_variables[$variable]
			: false;
	}

	/**
	 * Render the page.
	 *
	 * @access public
	 */
	public function render() {
		// Can we use a cache to speed things up?
		// If the cache object exists then it means the controller wants to use caching
		// However, the action might have disabled it
		if ($this->cache && $this->cache->cachedFileAvailable()) {
			// The cache is enabled and there is an instance of the file in cache
			$viewContent = $this->cache->getCachedFile();
		}

		// Nope, there is no cache
		else {
			// Does the view file exist?
			if (! file_exists(PATH_SCRIPT . $this->controller . DS . $this->action . '.phtml')) {
				throw new Exception('The view ' . $this->action . ' does not exist in ' . $this->controller);
			}

			// The view exists
			// Extract the variables that have been set
			if ($this->_variables) {
				extract($this->_variables);
			}

			// Get the language items
			$lang = Core_Language::getLanguage();

			// Enable object buffering
			ob_start();

			// And include the file for parsing
			include PATH_SCRIPT . $this->controller . DS . $this->action . '.phtml';

			// Get the content of the view after parsing, and dispose of the buffer
			$viewContent = ob_get_contents();
			ob_end_clean();

			// If we are using the cache then save it
			if ($this->cache && $this->cache->getCacheEnabled()) {
				$this->cache->saveFileToCache($viewContent);
			}
		}

		// Include the layout
		include PATH_LAYOUT . $this->layout . '.phtml';

		// Inform the bootstrap that we are about to shutdown
		Core_Bootstrap::initShutdown($this->controller, $this->action);

		// And now, the journey ends
		// We die so that we do not call other action's render()
		die();
	}

	/**
	 * Make a URL.
	 *
	 * By default we do not use the URL variables, but you can chose to do so.
	 *
	 * <code>
	 * array(
	 *     'controller'      => 'Index',
	 *     'action'          => 'Index',
	 *     'variables'       => array(
	 *         'foo' => 'bar',
	 *         'bar' => 'foobar'
	 *     )
	 *     'variable_retain' => false
	 * )
	 * </code>
	 *
	 * @access public
	 * @param $param array
	 * @return string
	 */
	public function url($param = array()) {
		// Set some defaults
		$defaults = array(
			'controller'      => $this->controller,
			'action'          => '',
			'variables'       => isset($param['variable_retain']) && $param['variable_retain']
									? $_GET
									: array(),
			'variable_retain' => false
		);

		// However, we do not want the controller/action in the variable list
		unset($defaults['variables']['controller'], $defaults['variables']['action']);

		// Merge these in with the parameters
		// Parameters will take precedence
		$param = array_merge($defaults, $param);

		// Start to build URL
		// The controller
		$url = PATH_WEB . $param['controller'] . '/' . $param['action'];

		// Any variables
		if ($param['variables']) {
			// Yes, there are variables to append, loop over them
			foreach ($param['variables'] as $variable => $value) {
				// If there is an odd amount of variables in the URL string
				// .. then we just set the last variable to true. This needs
				// .. to be the same in this case also.
				$url .= '/' . urlencode($variable) . '/' . ($value === true ? '' : $value);
			}
		}

		// URL has finished constructing, pass back
		return $url;
	}

	/**
	 * Ensure that a string is safe to be outputted to the browser.
	 *
	 * @access public
	 * @param $string string
	 * @return string
	 */
	public function safe($string) {
		return Core_Format::safeHtml($string);
	}

	/**
	 * Provides a nice interface to call view helpers.
	 *
	 * This is a magic function, so any calls to the view/view helper which do not
	 * exist will end up here. We only pass through the first parameter to make for
	 * a nicer implementation in each view helper. This is why it needs to be an array.
	 *
	 * @access public
	 * @param $helperName string
	 * @param $param array
	 * @return string
	 */
	public function __call($helperName, $param) {
		// Try and instantiate the helper
		$viewHelperClassName = 'View_' . MODULE . '_Helper_' . ucfirst($helperName);
		$viewHelper = new $viewHelperClassName();

		// Call the init helper so they can set up any pre rendering settings
		if (method_exists($viewHelper, 'init')) {
			$param[0] = $viewHelper->init($param[0]);
		}

		// Render and return
		return $viewHelper->render($param[0]);
	}
}