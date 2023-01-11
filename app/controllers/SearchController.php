<?php
use Phalcon\Mvc\Controller;

class SearchController extends ControllerBase
{

	public function indexAction()
	{
		if( $this->request->has('q') )
		{
			$text = $this->request->get('q');
			$type = 0;
			$user = 0;
			$deep = 0;
			$page = $this->request->get('page', 'int', 1);
			$item_per_page = 10;
			$this->view->text = $text;
			$this->view->paginator = Objects::searchObjects($text, null, $type, $user, $deep, $page, $item_per_page);
		}

	}

}

