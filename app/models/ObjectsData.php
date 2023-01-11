<?php

class ObjectsData extends \Phalcon\Mvc\Model
{
	public function initialize()
	{
		$this->setSource("ObjectsData");
		$this->hasOne(
			'TypesData_Id',
			TypesData::class,
			'Id',
			['alias' => 'typedata']
		);
	}
}
