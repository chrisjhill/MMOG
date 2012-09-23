<?php
/**
 * Add an email to the email database queue.
 *
 * You can specify a date in the future to send the email out and can also
 * attach an file to the email.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       20/09/2012
 */
class Core_EmailSend
{
	/**
	 * The template that we want to use.
	 * 
	 * @access private
	 * @var string
	 */
	private $_emailTemplate;

	/**
	 * Variables that we want to replae in the template.
	 * 
	 * @access private
	 * @var array
	 */
	private $_emailVariables = array();

	/**
	 * The ID of the user we are sending the email to.
	 * 
	 * @access private
	 * @var string
	 */
	private $_emailTo;

	/**
	 * The ID of the user we are sending the email from.
	 * 
	 * @access private
	 * @var string
	 */
	private $_emailFrom;

	/**
	 * The subject of the email.
	 *
	 * We will use variable replacements on this field.
	 * 
	 * @access private
	 * @var string
	 */
	private $_emailSubject;

	/**
	 * The location of a file we wish to attach to this email.
	 * 
	 * @access private
	 * @var string
	 */
	private $_emailAttachment;

	/**
	 * Send the email after a certain date.
	 * 
	 * @access private
	 * @var string
	 */
	private $_emailSendAfter = 1;

	/**
	 * Send the email to the user with the specified data.
	 * 
	 * @access private
	 * @var string
	 * @return mixed int on success, boolean false on error
	 */
	public function send() {
		// Fetch the template and set a local copy of the subject
		$emailBody    = file_get_contents(PATH_EMAIL . $this->_emailTemplate . '.phtml');
		$emailSubject = $this->_emailSubject;

		// Perform variable replacements
		foreach ($this->_emailVariables as $variable => $value) {
			// Replace from body and subject
			$emailBody    = str_replace('{' . $variable . '}', $value, $emailBody);
			$emailSubject = str_replace('{' . $variable . '}', $value, $emailSubject);
		}

		// And insert into the queue
		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			INSERT INTO `email`
			(
				`email_to`,
				`email_from`,
				`email_subject`,
				`email_body`,
				`email_attachment`,
				`email_send_after`
			)
			VALUES
			(
				:email_to,
				:email_from,
				:email_subject,
				:email_body,
				:email_attachment,
				:email_send_after
			)
		");

		// Execute the query
		return $statement->execute(array(
			':email_to'         => $this->_emailTo,
			':email_from'       => $this->_emailFrom,
			':email_subject'    => $emailSubject,
			':email_body'       => $emailBody,
			':email_attachment' => $this->_emailAttachment,
			':email_send_after' => $this->_emailSendAfter
		));
	}

	/**
	 * Set the template that we wish to use.
	 * 
	 * Note: This refers to a HTML file as defined the in the PATH_EMAIL variable.
	 * 
	 * @access private
	 * @param $template string
	 * @param $language string
	 * @return Core_EmailSend
	 * @throws Exception
	 */
	public function setTemplate($template, $language = 'en') {
		// Does the template exist?
		if (! file_exists(PATH_EMAIL . $language . DS . $template . '.phtml')) {
			throw new Exception('Unable to locate the email template.');
		}

		// Seems fine
		$this->_emailTemplate = $language . DS . $template;

		// Return this for chainability
		return $this;
	}

	/**
	 * Add a variable to this email.
	 *
	 * Variables have a opening and closing bracket surrounding them {variable}
	 * in the template file, but you do you not need to pass them in, this
	 * class handles that.
	 *
	 * Note: This function adds one variable. If you want to add multiple at once
	 * then use the setVariables() method.
	 *
	 * @access public
	 * @param $variable string
	 * @return Core_EmailSend
	 * @param $value string
	 */
	public function addVariable($variable, $value) {
		// Set the individual variable
		$this->_emailVariables[$variable] = $value;

		// Return this for chainability
		return $this;
	}

	/**
	 * Adds multiple variables at once to the email variable list.
	 *
	 *
	 * <code>
	 * array(
	 *     'foo' => 'bar',
	 *     'bar' => 'foobar'
	 * )
	 * </code>
	 *
	 * Note: If you want to pass in just one variable then addVariable() is better.
	 *
	 * @access public
	 * @param $variables array
	 * @return Core_EmailSend
	 */
	public function setVariables($variables) {
		// Mass set variables
		$this->_emailVariables = $variables;

		// Return this for chainability
		return $this;
	}

	/**
	 * Set the ID of the user we wish to send the email to.
	 *
	 * @access public
	 * @param $userId int
	 * @return Core_EmailSend
	 */
	public function setEmailTo($userId) {
		// Set the user ID this emaiil is going to
		$this->_emailTo = $userId;

		// Return this for chainability
		return $this;
	}

	/**
	 * Set the ID of the user this email is coming from.
	 *
	 * @access public
	 * @param $userId int
	 * @return Core_EmailSend
	 */
	public function setEmailFrom($userId) {
		// Set the user ID this emaiil is coming from
		$this->_emailFrom = $userId;

		// Return this for chainability
		return $this;
	}

	/**
	 * Set the subject of the email.
	 *
	 * @access public
	 * @param $subject string
	 * @return Core_EmailSend
	 */
	public function setEmailSubject($subject) {
		// Set the user ID this emaiil is going to
		$this->_emailSubject = $subject;

		// Return this for chainability
		return $this;
	}

	/**
	 * Set the email attachment this email contains.
	 *
	 * Note: This function expects the email attachment to be a full location
	 * to the file you wish to attach.
	 *
	 * @access public
	 * @param $file string
	 * @return Core_EmailSend
	 * @throws Exception
	 */
	public function setEmailAttachment($file) {
		// Does the attachment actually exist?
		if (! file_exists($file)) {
			throw new Exception('Attachment could not be located.');
		}

		// Set the user ID this emaiil is going to
		$this->_emailAttachment = $file;

		// Return this for chainability
		return $this;
	}

	/**
	 * A date when the email should be sent out, and not before.
	 *
	 * @access public
	 * @param $date int Unix timestamp
	 * @return Core_EmailSend
	 * @throws Exception
	 */
	public function setEmailSendAfter($date) {
		// Is the date in the past?
		if ($date < $_SERVER['REQUEST_TIME']) {
			throw new Exception('The date to send the email after is invalid.');
		}

		// Set the user ID this emaiil is going to
		$this->_emailSendAfter = $date;

		// Return this for chainability
		return $this;
	}
}