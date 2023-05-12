<?php
namespace App\Forms;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;

class MenusForm extends Form
{
	public function initialize($menu = null, array $options = [])
	{
		$this->add(new Hidden('Id'));

		$commonFilters = [
			'striptags',
			'string',
		];

		$code = new Text('Code');
		$code->setFilters($commonFilters);
		$code->addValidators([
			new PresenceOf(['message' => 'Código es requerido']),
		]);
		$this->add($code);

		$title = new Text('Title');
		$title->setFilters($commonFilters);
		$title->addValidators([
			new PresenceOf(['message' => 'Título es requerido']),
		]);
		$this->add($title);

	}
}