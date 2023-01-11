<?php
namespace App\Forms;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;

class UsersForm extends Form
{
	public function initialize($user = null, array $options = [])
	{
		$this->add(new Hidden('Id'));

		$commonFilters = [
			'striptags',
			'string',
		];

		$name = new Text('FirstName');
		$name->setFilters($commonFilters);
		$name->addValidators([
			new PresenceOf(['message' => 'Nombre es requerido']),
		]);
		$this->add($name);

		$last_name = new Text('LastName');
		$last_name->setFilters($commonFilters);
		$last_name->addValidators([
			new PresenceOf(['message' => 'Apellidos es requerido']),
		]);
		$this->add($last_name);

		$user_name = new Text('Username');
		$user_name->setFilters($commonFilters);
		$user_name->addValidators([
			new PresenceOf(['message' => 'Nombre de usuario es requerido']),
		]);
		$this->add($user_name);

		$email = new Text('Email');
		$email->setFilters('email');
		$email->addValidators([
			new PresenceOf(['message' => 'E-mail es requerido']),
			new Email(['message' => 'E-mail no es valido'])
		]);
		$this->add($email);

		$password = new Password('Pass');
		if( is_null($user) )
		{
			$password->addValidators([
				new PresenceOf(['message' => 'La contraseña es requerida']),
			]);
		}
		$this->add($password);

/*		$repeatPassword = new Password('repeatPassword');
		$repeatPassword->setLabel('Repetir Contraseña');
		$repeatPassword->addValidators([
			new PresenceOf(['message' => 'Repetición de contraseña is required']),
		]);
		$this->add($repeatPassword);
*/

	}
}