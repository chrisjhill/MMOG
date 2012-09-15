<?php
/**
 * Handles caching of template files.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       15/09/2012
 */
class Core_Cache
{
	/**
	 * The template file that we want to use.
	 * 
	 * @access protected
	 * @var string
	 */
	protected $_template;

	/**
	 * Whether we can actually use the cache.
	 *
	 * Set to false by default as we shouldn't need to use it.
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $_enableCache = false;

	/**
	 * How long we should use the cache before regenerating.
	 *
	 * Set to one hour by default.
	 *
	 * @access private
	 * @var int
	 */
	private $_cacheLife = 3600;

	/**
	 * Whether this cache is for a specific user and not for general population.
	 *
	 * The user ID will be appended to the start of the file name, e.g.,
	 * 123_template_name.tpl
	 *
	 * @access private
	 * @var int
	 */
	private $_cacheUser;

	/**
	 * The location that we are going to use to store the cached file.
	 *
	 * @access private
	 * @var string
	 */
	private $_cacheLocation;

	/**
	 * Start to build the template snippet.
	 *
	 * We only want to pass in a template file at the moment. We do not want to give the
	 * constructor too much power, and we would rather build the template up as we go along.
	 *
	 * @access public
	 * @throws Exception
	 */
	public function __construct($template) {
		// Do we actually have this template file?
		if (! file_exists(PATH_TEMPLATE . $template)) {
			throw new Exception('Unable to locate the template file: ' . PATH_TEMPLATE . $template);
		}

		// Set the template file
		$this->_template = $template;
	}

	/**
	 * Set whether we want to use the cache or not.
	 *
	 * @access public
	 * @param $enableCache boolean
	 * @return Core_Template
	 */
	public function setCache($enableCache) {
		$this->_enableCache = $enableCache;
		return $this;
	}

	/**
	 * How long we should keep the copy of the cache before regenerating.
	 *
	 * Pass in seconds (3600 = one hour, 86400 = one day, etc.).
	 *
	 * @access public
	 * @param $life int
	 * @return Core_Template
	 */
	public function setCacheLife($life) {
		$this->_cacheLife = $life;
		return $this;
	}

	/**
	 * Set whether this cache is meant for a particular user.
	 *
	 * @access public
	 * @param $userId int
	 * @return Core_Template
	 */
	public function setUser($userId) {
		$this->_cacheUser = $userId;
		return $this;
	}

	/**
	 * Build the cache location.
	 *
	 * @access private
	 */
	private function setCacheLocation() {
		// Have we already set the cache location?
		if ($this->_cacheLocation) {
			return false;
		}

		// Build the location of the cache
		// A specific user?
		if ($this->_cacheUser) {
			// For a specific user
			$this->_cacheLocation = $this->_cacheUser . '_';
		}

		// And set the non-unique file name section
		$this->_cacheLocation = str_replace('/', '_', $this->_template);
	}

	/**
	 * Can we use the cache?
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function cachedTemplateAvailable() {
		// Have we said we want to use the cache?
		if (! $this->_enableCache) {
			return false;
		}

		// Set the location of the cache file
		$this->setCacheLocation();

		// Does the template already exist?
		return file_exists(PATH_CACHE . $this->_cacheLocation);
	}

	/**
	 * Get the cahced template that is pre-rendered.
	 * 
	 * @access protected
	 * @return string
	 */
	protected function getCachedTemplate() {
		return file_get_contents(PATH_CACHE . $this->_cacheLocation);
	}

	/**
	 * Save the template to the cache.
	 * 
	 * @access protected
	 * @param $content string
	 */
	protected function saveTemplate($content) {
		file_put_contents(PATH_CACHE . $this->_cacheLocation, $content);
	}
}