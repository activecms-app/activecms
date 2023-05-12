<?php
use App\Forms\MenusForm;
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

	public function newAction()
	{
		$this->view->form = new MenusForm(null);
	}

	public function createAction()
	{
		if (!$this->request->isPost()) {
			$this->dispatcher->forward([
				'controller' => 'menus',
				'action'     => 'index',
			]);
			return;
		}
		$form = new MenusForm();
		$menu = new Menus();

		$data = $this->request->getPost();
		if (!$form->isValid($data, $menu)) {
			foreach ($form->getMessages() as $message) {
				$this->flash->error((string)$message);
			}

			$this->dispatcher->forward([
				'controller' => 'menus',
				'action'     => 'new',
			]);
			return;
		}

		if (!$menu->save()) {
			foreach ($menu->getMessages() as $message) {
				$this->flash->error((string)$message);
			}

			$this->dispatcher->forward([
				'controller' => 'menus',
				'action'     => 'new',
			]);
			return;
		}
		$form->clear();
		$this->flash->success('Menú creado');

		$this->dispatcher->forward([
			'action'     => 'data',
			'params'     => [$menu->Id]
		]);
	}

	public function dataItemAction()
	{

	}

	public function saveItemAction()
	{
		if( $this->request->has('Num') && $this->request->get('Num') > 0 ) {
			$menuItem = MenusItems::findFirst([
				'Menus_Id = :menu: and Num = :num:',
				'bind' => [
					'menu' => $this->request->get('Menus_Id'),
					'num' => $this->request->get('Num')
				]
			]);
			if( empty($menuItem->Num) ) {
				return $this->response->setStatusCode(404, 'Item not found');
			}
			$oldParent = $menuItem->MenusItems_Num;
			$menuItem->assign($this->request->get());
		} else {
			$menuItem = new MenusItems();
			$menuItem->assign($this->request->get());
			$menuItem->Num = $menuItem->menu->maxNum() + 1;
			$menuItem->Position = $menuItem->countChilds() + 1;
			$oldParent = 0;
		}

		if( $menuItem->save() ) {
			$menuItem->normalizePosition($oldParent);
			$result = [
				'num' => $menuItem->Num,
				'title' => $menuItem->Title,
				'parent' => $menuItem->MenusItems_Num,
				'previous' => $menuItem->getPrevious()->Num
			];
		} else {
			$result = [
				'error' => $menuItem->getMessages()
			];
		}

		return $this->response->setJsonContent($result);
	}

}
