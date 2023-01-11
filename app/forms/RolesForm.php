<?php
namespace App\Forms;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Validation\Validator\PresenceOf;

class RolesForm extends Form
{
	public function initialize($entity = null, array $options = [])
	{
		$this->add(new Hidden('Id'));

		$commonFilters = [
			'striptags',
			'string',
		];

		$name = new Text('Name');
		$name->setFilters($commonFilters);
		$name->addValidators([
			new PresenceOf(['message' => 'Nombre de rol es requerido']),
		]);

		$this->add($name);

		$description = new TextArea('Description');
		$description->setFilters($commonFilters);

		$this->add($description);

	}
}