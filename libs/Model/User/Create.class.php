<?php
/**
 * Handles creating a new user and a new country.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       19/09/2012
 *
 * @todo Create a temporary password and email it to the user.
 * @todo Create the guts of the emailExists() method.
 * @todo Create a random string class.
 * @todo Create an Email class.
 */
class Model_User_Create
{
	/**
	 * The email address the user is signing up with.
	 *
	 * @access private
	 * @var string
	 */
	private $_email;

	/**
	 * Sets the email addres the users wishes to use.
	 *
	 * @access public
	 * @param $email string
	 */
	public function __construct($email) {
		$this->_email = $email;
	}

	/**
	 * Tries to register the user.
	 *
	 * @access public
	 * @throws Exception
	 * @return Model_User_Instance
	 */
	public function register() {
		// Is the email valid?
		if (! filter_var($this->_email, FILTER_VALIDATE_EMAIL)) {
			throw new Exception('Please enter a valid email address.');
		}

		// Get the database connection
		$database  = Core_Database::getInstance();

		// See if the email is unique
		// Returns false on not existing and a user ID if it does
		$userId = $this->emailExists($database);

		// Already exist?
		if ($userId) {
			// Email already exists, return the user model
			return new Model_User_Instance($userId);
		}

		// User does not exist
		// Insert into user table
		$statement = $database->prepare("
			INSERT INTO `user`
			(
				`user_email`,
				`user_password`,
				`user_created`
			)
			VALUES
			(
				:user_email,
				:user_password,
				NOW()
			)
		");
		// Execute the query
		$statement->execute(array(
			':user_email'    => $this->_email,
			':user_password' => 'abc'
		));

		// Return a new user model
		return new Model_User_Instance($database->lastInsertId());
	}

	/**
	 * Checks to see if the email address already exists in our user table.
	 * 
	 * @access public
	 * @param $database PDO
	 * @return boolean
	 */
	public function emailExists($database) {
		return false;
	}
}