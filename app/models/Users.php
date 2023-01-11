<?php

/**
 * Users model
 *
 * @property Simple|Users[] $users
 * @method   Simple|Users[] getUsers($parameters = null)
 * @method   integer        countUsers()
 */

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Users extends \Phalcon\Mvc\Model
{
	static $status = array(
		'active' 	=> 'Activo',
		'disabled' 	=> 'Inhabilitado',
		'deleted' 	=> 'Eliminado'
	);

    public function initialize()
    {
        $this->setSource("Users");
        $this->hasMany('Id', 'Sessions', 'Users_Id', ['alias' => 'Sessions']);
        $this->hasManyToMany(
			'Id',
			Users_Roles::class,
			'Users_Id',
			'Roles_Id',
			Roles::class,
			'Id',
			[
				'reusable' => true,
				'alias' => 'roles'
			]
		);
    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'Username',
            new Uniqueness(
                [
                    'message' => 'Ya existe un usuario con el Nombre de usuario ingresado',
                ]
            )
        );

        return $this->validate($validator);
    }

	public static function session()
	{
		if( $this->getDI()->getSession()->get('session_id') > 0 )
		{
			$session = Sessions::findFirstById($this->getDI()->getSession()->get('session_id'));
		}
		elseif( isset($_COOKIE['WASESSION']) && !empty($_COOKIE['WASESSION']) )
		{
			$session = Sessions::findFirst([
				'SessionKey = :key: and Status = :status:',
				'bind' => [
					'key' => $_COOKIE['WASESSION'],
					'status' => 'active'
				]
			]);
		}

		if( $session )
		{
			$this->getDI()->getSession()->set('session_id', $session->Id);
			$this->getDI()->getSession()->set('session_user', $session->Users_Id);
			return $session->users;
		}
		return null;
	}

	/**
	 * Create new Session in database
	 *
	 * @return true in success / false - in error
	 */
	public function createSession($sessionkey, $remoteIP = '', $remember = false)
	{
		$session = new UsersSessions;
		$session->AccessKey = $sessionkey;
		$session->Users_Id = $this->Id;
		$session->MakeDate = date('Y-m-d H:i:s');
		$session->IP = $remoteIP;
		if( $remember )
		{
			$session->ExpirationDate = date('Y-m-d H:i:s', strtotime("+30 days"));
		}
		else
		{
			$session->ExpirationDate = date('Y-m-d H:i:s', strtotime("+30 minutes"));
		}
		$session->Status = 'active';
		if( $session->save() === false )
		{
			return null;
		}
		return $session;
	}

	function getDisplayName()
	{
		return $this->FirstName . ' ' . $this->LastName;
	}

	function getStatusName()
	{
		return Users::$status[$this->UserStatus];
	}

	function isActive()
	{
		return $this->UserStatus == 'active';
	}

	function isDisabled()
	{
		return $this->UserStatus == 'disabled';
	}

	function isDeleted()
	{
		return $this->UserStatus == 'deleted';
	}

	function getTotal($deleted = false)
	{
		if( $deleted )
		{
			return Users::count();
		}
		return Users::count(
			[
				'UserStatus != ?0',
				'bind' => [ 'deleted'],
			]
		);
	}

	function getRolesText()
	{
		$roles = "";
		if($this->getRoles())
		{
			$first = true;
			foreach($this->getRoles() as $rol)
			{
				if($first)
				{
					$first = false;
				}
				else
				{
					$roles .= ", ";
				}

				$roles .= $rol->Name;
			}
		}
		return $roles;
	}

	function hasRol($id)
	{
		if( $this->countRoles(['Roles.Id = ?0', 'bind' => [0 => $id]]) )
		{
			return true;
		}
		return false;
	}

	function getLastSession()
	{
		return UsersSessions::findFirst([
			"conditions" => "Users_Id = :usuario:",
			"bind"       => ["usuario" => $this->Id],
			"order"      => "MakeDate desc"
		]);
	}

	function getLastAccess($format = 'd-m-Y H:i')
	{
		$session = $this->getLastSession();
		if( !is_null($session) )
		{
			return $session->getMakeDate($format);
		}
		return '';
	}

	/** 
	 * Recover user by e-mail and generate recover code
	 */
	function recoverByEmail($email)
	{
		$user = Users::findFirstByEmail($email);
		if( $user->isActive() )
		{
			$random = new \Phalcon\Security\Random();
			$user->RecoverCode = $random->base62(6);
			if( $user->save() )
			{
				return $user->RecoverCode;
			}
		}
		return false;
	}

	/**
	 * Change user password and clear recover code
	 */
	function updatePass($pass)
	{
		$this->Pass = $pass;
		$this->RecoverCode = '';
		if( $this->save() )
		{
			return true;
		}
		return false;
	}

}
