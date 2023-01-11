<?php
use Phalcon\Tag;

class MenusController extends ControllerBase
{

	public function initialize()
	{
		parent::initialize();
		$this->tag->setTitle('Gestión de menús');
	}

	public function indexAction()
	{
		$this->view->menus = \Menus::find(['order' => 'Title']);
	}

	public function dataAction($id = null)
	{
		if(is_null($id) )
		{
			$this->flash->error('Debe especificar un menu.');
			return $this->response->redirect($this->url->get('menus'));
		}
		$menu = \Menus::findFirst($id);
		if(!isset($menu->Id))
		{
			$this->flash->error('Menu no encontrado.');
			return $this->response->redirect($this->url->get('menus'));
		}

		$this->view->menu = $menu;
	}

}
