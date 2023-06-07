<?php

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
	public $user = null;
	public $web = null;

	public function beforeExecuteRoute(Phalcon\Mvc\Dispatcher $dispatcher)
	{
		if( !$this->isLoggedIn() ) {
			$this->response->redirect('user/login');
			return false;
		}
	}

	public function initialize()
	{
		$this->view->user = $this->user;
		$this->view->url = $this->url;
		$this->view->webs = Webs::find();
		if( $this->dispatcher->getParam(0) > 0 )
		{
			$this->web = Webs::findFirstByObjects_Id($this->dispatcher->getParam(0));
		}
		if( is_null($this->web) && count($this->view->webs) ) {
			$this->web = $this->view->webs[0];
		}
		$this->view->web = $this->web;

		$this->tag->prependTitle('Active - ');
	}

	public function onConstruct()
	{
		$this->view->url = $this->url;
//		 if (!$this->isLoggedIn()) {
//			$this->response->redirect('user/login');
//		 }
	}

	public function authorized()
	{
		if (!$this->isLoggedIn()) {
			return $this->response->redirect('user/login');
		}
	}

	public function isLoggedIn()
	{
		if( isset($_COOKIE['WASESSION']) && !empty($_COOKIE['WASESSION']) )
		{
			$session = UsersSessions::findFirst([
				'AccessKey = :key: and Status = :status:',
				'bind' => [
					'key' => $_COOKIE['WASESSION'],
					'status' => 'active'
				]
			]);
		}
		if( $session && $session->active() )
		{
			$this->user = $session->User;
			return true;
		}
		return false;
	}
}
