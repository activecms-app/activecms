<?php

class IndexController extends ControllerBase
{

	public function indexAction()
	{
		$this->view->recent_objects = Objects::findRecent($this->user);
	}

}
