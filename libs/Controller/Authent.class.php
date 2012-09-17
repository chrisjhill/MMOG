<?php
class Controller_Authent extends Core_Controller
{
	public function init() {

	}
	public function indexAction() {
		$this->forward('login');
	}
	public function loginAction() {
		$this->view->addVariable('loginMessage', '');
		$this->view->addVariable('title', 'Login to your account');
	}
}