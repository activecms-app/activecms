<?php
namespace App\Forms;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
// Validation
use Phalcon\Validation\Validator\PresenceOf;

class RecoverCodeForm extends Form
{
	public function initialize()
	{
		// Code
		$code = new Text('code');
		$code->addValidators([
			new PresenceOf(['message' => 'Debe ingresar el código de recuperación'])
		]);
		$this->add($code);
		// Password
		$password = new Password('pass');
		$password->addValidators([
			new PresenceOf(['message' => 'La contraseña es requerida']),
		]);
		$this->add($password);
		// Submit Button
		$submit = new Submit('submit', ["value" => "Cambiar"]);
		$this->add($submit);
	}
}
