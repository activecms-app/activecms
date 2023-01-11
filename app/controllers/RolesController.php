<?php
use App\Forms\RolesForm;

class RolesController extends ControllerBase
{

	public function initialize()
	{
		parent::initialize();
		$this->tag->setTitle('Gestión de roles');
	}

	public function indexAction()
	{
		$this->view->roles = Roles::find();
	}

	public function dataAction($id = null)
	{
		if($id)
		{
			$rol = \Roles::findFirst($id);
			if(!isset($rol->Id))
			{
				$this->flash->error('Rol no encontrado.');
				$this->response->redirect($this->url->get('roles'));
			}
		}
		else
		{
			$this->flash->error('Debe especificar rol.');
			$this->response->redirect($this->url->get('roles'));
		}

		$this->view->rol = $rol;
	}

	public function newAction()
	{
		$this->view->form = new RolesForm(null);
		$this->view->permissions = Permissions::find(['order' => 'Tittle asc']);
		$this->view->categories = PermissionsCategories::find(['order' => 'Title asc']);
	}

	public function createAction()
	{
		if (!$this->request->isPost()) {
			$this->dispatcher->forward([
				'controller' => 'roles',
				'action'     => 'index',
			]);
			return;
		}
		$form = new RolesForm();
		$rol = new Roles();

		$data = $this->request->getPost();
		if (!$form->isValid($data, $rol)) {
			foreach ($form->getMessages() as $message) {
				$this->flash->error((string)$message);
			}

			$this->dispatcher->forward([
				'controller' => 'roles',
				'action'     => 'new',
			]);
			return;
		}

		if (!$rol->save()) {
			foreach ($rol->getMessages() as $message) {
				$this->flash->error((string)$message);
			}

			$this->dispatcher->forward([
				'controller' => 'roles',
				'action'     => 'new',
			]);
			return;
		}
		else
		{
			if( $this->request->has('permissions') )
			{
				foreach($_REQUEST["permissions"] as $permission)
				{
					$rol_permission = new Roles_Permissions();
					$rol_permission->Roles_Id = $rol->Id;
					$rol_permission->Permissions_Id = $permission;
					$rol_permission->save();
				}
			}
		}

		$form->clear();
		$this->flash->success('Rol creado');

		$this->dispatcher->forward([
			'action' => 'data',
			'params' => [$rol->Id]
		]);
	}


	public function editAction($id)
	{
		$rol = Roles::findFirstById($id);
		if (!$rol) {
			$this->flash->error('Rol no encontrado.');

			$this->dispatcher->forward([
				'controller' => 'roles',
				'action'     => 'index',
			]);
			return;
		}

		$this->view->form = new RolesForm($rol);
		$this->view->rol = $rol;
		$this->view->categories = PermissionsCategories::find(['order' => 'Title asc']);
		$this->view->permissions = Permissions::find(['order' => 'Tittle asc']);
	}

	public function saveAction(): void
	{
		if (!$this->request->isPost()) {
			$this->dispatcher->forward([
				'controller' => 'roles',
				'action'     => 'index',
			]);
			return;
		}

		$id = $this->request->getPost('Id', 'int');
		$rol = Roles::findFirstById($id);
		if (!$rol) {
			$this->flash->error('Rol no encontrado.');
			$this->dispatcher->forward([
				'controller' => 'roles',
				'action'     => 'index',
			]);
			return;
		}

		$data = $this->request->getPost();
		$form = new RolesForm();
		if (!$form->isValid($data, $rol)) {
			foreach ($form->getMessages() as $message) {
				$this->flash->error((string)$message);
			}
			$this->dispatcher->forward([
				'controller' => 'roles',
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
			'params' => [$rol->Id]
		]);

	}

	public function deleteAction($id)
	{
		$rol = Roles::findFirstById($id);
		if (!$rol) {
			$this->flash->error('Rol no encontrado.');

			$this->dispatcher->forward([
				'controller' => 'roles',
				'action'     => 'index',
			]);
			return;
		}

		if (!$rol->delete()) {
			foreach ($rol->getMessages() as $message) {
				$this->flash->error((string)$message);
			}

			$this->dispatcher->forward([
				'controller' => 'roles',
				'action'     => 'index',
			]);

			return;
		}

		$this->flash->success('Rol eliminado');

		$this->dispatcher->forward([
			'controller' => 'roles',
			'action'     => 'index',
		]);
	}

}
