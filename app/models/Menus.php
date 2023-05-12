<?php

/**
 * Menus model
 *
 */

class Menus extends \Phalcon\Mvc\Model
{

	public function initialize()
	{
		$this->setSource("Menus");
		$this->hasMany('Id', 'MenusItems', 'Menus_Id', ['alias' => 'Items']);
	}

	public function getItemsParent($parent_num = null, $includeDisabled = true)
	{
		$bind = [];
		$conditions = [];
		if( is_null($parent_num) )
		{
			$conditions[] = 'MenusItems_Num is null or MenusItems_Num = 0';
		}
		else
		{
			$conditions[] = 'MenusItems_Num = :num:';
			$bind['num'] = $parent_num;
		}
		if( !$includeDisabled )
		{
			$conditions[] = "ItemStatus = 'enabled'";
		}

		return $this->getItems([
			implode(' and ', $conditions),
			'bind' => $bind,
			'order' => 'Position'
		]);

	}

	public function maxNum()
	{
		return MenusItems::maximum([
			'column'=> 'Num',
			'conditions' => "Menus_Id = :menu:",
			'bind' => [
				'menu' => $this->Id
			]
		]);
	}
}
