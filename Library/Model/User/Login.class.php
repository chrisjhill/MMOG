<?php
/**
 * Searches the database for the supplied username and password.
 *
 * We are using the bcrypt blowfish implementation (phpass) and
 * "howto" by Sunny Singh.
 *
 * PHPass: http://www.openwall.com/phpass
 * Sunny Singh: http://sunnyis.me/blog/secure-passwords
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       16/09/2012
 */
class Model_User_Login
{
	/**
	 * The supplied email address.
	 *
	 * @access private
	 * @var string
	 */
	private $_email;

	/**
	 * The supplied password in unhashed form.
	 * 
	 * @access private
	 * @var string
	 */
	private $_password;

	/**
	 * Which round we are on.
	 *
	 * Whilst this is irrelevant to actually logging in, we need to set the game
	 * the user is playing in the session and to make sure the user actually has
	 * a country in this particular game.
	 *
	 * @access private
	 * @var int
	 */
	private $_round;

	/**
	 * Set up the login system
	 *
	 * @access public
	 * @param $email string
	 * @param $password string
	 * @param $round int
	 * @return boolean
	 */
	public function __construct($email, $password, $round) {
		// Set variables
		$this->_email    = $email;
		$this->_password = $password;
		$this->_round    = $round;
	}

	/**
	 * Try and find the user.
	 *
	 * Unlike most login systems we are using PHPass which you can't simply compare
	 * hashes. So we need to get the users account via their email adress and then
	 * check the hashes side-by-side. Annoying, but far more secure.
	 *
	 * @access private
	 * @return Model_User_Instance 
	 * @throws Exception
	 */
	public function login() {
		// Make sure the password is less than 72 characters
		if (strlen($this->_password) <= 4 || strlen($this->_password) >= 72) {
			throw new Exception('login-error-incorrect-format');
		}

		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			SELECT u.user_id, u.user_email, u.user_password,
			       u.user_created, u.user_verified, u.user_last_login, u.user_updated, u.user_removed,
			       c.country_id
			FROM   `user` u
				       LEFT JOIN `country` c ON c.user_id = u.user_id
			WHERE  u.user_email    = :user_email
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':user_email' => $this->_email
		));

		// Did we find the user?
		if ($statement->rowCount() <= 0) {
			// No user found
			throw new Exception('login-error-incorrect-combo');
		}

		// Set the user information
		$user = $statement->fetch();

		// Create a new hashing instance
		$hashAlgorithm = new Core_Password(8, false);

		// Is the supplied password allowed with the database password for this user?
		if (! $hashAlgorithm->CheckPassword($this->_password, $user['user_password'])) {
			// Password mismatch
			throw new Exception('login-error-incorrect-combo');
		}

		// All went well
		return new Model_User_Instance($user['user_id']);
	}
}