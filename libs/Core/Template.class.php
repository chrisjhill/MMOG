<?php
/**
 * Return snippets of HTML with variable replacement.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       15/09/2012
 *
 * @todo A way to merge a group of templates into one cache (e.g., battle report).
 */
class Core_Template extends Core_Cache
{
	/**
	 * The variables that we wish to replace.
	 *
	 * Note: Variables in the template file are wrapped in { and }. You do not need
	 * to pass these in, we'll do that for you automatically.
	 *
	 * <code>
	 * array(
	 *     'foo'    => 'bar',
	 *     'foobar' => 'The replacement string'
	 * )
	 * </code>
	 *
	 * @access private
	 * @var array
	 */
	private $_variable = array();

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
		parent::__construct($template);
	}

	/**
	 * Add a variable to be replaced.
	 *
	 * Note: If you pass in the same variable twice then it will overwrite the first.
	 *
	 * @access public
	 * @param $variable string
	 * @param $value string
	 * @return Core_Template
	 */
	public function addVariable($variable, $value) {
		$this->_variable[$variable] = $value;
		return $this;
	}

	/**
	 * Return the template with the variables replaced.
	 *
	 * If we can use a cached version of the file then we will, otherwise we
	 * will render the template fresh.
	 *
	 * @access public
	 * @return string
	 */
	public function render() {
		// Can we use a cached template?
		if ($this->cachedTemplateAvailable()) {
			// We can use a cached copy, mucho quick
			return $this->getCachedTemplate();
		}

		// Nope, looks as though we are generating a fresh template
		$content = file_get_contents(PATH_TEMPLATE . $this->_template);

		// Start the replacements
		foreach ($this->_variable as $variable => $value) {
			// Replace all instances
			$content = str_replace('{' . $variable . '}', $value, $content);
		}

		// Do we want to save this to the cache
		if ($this->_enableCache) {
			$this->saveTemplate($content);
		}

		// Rendering complete
		return $content;
	}
}