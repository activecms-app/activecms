<?php
namespace App\Forms;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Check;
use Phalcon\Validation\Validator\PresenceOf;

class WebsForm extends Form
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
			new PresenceOf(['message' => 'Nombre de web es requerido']),
		]);
		$this->add($name);

		$host = new Text('Host');
		$host->setFilters($commonFilters);
		$host->addValidators([
			new PresenceOf(['message' => 'Host de web es requerido']),
		]);
		$this->add($host);

		$object = new Text('Objects_Id');
		$object->setFilters($commonFilters);
		$this->add($object);

		$published = new Check('Published', ['value' => 'yes']);
		$this->add($published);

	}
}