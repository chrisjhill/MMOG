<?php
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
	 * Information on whether to cache the view or not.
	 * 
	 * @access public
	 * @var Core_Cache
	 */
	public $cache;

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
	 * Render the page.
	 *
	 * @access public
	 */
	public function render() {
		// Can we use a cache to speed things up?
		// If the cache object exists then it means the controller wants to use caching
		if ($this->cache->cachedFileAvailable()) {
			// The cache is enabled and there is an instance of the file in cache
			echo $this->cache->getCachedFile();
			return true;
		}

		// Does the view file exist?
		if (! file_exists(PATH_VIEW . $this->controller . DIRECTORY_SEPARATOR . $this->action . '.phtml')) {
			throw new Exception('The view ' . $this->action . ' does not exist in ' . $this->controller . '.');
		}

		// The view exists
		// Extract the variables that have been set
		if ($this->_variables) {
			extract($this->_variables);
		}

		// Enable object buffering if we want a cache
		if ($this->cache) {
			ob_start();
		}

		// And include the file for parsing
		include PATH_VIEW . $this->controller . DIRECTORY_SEPARATOR . $this->action . '.phtml';

		// If we are using the cache then save it
		if ($this->cache) {
			// Quickly state that this is a cached file they are seeing
			// And when it was generated and set to expire
			echo "\n" . '<!--
				This page was cached on ' . date('r', $_SERVER['REQUEST_TIME']) . '
				and is due to expire on ' . date('r', ($_SERVER['REQUEST_TIME'] + $this->cache->getCacheLife())) .
				"\n" . '//-->';

			// Save the cache to disk
			$this->cache->saveFileToCache(ob_get_contents());
			ob_end_flush();
		}
	}
}