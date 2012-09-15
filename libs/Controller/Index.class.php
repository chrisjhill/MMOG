<?php
class Controller_Index extends Core_Controller
{
	/**
	 * Yes, we want to enable the cache for this file.
	 *
	 * @access public
	 * @var boolean
	 */
	public $enableCache = true;

	/**
	 * Set the life of the cached file to 30 seconds.
	 *
	 * @access public
	 * @var int
	 */
	public $cacheLife = 30;

	/**
	 * The index action
	 *
	 * This action will call the /libs/View/Index/index.phtml.
	 *
	 * @access public
	 */
	public function indexAction() {
		$this->view->addVariable('helloWorld', 'Hello World!');
	}
}