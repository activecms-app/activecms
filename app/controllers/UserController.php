<?php
use App\Forms\LoginForm;
use App\Forms\RecoverForm;
use App\Forms\RecoverCodeForm;
use Phalcon\Mvc\Controller;
use App\Library\Notifications;

class UserController extends Controller
{
	public $loginForm;
	public $usersModel;

	// Login Page View
	public function loginAction()
	{
		$this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);  //Disable layout
		$this->tag->setTitle('ActiveCMS :: Login');
		$this->view->form = new LoginForm();
		$this->view->url = $this->url;
	}

	/**
	 * Login Action
	 * @method: POST
	 * @param: email
	 * @param: password
	 */
	public function loginSubmitAction()
	{
		$this->loginForm = new LoginForm();

		// check request
		if (!$this->request->isPost()) {
			return $this->response->redirect('user/login');
		}

		$this->loginForm->bind($_POST, $this->usersModel);
		// check form validation
		if (!$this->loginForm->isValid()) {
			foreach ($this->loginForm->getMessages() as $message) {
				$this->flash->error($message);
			}
			$this->dispatcher->forward([
				'controller' => $this->router->getControllerName(),
				'action'     => 'login',
			]);
			return;
		}

		// login with database
		$username = $this->request->getPost('username');
		$password = $this->request->getPost('password');
		$remember = $this->request->getPost('remember', 'bool', false);

		$user = Users::findFirstByUsername($username);

		if ($user) {
//			if ($this->security->checkHash($password, $user->Password))
			if ( $password == $user->Pass )
			{
				if( $session = $user->createSession($_COOKIE['WASESSION'], $this->request->getClientAddress(), $remember) == null )
				{
					$this->flash->error("No se pudo iniciar la sesión");
					return $this->response->redirect('user/login');
				}
				return $this->response->redirect('');
			}
		} else {
			$this->security->hash(rand());
		}
		// The validation has failed
		$this->flash->error("Datos de acceso invalidos");
		$this->dispatcher->forward(['action' => 'login']);
		return;
	}
	
	/**
	 * User Logout
	 */
	public function logoutAction()
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
			$session->finish();
		}
		$this->session->destroy();
		return $this->response->redirect('user/login');
	}

	public function recoverAction()
	{
		$this->tag->setTitle('ActiveCMS :: Recuperar');
		$this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);  //Disable layout
		$this->view->form = new RecoverForm();
		$this->view->url = $this->url;
	}

	public function recoverSubmitAction()
	{
		$this->recoverForm = new RecoverForm();

		// check request
		if (!$this->request->isPost()) {
			return $this->response->redirect('user/recover');
		}

		// check form validation
		if (!$this->recoverForm->isValid($_POST)) {
			foreach ($this->recoverForm->getMessages() as $message) {
				$this->flash->error($message);
			}
			$this->dispatcher->forward(['action' => 'recover']);
			return;
		}

		$email = $this->request->getPost('email');
		$recovercode = Users::recoverByEmail($email);

		if( $recovercode ) {
			//Send validation code by e-mail
			if( Notifications::recoverCode($email, $recovercode) )
			{
				$this->dispatcher->forward(['action' => 'code']);
				return;
			}
		}

		// The validation has failed
		$this->flash->error("No se encontró usuario activo para el correo ingresado");
		$this->dispatcher->forward(['action' => 'recover']);
		return;
	}

	function codeAction()
	{
		$this->tag->setTitle('ActiveCMS :: Recuperar - Ingreso código');
		$this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);  //Disable layout
		$this->view->form = new RecoverCodeForm();
		$this->view->url = $this->url;
	}

	function codeSubmitAction()
	{
		$this->recoverCodeForm = new RecoverCodeForm();

		// check request
		if (!$this->request->isPost()) {
			return $this->response->redirect('user/recover');
		}

		// check form validation
		if (!$this->recoverCodeForm->isValid($_POST)) {
			foreach ($this->recoverCodeForm->getMessages() as $message) {
				$this->flash->error($message);
			}
			$this->dispatcher->forward(['action' => 'code']);
			return;
		}

		$code = $this->request->getPost('code');
		$pass = $this->request->getPost('pass');
		$user = Users::findFirstByRecoverCode($code);

		if( $user ) {
			if( $user->updatePass($pass) )
			{
				$this->flashSession->success("Contraseña cambiada");
				return $this->response->redirect('user/login');
			}
		}

		// The validation has failed
		$this->flash->error("No se encontró usuario activo para el correo ingresado");
		$this->dispatcher->forward(['action' => 'recover']);
		return;
	}
}

