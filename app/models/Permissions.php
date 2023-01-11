<?php

class Permissions extends \Phalcon\Mvc\Model
{
	public function initialize()
	{
		$this->setSource("Permissions");
		$this->hasManyToMany(
			'Id',
			Roles_Permissions::class,
			'Permissions_Id',
			'Roles_Id',
			Roles::class,
			'Id',
			[
				'reusable' => true,
				'alias' => 'roles'
			]
		);
	}

	function getCategorie()
	{
		return PermissionsCategories::findFirst($this->PermissionsCategories_Id);
	}

}
