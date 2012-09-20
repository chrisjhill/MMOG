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
	private $_emailSendAfter;

	/**
	 * Send the email to the user with the specified data.
	 * 
	 * @access private
	 * @var string
	 */
	public function send() {
		email_id	email_to	email_from	email_subject	email_body	email_attachment	email_created	email_send_after	email_delivered
	}

	/**
	 * Set the template that we wish to use.
	 * 
	 * Note: This refers to a HTML file as defined the in the PATH_EMAIL variable.
	 * 
	 * @access private
	 * @var string
	 * @return Core_EmailSend
	 * @throws Exception
	 */
	public function setTemplate($template) {
		// Does the template exist?
		if (! file_exists(PATH_EMAIL . $template . '.phtml')) {
			throw new Exception('Unable to locate the email template.');
		}

		// Seems fine
		$this->_emailTemplate = $template;

		// Return this for chainability
		return $this;
	}

	/**
	 * Add a variable to this email.
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
	public function setEmailTo($string) {
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
	public function setEmailAttachment($date) {
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