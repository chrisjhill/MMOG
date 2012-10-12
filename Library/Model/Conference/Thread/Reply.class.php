<?php
/**
 * Reply to a conference thread.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       12/10/2012
 *
 * @todo thread_updated needs to be a timestamp on creation automatically.
 * @todo We should create a new post using Model_Conference_Post_Create
 */
class Model_Conference_Thread_Reply
{
	/**
	 * Create a new conference thread.
	 * 
	 * @param  Model_Country_Instance $country
	 * @return int                    The thread ID.
	 * @param  string                 $message
	 * @throws Exception              If the message is empty.
	 */
	public function reply($country, $threadId, $message) {
		// Has the user populated the subject and body?
		if (empty($message)) {
			throw new Exception('conference-error-empty');
		}

		// Get the database connection
		$database  = Core_Database::getInstance();
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

		// We can now update the thread as to when it was last updated
		$statement = $database->prepare("
			UPDATE `conference_thread` t
			SET    t.thread_updated = NOW()
			WHERE  t.round_id  = :round_id
			       AND
			       t.thread_id = :thread_id
			LIMIT  1
		");

		// Execute the query
		$statement->execute(array(
			':round_id'  => GAME_ROUND,
			':thread_id' => $threadId
		));
	}
}