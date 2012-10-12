<?php
/**
 * Returns a country string, e.g., [1:1:1] Ruler of Country Name.
 *
 * This class provides a standard way to output the country string, allowing
 * you to make each section clickable and also provide a quick "popup"
 * providing the user a bit more information on the planet.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       10/10/2012
 */
class View_Web_Helper_CountryString extends Core_ViewHelper
{
	/**
	 * Builds and returns a standard way to show a country string.
	 *
	 * <code>
	 * array(
	 *     'country'          => Model_Country_Instance,
	 *     'coords_show'      => true|false,
	 *     'coords_clickable' => true|false,
	 *     'name_show'        => true|false,
	 *     'name_show_info'   => true|false,
	 *     'name_clickable'   => true|false,
	 * )
	 * </code>
	 *
	 * @access public
	 * @param  array  $param
	 * @return array
	 */
	public function render($param) {
		// Set some defaults
		$defaults = array(
			'coords_show'      => true,
			'coords_clickable' => true,
			'name_show'        => true,
			'name_show_info'   => true,
			'name_clickable'   => true,
		);

		// Merge these in with the parameters
		// Parameters will take precedence
		$param = array_merge($defaults, $param);

		// The coords
		if ($param['coords_show']) {
			// We want the coords to be shown
			// But do we want them to be clickable
			if ($param['coords_clickable']) {
				$output =
					'[<a href="' .
					self::$view->url(
						array(
							'controller' => 'planet',
							'action'     => 'explore',
							'variables'  => array(
								'x' => $param['country']->getInfo('country_x_coord')
							)
						)
					) . '">' . $param['country']->getInfo('country_x_coord') . '</a>:' .
					'<a href="' .
					self::$view->url(
						array(
							'controller' => 'planet',
							'action'     => 'explore',
							'variables'  => array(
								'x' => $param['country']->getInfo('country_x_coord'),
								'y' => $param['country']->getInfo('country_y_coord')
							)
						)
					) .'">' . $param['country']->getInfo('country_y_coord') . '</a>:' .
					'<a href="' .
					self::$view->url(
						array(
							'controller' => 'planet',
							'action' => 'explore',
							'variables'  => array(
								'x' => $param['country']->getInfo('country_x_coord'),
								'y' => $param['country']->getInfo('country_y_coord'),
								'z' => $param['country']->getInfo('country_z_coord')
							)
						)
					) . '">' . $param['country']->getInfo('country_z_coord') . '</a>]';
			} else {
				$output = '[' . $param['country']->getCoords() . ']';
			}
		}

		// Start the ruler and country name
		if ($param['name_show']) {
			// We want to show the name
			// Do we need to add a space?
			if ($output != '') {
				$output .= ' ';
			}

			// Need to get the language
			$lang = Core_Language::getLanguage();

			// But do we want it to be clickable?
			if ($param['name_clickable']) {
				// Yes, it needs to be clickable
				$output .= '<a href="' .
				self::$view->url(
					array(
						'controller' => 'planet',
						'action' => 'explore',
						'variables'  => array(
							'x' => $param['country']->getInfo('country_x_coord'),
							'y' => $param['country']->getInfo('country_y_coord'),
							'z' => $param['country']->getInfo('country_z_coord')
						)
					)
				) . '">' . $param['country']->getFullCountryName($lang['of']) . '</a>';
			} else {
				// No, it just needs to be displayed
				$output .= $param['country']->getFullCountryName($lang['of']);
			}
		}

		// We have finished
		return $output;
	}
}