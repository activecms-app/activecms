<?php
use App\Forms\UsersForm;
use Phalcon\Tag;

class UsersController extends ControllerBase
{

	public function initialize()
	{
		parent::initialize();
		$this->tag->setTitle('Gestión de usuarios');
	}

	public function indexAction()
	{
		if( $this->request->has('rol') )
		{
			$rol = Roles::findFirst($this->request->get('rol', 'int'));
			if( $rol )
			{
				$this->view->users = $rol->getUsers(['order' => 'FirstName asc, LastName asc']);
				$this->view->rolSelected = $rol;
			}
		}
		else
		{
			$this->view->users  = Users::find(['order' => 'FirstName asc, LastName asc']);
		}

		$this->view->status = Users::$status;
		$this->view->roles = Roles::find();
		$this->view->total = Users::getTotal(); //Todo pasar si se quiere los eliminados o no
	}

	public function dataAction($id = null)
	{
		if($id)
		{
			$user = \Users::findFirst($id);
			if(!isset($user->Id))
			{
				$this->flash->error('Usuario no encontrado.');
				$this->response->redirect($this->url->get('users'));
			}
		}
		else
		{
			$this->flash->error('Debe especificar usuario.');
			$this->response->redirect($this->url->get('users'));
		}

		$this->view->user = $user;
	}

	public function newAction()
	{
		$this->view->form = new UsersForm(null);
		$this->view->roles = Roles::find(['order' => 'Name asc']);
	}

	public function createAction()
	{
		if (!$this->request->isPost()) {
			$this->dispatcher->forward([
				'controller' => 'users',
				'action'     => 'index',
			]);
			return;
		}
		$form = new UsersForm();
		$user = new Users();

		$data = $this->request->getPost();
		$user->UserStatus = 'active';
		if (!$form->isValid($data, $user)) {
			foreach ($form->getMessages() as $message) {
				$this->flash->error((string)$message);
			}

			$this->dispatcher->forward([
				'controller' => 'users',
				'action'     => 'new',
			]);
			return;
		}

		if (!$user->save()) {
			foreach ($user->getMessages() as $message) {
				$this->flash->error((string)$message);
			}

			$this->dispatcher->forward([
				'controller' => 'users',
				'action'     => 'new',
			]);
			return;
		}
		else
		{
			if( $this->request->has('roles') )
			{
				foreach($_REQUEST["roles"] as $rol)
				{
					$user_rol = new Users_Roles();
					$user_rol->Users_Id = $user->Id;
					$user_rol->Roles_Id = $rol;
					$user_rol->save();
				}
			}
		}

		$form->clear();
		$this->flash->success('Usuario creado');

		$this->dispatcher->forward([
			'action'     => 'data',
			'params'     => [$user->Id]
		]);
	}


	public function editAction($id)
	{
		$user = Users::findFirstById($id);
		if (!$user) {
			$this->flash->error('Usuario no encontrado.');

			$this->dispatcher->forward([
				'controller' => 'users',
				'action'     => 'index',
			]);
			return;
		}

		$this->view->form = new UsersForm($user);
		$this->view->user = $user;
		$this->view->roles = Roles::find(['order' => 'Name asc']);
	}

	public function saveAction()
	{
		if (!$this->request->isPost()) {
			$this->dispatcher->forward([
				'action'     => 'index'
			]);
			return;
		}

		$id = $this->request->getPost('Id', 'int');
		$user = Users::findFirstById($id);
		if (!$user) {
			$this->flash->error('Usuario no encontrado.');
			$this->dispatcher->forward([
				'action'     => 'index'
			]);
			return;
		}

		$data = $this->request->getPost();
		$form = new UsersForm($user);
		if (!$form->isValid($data, $user)) {
			foreach ($form->getMessages() as $message) {
				$this->flash->error((string)$message);
			}
			$this->dispatcher->forward([
				'action'     => 'edit'
			]);
			return;
		}

		if (!$user->save()) {
			foreach ($user->getMessages() as $message) {
				$this->flash->error((string)$message);
			}
			$this->dispatcher->forward([
				'action'     => 'edit'
			]);
			return;
		}
		else
		{
			$this->db->execute('delete from Users_Roles where Users_Id = "'.$user->Id.'"');
			if( $this->request->has('roles') )
			{
				foreach($_REQUEST["roles"] as $rol)
				{
					$user_rol = new Users_Roles();
					$user_rol->Users_Id = $user->Id;
					$user_rol->Roles_Id = $rol;
					$user_rol->save();
				}
			}
		}

		$form->clear();
		$this->flash->success('Usuario actualizado');

		$this->dispatcher->forward([
			'action'     => 'data',
			'params'     => [$user->Id]
		]);
	}

	public function disableAction($id)
	{
		if($id)
		{
			$user = \Users::findFirst($id);
			if(!isset($user->Id))
			{
				$this->flash->error('Usuario no encontrado.');
				$this->response->redirect($this->url->get('users'));
			}
		}
		else
		{
			$this->flash->error('Debe especificar usuario.');
			$this->response->redirect($this->url->get('users'));
		}

		$user->UserStatus = 'disabled';
		$user->save();

		$this->flash->success('Usuario inhabilitado.');
		$this->dispatcher->forward([
			'action'     => 'data',
			'params'     => [$user->Id]
		]);
	}

	public function activeAction($id)
	{
		if($id)
		{
			$user = \Users::findFirst($id);
			if(!isset($user->Id))
			{
				$this->flash->error('Usuario no encontrado.');
				$this->response->redirect($this->url->get('users'));
			}
		}
		else
		{
			$this->flash->error('Debe especificar usuario.');
			$this->response->redirect($this->url->get('users'));
		}

		$user->UserStatus = 'active';
		$user->save();

		$this->flash->success('Usuario activo.');
		$this->dispatcher->forward([
			'action'     => 'data',
			'params'     => [$user->Id]
		]);
	}

	public function deleteAction($id)
	{
		$user = Users::findFirstById($id);
		if (!$user) {
			$this->flash->error('Usuario no encontrado.');

			$this->dispatcher->forward([
				'controller' => 'users',
				'action'     => 'index',
			]);
			return;
		}

		if (!$user->delete()) {
			foreach ($user->getMessages() as $message) {
				$this->flash->error((string)$message);
			}

			$this->dispatcher->forward([
				'controller' => 'users',
				'action'     => 'index',
			]);

			return;
		}

		$this->flash->success('Usuario eliminado');

		$this->dispatcher->forward([
			'controller' => 'users',
			'action'     => 'index',
		]);
	}
}