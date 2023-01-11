<?php

class Roles extends \Phalcon\Mvc\Model
{
	public function initialize()
	{
		$this->setSource("Roles");
		$this->hasManyToMany(
			'Id',
			Users_Roles::class,
			'Roles_Id',
			'Users_Id',
			Users::class,
			'Id',
			[
				'reusable' => true,
				'alias' => 'users'
			]
		);
		$this->hasManyToMany(
			'Id',
			Roles_Permissions::class,
			'Roles_Id',
			'Permissions_Id',
			Permissions::class,
			'Id',
			[
				'reusable' => true,
				'alias' => 'permissions'
			]
		);
	}

	public function beforeDelete()
	{
		$roles_permission = Roles_Permissions::find("Roles_Id = " . $this->Id);
		if( $roles_permission )
		{
			foreach($roles_permission as $role_permission )
			{
				$role_permission->delete();
			}
		}
		return true;
	}

	function getPermissionsText()
	{
		$permissions = "";
		if($this->getPermissions())
		{
			$first = true;
			foreach($this->getPermissions() as $permission)
			{
				if($first)
				{
					$first = false;
				}
				else
				{
					$permissions .= ", ";
				}

				$permissions .= $permission->Tittle;
			}
		}
		return $permissions;
	}

	function hasPermission($id)
	{
		if( $this->countPermissions(['Permissions.Id = ?0', 'bind' => [0 => $id]]) )
		{
			return true;
		}
		return false;
	}

}
