<?php
/**
 * Handles any time/date related conversions or outputs.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       30/09/2012
 */
class Core_Date
{
	/**
	 * Takes a timestamp or MySQL datetime and returns the format specified.
	 *
	 * @access public
	 * @param $format string
	 * @param $time string
	 * @return string
	 * @static
	 */
	public static function format($format, $time) {
		// Is this a unix timestamp or a MySQL datetime?
		if (strpos($time, ':') !== false) {
			// Seems to be a MySQL datetime
			// Convert to a unix timestamp
			$time = Core_Date::datetimeToTimestamp($time);
		}

		// And return
		return date($format, $time);
	}

	/**
	 * Returns a word representation of how long ago something was.
	 *
	 * @access public
	 * @param $time string
	 * @return string
	 * @static
	 */
	public static function timeAgo($time) {
		// Is this a unix timestamp or a MySQL datetime?
		if (strpos($time, ':') !== false) {
			// Seems to be a MySQL datetime
			// Convert to a unix timestamp
			$time = Core_Date::datetimeToTimestamp($time);
		}

		// Get the language
		$lang = Core_Language::getLanguage();

		// Work out the difference in seconds this happened
		$difference = $_SERVER['REQUEST_TIME'] - $time;

		// Less than one minute ago?
		if ($difference < 60) {
			return 'Just now';
		}

		// Less than one hour ago?
		else if ($difference < 3600) {
			return floor($difference / 60) . ' ' . $lang['date-minutes-ago'];
		}

		// Less than one day ago?
		else if ($difference < 86400) {
			return floor($difference / 3600) . ' ' . $lang['date-hours-ago'];
		}

		// Less than one week?
		else if ($difference < 604800) {
			return floor($difference / 86400) . ' ' . $lang['date-days-ago'];
		}

		// Less than one month ago?
		else if ($difference < 2419300) {
			return floor($difference / 604800) . ' ' . $lang['date-weeks-ago'];
		}

		// Less than one year ago?
		else if ($difference < 31536000) {
			return floor($difference / 2419300) . ' ' . $lang['date-months-ago'];
		}

		// Looks like it was years ago
		else {
			return floor($difference / 31536000) . ' ' . $lang['date-years-ago'];
		}
	}

	/**
	 * Convert a MySQL datetime field to a unix timestamp.
	 *
	 * @access public
	 * @param $datetime string
	 * @return int
	 * @static
	 */
	public static function datetimeToTimestamp($datetime) {
		// Separate the date and time
		list($date, $time) = explode(' ', $datetime);
		// Separate the year, month, and day
		list($year, $month, $day) = explode('-', $date);
		// Separate the hour, minute, and second
		list($hour, $minute, $second) = explode(':', $time);

		// And return the timestamp
		return mktime($hour, $minute, $second, $month, $day, $year);
	}
}