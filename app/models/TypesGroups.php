<?php

class TypesGroups extends \Phalcon\Mvc\Model
{
	public function initialize()
	{
		$this->setSource("TypesGroups");
	}

	function getTypesData()
	{
		return \TypesData::find(
	    			[
						'conditions' => 'TypesGroups_Id = :group: and Deleted = :deleted:',
						'bind'       => ['group' => $this->Id, 'deleted' => 'no'],
						'order'      => 'Position asc',
	    			]
	    		);
	}
}