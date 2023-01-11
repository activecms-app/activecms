<?php

class PermissionsCategories extends \Phalcon\Mvc\Model
{
	public function initialize()
	{
		$this->setSource("PermissionsCategories");
	}

	function getPermissions()
	{
		return Permissions::find(["conditions" => "PermissionsCategories_Id = :categoria:",
								"bind"  => ["categoria" => $this->Id],
								"order" => "Tittle asc"
		]);
	}

}