<?php
/**
 * Returns a notice fully coded in HTML.
 *
 * There are four types of notice that you can use:
 * 
 * <ol>
 *     <li>Success</li>
 *     <li>Error</li>
 *     <li>Notice</li>
 *     <li>Info</li>
 * </ol>
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       17/09/2012
 */
class View_Web_Helper_Notice
{
	/**
	 * Builds and returns an HTML notice ready to give to the user.
	 *
	 * <code>
	 * array(
	 *     'status'      => 'success|error|notice|info',
	 *     'title'       => 'The very top level sentence',
	 *     'body'        => 'The main body of the notice',
	 *     'close_allow' => true
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
			'close_allow' => true
		);

		// Merge these in with the parameters
		// Parameters will take precedence
		$param = array_merge($defaults, $param);

		// Set the classes
		switch ($param['status']) {
			case 'success' : $param['status'] = 'alert alert-success'; break;
			case 'error'   : $param['status'] = 'alert alert-error';   break;
			case 'info'    : $param['status'] = 'alert alert-info';    break;
			default        : $param['status'] = 'alert';               break;
		}

		// And return
		return '
			<div class="' . $param['status'] . '">
				' . ($param['close_allow'] ? '<button type="button" class="close" data-dismiss="alert">×</button>' : '') . '

				<h4>' . $param['title'] . '</h4>

				' . $param['body'] . '
			</div>';
	}
}