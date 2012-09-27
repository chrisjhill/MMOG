<?php
/**
 * Load a language file.
 *
 * We get the language the user wants from the store. The store will be
 * set if the user logs in (in which case we use their specified language)
 * or if they click on their country flag.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       23/09/2012
 */
class Core_Language
{
	/**
	 * Language files that we have already loaded.
	 *
	 * @access public
	 * @var array
	 * @static
	 */
	public static $includedLanguageFiles = array();

	/**
	 * The container for all the language items.
	 *
	 * @access public
	 * @var array
	 * @static
	 */
	public static $lang = array();

	/**
	 * Tries to load a language file.
	 *
	 * @access public
	 * @param $file string
	 * @param $language string
	 * @throws Exception
	 */
	public function load($file, $language = 'en') {
		// Is there a language in the store?
		if (Core_Store::has('language')) {
			$language = Core_Store::get('language');
		}

		// have we already included this language file?
		if (in_array($file, self::$includedLanguageFiles)) {
			return false;
		}

		// Does the language file exist?
		if (! file_exists(PATH_LANGUAGE . $language . DS . $file . '.php')) {
			throw new Exception('Unable to load the language file ' . $language . '/' . $file);
		}

		// File exists and we have not already loaded it
		include PATH_LANGUAGE . $language . DS . $file . '.php';
	}

	/**
	 * Return the language items.
	 *
	 * @access public
	 * @return array
	 */
	public function getLanguage() {
		return self::$lang;
	}
}