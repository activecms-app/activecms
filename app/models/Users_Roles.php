<?php

class Users_Roles extends \Phalcon\Mvc\Model
{
	public function initialize()
	{
		$this->setSource("Users_Roles");
	}
}