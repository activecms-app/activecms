<?php
namespace App\Forms;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;

class TypesForm extends Form
{
	public function initialize($user = null, array $options = [])
	{
		$this->add(new Hidden('Id'));

		$commonFilters = [
			'striptags',
			'string',
		];

		$name = new Text('Name');
		$name->setFilters($commonFilters);
		$name->addValidators([
			new PresenceOf(['message' => 'Nombre es requerido']),
		]);
		$this->add($name);

		$description = new TextArea('Description');
		$description->setFilters($commonFilters);
		$this->add($description);

		$class = new Select('Class', \Types::$classes);
		$class->addValidators([
			new PresenceOf(['message' => 'Clase es requerida']),
		]);
		$this->add($class);

		$template = new Text('Template');
		$template->setFilters($commonFilters);
		$class->addValidators([
			new PresenceOf(['message' => 'Clase es requerida']),
		]);
		$this->add($template);

		$type_status = new Select('TypeStatus', \Types::$type_status);
		$type_status->addValidators([
			new PresenceOf(['message' => 'Estado es requerido']),
		]);
		$this->add($type_status);
	}
}