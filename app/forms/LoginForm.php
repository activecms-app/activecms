<?php
namespace App\Forms;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Submit;
// Validation
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Email;

class LoginForm extends Form
{
	public function initialize()
	{
		// Username
		$username = new Text('username', [
			"placeholder" => "Username"
		]);
		// form email field validation
		$username->addValidators([
			new PresenceOf(['message' => 'El nombre de usuario es requerido']),
		]);
		$this->add($username);
		// Password
		$password = new Password('password', [
			"class" => "form-control",
			"placeholder" => "Clave"
		]);
		// password field validation
		$password->addValidators([
			new PresenceOf(['message' => 'La contraseña es requerida'])
		]);
		$this->add($password);
		//Remember
		$this->add(
			(new Check('remember', ['value' => 1]))->setDefault('1')->addFilter('bool')
		);
		// Submit Button
		$submit = new Submit('submit', [
			"value" => "Login",
			"class" => "btn btn-primary",
		]);
		$this->add($submit);
	}
}
