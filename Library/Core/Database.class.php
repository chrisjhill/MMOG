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
	public static $connection;

	/**
	 * Returns the database connection, or connects if does not exist.
	 *
	 * @access public
	 * @return PDO
     * @static
	 */
	public static function getInstance() {
		// Have we already connected?
		if (! self::$connection) {
			// No, try and connect
			try {
				// PDO connection
				// Details from /libs/config.php
				self::$connection = new PDO(
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
			self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			// Show errors in SQL
			// @todo Remove once no longer in development
			self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}

		// We have connected, return the instance
		return self::$connection;
	}
}