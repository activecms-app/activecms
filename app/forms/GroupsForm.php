<?php
namespace App\Forms;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Check;
use Phalcon\Validation\Validator\PresenceOf;

class GroupsForm extends Form
{
	public function initialize($entity = null, array $options = [])
	{
		$this->add(new Hidden('Id'));

		$commonFilters = [
			'striptags',
			'string',
		];

		$title = new Text('Title');
		$title->setFilters($commonFilters);
		$title->addValidators([
			new PresenceOf(['message' => 'Título de grupo es requerido']),
		]);
		$this->add($title);

	}
}