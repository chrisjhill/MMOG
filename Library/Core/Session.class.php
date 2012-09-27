<?php
class Core_Session
{
	/**
	 * The database connection we have created.
	 *
	 * @access private
	 * @var Core_Database
	 */
	private $_database;

	/**
	 * Open a connection to the database.
	 *
	 * @access public
     * @param $session string
     * @param $sessionName string
	 * @return boolean
	 */
	public function open($session, $sessionName) {
		// Set database object
		$this->_database  = Core_Database::getInstance();

		// Yes, we established a connection
		return true;
	}

	/**
	 * Retrieve the session from the database.
	 *
	 * @access public
	 * @param $sessionId string
	 * @return string
	 */
	public function read($sessionId) {
		// Prepare the SQL
		$statement = $this->_database->prepare("
			SELECT s.session_data
			FROM   `session` s
			WHERE  s.session_id            = :session_id
			       AND
			       s.session_browser_agent = :session_browser_agent
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':session_id'            => $sessionId,
			':session_browser_agent' => md5($_SERVER['HTTP_USER_AGENT'])
		));

		// Did we find the country?
		if ($statement->rowCount() >= 1) {
			// Yes, set the information
			$sessionData = $statement->fetch();
			return $sessionData['session_data'];
		}

		// We found no session, return a blank string
		return '';
	}

	/**
	 * Write the session data to the database.
	 *
	 * @access public
	 * @param $sessionId string
	 * @param $sessionData string
     * @return boolean
	 */
	public function write($sessionId, $sessionData) {
		// Do not bother to write if we have no session data
		if ($sessionData == '') {
			return true;
		}

		// Prepare the SQL
		$statement = $this->_database->prepare("
			REPLACE INTO `session`
				(
					`session_id`,
					`session_data`,
					`session_browser_agent`
				)
			VALUES
				(
					:session_id,
					:session_data,
					:session_browser_agent
				)
		");

		// Execute the query
		return $statement->execute(array(
			':session_id'            => $sessionId,
			':session_data'          => $sessionData,
			':session_browser_agent' => md5($_SERVER['HTTP_USER_AGENT'])
		));
	}

	/**
	 * Delete the users session from the database.
	 *
	 * @access public
	 * @param $sessionId string
	 * @return boolean
	 */ 
	public function destroy($sessionId) {
		// Prepare the SQL
		$statement = $this->_database->prepare("
			DELETE FROM `session`
			WHERE       `session_id` = :session_id
			LIMIT       1
		");

		// Execute the query
		return $statement->execute(array(
			':session_id'   => $sessionId
		));
	}

	/**
	 * Removes old sessions from the database.
	 *
	 * There is a small chance (0.1%) that this function will be called on
	 * each page load.
	 *
	 * @access public
	 * @param $sessionExpirationSeconds int
	 */
	public function gc($sessionExpirationSeconds) {
		// Prepare the SQL
		$statement = $this->_database->prepare("
			DELETE FROM `session`
			WHERE       `session_last_accessed` < :session_expiration
		");

		// Execute the query
		return $statement->execute(array(
			':session_expiration' => $_SERVER['REQUEST_TIME'] - $sessionExpirationSeconds
		));
	}

	/**
	 * Close the database connection.
	 *
	 * @access public
	 */
	public function close() {
		// Set the connection to null, as adviced in the PHP manual
		// http://php.net/manual/en/pdo.connections.php#example-955
		$this->_database = null;
	}
}