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
	 * Tries to create the user.
	 *
	 * @access public
	 * @throws Exception
	 * @return Model_User_Instance
	 */
	public function create() {
		// Is the email valid?
		if (! filter_var($this->_email, FILTER_VALIDATE_EMAIL)) {
			throw new Exception('register-error-invalid-email');
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
		// Create a new hashing instance
		$hashAlgorithm = new Core_Password(8, false);

		// Create the users password
		$password = substr(md5($_SERVER['REQUEST_TIME'] . mt_rand(0, 999999)), 0, 8);

		// And create a hashed password
		$passwordHash = $hashAlgorithm->HashPassword($password);

		// Insert into user table
		$statement = $database->prepare("
			INSERT INTO `user`
			(
				`user_email`,
				`user_password`
			)
			VALUES
			(
				:user_email,
				:user_password
			)
		");

		// Execute the query
		$statement->execute(array(
			':user_email'    => $this->_email,
			':user_password' => $passwordHash
		));

		// Get the user ID
		$userId = $database->lastInsertId();

		// Send the user an email
		$email = new Core_EmailSend();
		$email->setTemplate('welcome')
		      ->addVariable('email',    $this->_email)
		      ->addVariable('password', $password)
		      ->setEmailTo($userId)
		      ->setEmailFrom(0)
		      ->setEmailSubject('Welcome to ' . GAME_NAME)
		      ->send();

		// Return a new user model
		return new Model_User_Instance($userId);
	}

	/**
	 * Checks to see if the email address already exists in our user table.
	 * 
	 * @access public
	 * @param $database PDO
	 * @return boolean
	 */
	public function emailExists($database) {
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT u.user_id
			FROM   `user` u
			WHERE  u.user_email = :user_email
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':user_email' => $this->_email,
		));

		// Did we find anyone?
		if ($statement->rowCount() >= 1) {
			// Yes, this email already exists
			$data = $statement->fetch();
			return $data['user_id'];
		}

		// No, this is a new email address
		return false;
	}
}