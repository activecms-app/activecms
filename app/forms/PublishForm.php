<?php
namespace App\Forms;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\Check;
use Phalcon\Validation\Validator\PresenceOf;

class PublishForm extends Form
{
	public function initialize($object)
	{
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

		$displaybegin = new Date('DisplayBegin');
		$displaybegin->setFilters($commonFilters);
		$this->add($displaybegin);

		$displayend = new Date('DisplayEnd');
		$displayend->setFilters($commonFilters);
		$this->add($displayend);

		$published = new Check('Published', ['value' => 'yes']);
		$this->add($published);
	}
}