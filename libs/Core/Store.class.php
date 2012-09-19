<?php
/**
 * Handles the storing of data for the application, for both page loads
 * and session based data.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       19/09/2012
 */
class Core_Store
{
	/**
	 * Page based variables.
	 *
	 * Note: These variable will only be available for the single page load unless
	 * you specify $persistence as 'session' (which it is by default).
	 *
	 * @access public
	 * @var array
	 * @static
	 */
	public statis $store;

	/**
	 * Returns true or false as to if the variable exists in the storage medium.
	 * 
	 * @access public
	 * @param $variable string
	 * @param $persistence string
	 * @return boolean
	 * @static
	 */
	public static function has($variable, $persistence = 'session') {
		// Does this variable exist in the session?
		if ($persistence == 'session') {
			return isset($_SESSION['store'][$variable]);
		}

		// A local variable
		return isset(self::$store[$variable]);
	}

	/**
	 * Store a variable for use later on.
	 *
	 * If you pass in an array or object then it will be serialized and
	 * deserialized automatically when storing in the session.
	 * 
	 * @access public
	 * @param $variable string
	 * @param $value mixed
	 * @param $persistence string
	 * @return boolean
	 * @static
	 */
	public static function put($variable, $value, $persistence = 'session') {
		// Do we need to store this variable in the session?
		if ($persistence == 'session') {
			$_SESSION['store'][$variable] = is_array($vale) || is_object($value)
				? serialize($value)
				: $value;
		}

		// A local variable
		self::$store[$variable] = $value;
	}

	/**
	 * Return the variable we were asked to store.
	 *
	 * This function will return boolean false if the variable does not exist in
	 * the store.
	 * 
	 * @access public
	 * @param $variable string
	 * @param $persistence string
	 * @return mixed
	 * @static
	 */
	public static function get($variable, $persistence = 'session') {
		// Do we need to fetch from the session?
		if ($persistence == 'session') {
			// Stored in the session, but does it exist?
			if (! isset($_SESSION['store'][$variable])) {
				return false;
			}

			// Since we do not know if this variable has been seriablized we need to unserialize it
			// PHP will return boolean false if the variable cannot be unserialized
			// It will also throw an E_NOTICE, so we need to error supress it with the @
			// However, the value might also be boolean false, so check for that also
			$value = @unserialize($_SESSION['store'][$variable]);

			// And return
			return $value !== false && $_SESSION['store'][$variable] != 'b:0;'
				? $value
				: $_SESSION['store'][$variable];
		}

		// A local variable
		return isset(self::$store[$variable])
			? self::$store[$variable]
			: false;
	}

	/**
	 * Removes a variable from the store.
	 * 
	 * @access public
	 * @param $variable string
	 * @param $persistence string
	 * @static
	 */
	public static function remove($variable, $persistence = 'session') {
		// Do we need to remove it from the session?
		if ($persistence == 'session') {
			unset($_SESSION['store'][$variable]);
		}

		// A local variable
		unset(self::$store[$variable]);
	}
}