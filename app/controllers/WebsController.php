<?php
use App\Forms\WebsForm;

class WebsController extends ControllerBase
{

	public function initialize()
	{
		parent::initialize();
		$this->tag->setTitle('Gestión de webs');
	}

	public function indexAction()
	{
		$this->view->status = Webs::$status;
		$this->view->webs   = Webs::find(["order" => "Name asc"]);
	}

	public function dataAction($id = null)
	{
		if($id)
		{
			$web = Webs::findFirst($id);
			if(!isset($web->Id))
			{
				$this->flash->error('Web no encontrado.');
				$this->response->redirect($this->url->get('webs'));
			}
		}
		else
		{
			$this->flash->error('Debe especificar web.');
			$this->response->redirect($this->url->get('webs'));
		}

		$this->view->web    = $web;
		$this->view->status = Webs::$status;
	}

	public function newAction()
	{
		$this->view->status = Webs::$status;
		$this->view->form   = new WebsForm(null);
		$this->view->themes = Themes::find(['order' => 'Title asc']);
	}

	public function createAction()
	{
		if (!$this->request->isPost()) {
			$this->dispatcher->forward([
				'controller' => 'webs',
				'action'     => 'index',
			]);
			return;
		}
		$form = new WebsForm();
		$web  = new Webs();

		$data = $this->request->getPost();
		if (!$form->isValid($data, $web)) {
			foreach ($form->getMessages() as $message) {
				$this->flash->error((string)$message);
			}

			$this->dispatcher->forward([
				'controller' => 'webs',
				'action'     => 'new',
			]);
			return;
		}
		if(isset($_REQUEST['Themes_Id']) && $_REQUEST['Themes_Id'] != "")
		{
			$web->Themes_Id = $_REQUEST['Themes_Id'];
		}
		else
		{
			$web->Themes_Id = null;
		}
		if (!$web->save()) {
			foreach ($rol->getMessages() as $message) {
				$this->flash->error((string)$message);
			}

			$this->dispatcher->forward([
				'controller' => 'webs',
				'action'     => 'new',
			]);
			return;
		}
		else
		{
			$form->clear();
			$this->flash->success('Web creado');
		}

		$this->dispatcher->forward([
			'action' => 'data',
			'params' => [$web->Id]
		]);
	}

	public function editAction($id)
	{
		$web = Webs::findFirstById($id);
		if (!$web) {
			$this->flash->error('Web no encontrado.');

			$this->dispatcher->forward([
				'controller' => 'webs',
				'action'     => 'index',
			]);
			return;
		}

		$this->view->web    = $web;
		$this->view->form   = new WebsForm($web);
		$this->view->themes = Themes::find(['order' => 'Title asc']);
	}

	public function saveAction(): void
	{
		if (!$this->request->isPost()) {
			$this->dispatcher->forward([
				'controller' => 'webs',
				'action'     => 'index',
			]);
			return;
		}

		$id = $this->request->getPost('Id', 'int');
		$web = Webs::findFirstById($id);
		if (!$web) {
			$this->flash->error('Web no encontrado.');
			$this->dispatcher->forward([
				'controller' => 'webs',
				'action'     => 'index',
			]);
			return;
		}

		$data = $this->request->getPost();
		$form = new WebsForm();
		if (!$form->isValid($data, $web)) {
			foreach ($form->getMessages() as $message) {
				$this->flash->error((string)$message);
			}
			$this->dispatcher->forward([
				'controller' => 'webs',
				'action'     => 'edit',
			]);
			return;
		}
		if(isset($_REQUEST['Themes_Id']) && $_REQUEST['Themes_Id'] != "")
		{
			$web->Themes_Id = $_REQUEST['Themes_Id'];
		}
		else
		{
			$web->Themes_Id = null;
		}
		if (!$web->save()) {
			foreach ($rol->getMessages() as $message) {
				$this->flash->error((string)$message);
			}

			$this->dispatcher->forward([
				'controller' => 'webs',
				'action'     => 'edit',
			]);
			return;
		}
		else
		{
			$form->clear();
			$this->flash->success('Web actualizado');
		}

		$this->dispatcher->forward([
			'action' => 'data',
			'params' => [$web->Id]
		]);
	}

	public function deleteAction($id)
	{
		$web = Webs::findFirstById($id);
		if (!$web) {
			$this->flash->error('Web no encontrado.');

			$this->dispatcher->forward([
				'controller' => 'webs',
				'action'     => 'index',
			]);
			return;
		}

		if (!$web->delete()) {
			foreach ($rol->getMessages() as $message) {
				$this->flash->error((string)$message);
			}

			$this->dispatcher->forward([
				'controller' => 'webs',
				'action'     => 'index',
			]);

			return;
		}

		$this->flash->success('Web eliminado');

		$this->dispatcher->forward([
			'controller' => 'webs',
			'action'     => 'index',
		]);
	}
}