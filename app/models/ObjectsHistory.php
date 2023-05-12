<?php

class ObjectsHistory extends \Phalcon\Mvc\Model
{
	public function initialize()
	{
		$this->setSource("ObjectsHistory");
	}

	static public function createHistory($objects_id, $version, $action, $user_id, $comment)
	{
		$history = new ObjectsHistory();
		$history->Objects_Id = $objects_id;
		$history->Version = $version;
		$history->Action = $action;
		$history->ActionDate = date("Y-m-d H:i:s");
		$history->Users_Id = $user_id;
		$history->Comment = $comment;
		if( $history->save() ) {
			return $history;
		}
		return null;
	}
}
