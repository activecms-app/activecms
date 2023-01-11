<?php
namespace App\Forms;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Submit;
// Validation
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;

class RecoverForm extends Form
{
	public function initialize()
	{
		// Email
		$email = new Text('email');
		$email->addValidators([
			new Email(['message' => 'El correo electrónico ingresado no es valido'])
		]);
		$this->add($email);
		// Submit Button
		$submit = new Submit('submit', ["value" => "Continuar"]);
		$this->add($submit);
	}
}
