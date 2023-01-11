<?php

class UsersSessions extends \Phalcon\Mvc\Model
{

	public $id;
	public $keyId;
	public $keySession;
	public $users_Id;
	public $makeDate;
	public $expirationDate;
	public $status;

	public function initialize()
	{
		$this->setSource("UsersSessions");
		$this->belongsTo('Users_Id', 'Users', 'Id', ['alias' => 'User']);
	}

	public function active()
	{
		return $this->Status == 'active';
	}

	public function getMakeDate($format = 'd-m-Y H:i')
	{
		return empty($this->MakeDate) ? '' : date($format, strtotime($this->MakeDate));
	}

	public function getExpirationDate($format = 'd-m-Y H:i')
	{
		return empty($this->ExpirationDate) ? '' : date($format, strtotime($this->ExpirationDate));
	}

	public function finish()
	{
		$this->Status = 'terminated';
		$this->save();
	}
}
