<?php
/**
 * Returns a notice fully coded in HTML.
 *
 * There are three types of notice that you can use:
 * 
 * <ol>
 *     <li>Success</li>
 *     <li>Error</li>
 *     <li>Notice</li>
 * </ol>
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       17/09/2012
 *
 * @todo Pre-render parameter checking.
 * @todo Allow (auto) closing of notices (bootstrap provides this functionality, I believe).
 */
class View_Helper_Notice
{
	/**
	 * Builds and returns an HTML notice ready to give to the user.
	 *
	 * <code>
	 * array(
	 *     'status'      => 'success|error|notice',
	 *     'title'       => 'The very top level sentence',
	 *     'body'        => 'The main body of the notice',
	 *     'close_allow' => true,
	 *     'close_auto'  => 5
	 *
	 * @access public
	 * @param $param array
	 * @return array
	 */
	public function render($param) {
		return '
			<div class="notice">
				<h3>' . $param['title'] . '</h3>

				<p>' . $param['body'] . '</p>
			</div>';
	}
}