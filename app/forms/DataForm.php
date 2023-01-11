<?php
namespace App\Forms;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Validation\Validator\PresenceOf;

class DataForm extends Form
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
		$this->add($name);

		$title = new Text('Title');
		$title->setFilters($commonFilters);
		$this->add($title);

		$description = new TextArea('Description');
		$description->setFilters($commonFilters);
		$this->add($description);

		$options = new TextArea('Options');
		$options->setFilters($commonFilters);
		$this->add($options);

	}
}