<?php
/**
 * Connects to the database.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       13/09/2012
 * @singleton
 */
class Core_Database
{
	/**
	 * Static reference to a database connection.
	 *
	 * @access public
	 * @var PDO
	 * @static
	 */
	public static $_connection;

	/**
	 * Returns the database conenction, or connects if doesn't exist.
	 *
	 * @access public
	 * @return PDO
	 */
	public function getInstance() {
		// Have we already connecteD?
		if (! self::$_connection) {
			// No, try and connect
			try {
				// PDO connection
				// Details from /libs/config.php
				self::$_connection = new PDO(
					'mysql:host=' . DB_LOCATION . ';dbname=' . DB_NAME . ';charset=utf8',
					DB_USERNAME,
					DB_PASSWORD
				);
			} catch(PDOException $e) {
				// Oh dear, something went wrong
				die('Error connecting to the database.');
			}

			// We want associate arrays returned by PDO, not the default objects
			// Sorry if you prefer objects, but I prefer arrays :)
			self::$_connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		}

		// We have connected, return the instance
		return self::$_connection;
	}
}