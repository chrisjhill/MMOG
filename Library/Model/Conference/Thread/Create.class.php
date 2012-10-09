<?php
/**
 * Create a new conference thread.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       08/10/2012
 *
 * @todo thread_updated needs to be a timestamp on creation automatically.
 */
class Model_Conference_Thread_Create
{
	/**
	 * Create a new conference thread.
	 * 
	 * @param $country Model_Country_Instance
	 * @param $planet Model_Planet_Instance
	 * @param $subject string
	 * @param $message string
	 * @return boolean
	 * @throws Exception
	 */
	public function create($country, $planet, $subject, $message) {
		// Has the user populated the subject and body?
		if (empty($subject) || empty($message)) {
			throw new Exception('conference-error-empty');
		}

		// Get the database connection
		$database  = Core_Database::getInstance();
		$statement = $database->prepare("
			INSERT INTO `conference_thread`
				(
					`round_id`,
					`planet_id`,
					`thread_subject`,
					`thread_updated`
				)
			VALUES
				(
					:round_id,
					:planet_id,
					:thread_subject,
					NOW()
				)
		");

		// Execute the query
		$statement->execute(array(
			':round_id'       => GAME_ROUND,
			':planet_id'      => $planet->getInfo('planet_id'),
			':thread_subject' => $subject
		));

		// Get the thread ID that we just created
		$threadId = $database->lastInsertId();

		// And insert the post
		$statement = $database->prepare("
			INSERT INTO `conference_post`
				(
					`thread_id`,
					`country_id`,
					`post_message`
				)
			VALUES
				(
					:thread_id,
					:country_id,
					:post_message
				)
		");

		// Execute the query
		$statement->execute(array(
			':thread_id'   => $threadId,
			':country_id'  => $country->getInfo('country_id'),
			'post_message' => $message
		));

		// All went well
		return true;
	}
}