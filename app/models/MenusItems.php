<?php

/**
 * MenusItems model
 *
 */

class MenusItems extends \Phalcon\Mvc\Model
{

	public function initialize()
	{
		$this->setSource("MenusItems");
		$this->hasMany(['Menus_Id', 'Num'], 'MenusItems', ['Menus_Id', 'MenusItems_Num'], ['alias' => 'Items']);
		$this->hasOne(
			'Objects_Id',
			Objects::class,
			'Id',
			[
				'reusable' => true,
				'alias' => 'object'
			]
		);
		$this->hasOne(
			'MenusItems_Num',
			'MenusItems',
			'Num',
			[
				'alias' => 'parent'
			]
		);
		$this->hasOne(
			'Menus_Id',
			'Menus',
			'Id',
			[
				'alias' => 'menu'
			]
		);
	}

	function getDestination()
	{
		if( $this->ItemType == 'link' ) {
			return $this->Link;
		}
		if( $this->ItemType == 'reference' ) {
			return $this->object->objectversion->Title;
		}
		return '';
	}

	function hasChilds()
	{
		return $this->countItems() > 0;
	}

	function countChilds()
	{
		return $this->countItems();
	}

	static function sortItems($items)
	{
		if( count($items) == 0 ) {
			return;
		}
		for ($numItem = 0; $numItem  < count($items); $numItem ++) {
			if( $items[$numItem]->Position != $numItem + 1 ) {
				$items[$numItem]->Position = $numItem + 1;
				$items[$numItem]->save();
			}
		}
	}

	function normalizePosition($oldParent)
	{
		if( $oldParent == $this->MenusItems_Num ) {
			return;
		}
		if( empty($oldParent) ) {
			$items = self::find([
				'MenusItems_Num is null',
				'order' => 'Position asc'
			]);
			self::sortItems($items);
		} else {
			$items = self::find([
				'MenusItems_Num = :parent',
				'bind' => [
					'parent' => $oldParent
				],
				'order' => 'Position asc'
			]);
			self::sortItems($items);
		}
	}

	function getPrevious()
	{
		if( empty($this->MenusItems_Num) ) {
			return self::findFirst([
				'MenusItems_Num is null and Position < :position:',
				'bind' => [
					'position' => $this->Position
				],
				'sort' => 'Position asc'
			]);
		}
		return self::findFirst([
			'MenusItems_Num = :parent: and Position < :position:',
			'bind' => [
				'parent' => $this->MenusItems_Num,
				'position' => $this->Position
			],
			'sort' => 'Position asc'
		]);
	}

	function toFront($web)
	{
		$front = [
			'num' => $this->Num,
			'title' => $this->Title,
			'childs' => $this->hasChilds(),
			'separator' => $this->ItemType == 'separator',
			'option' => empty($this->Options) ? [] : json_decode($this->Options)
		];

		if( $this->ItemType == 'link' )
		{
			$front['url'] = $this->Link;
		}
		elseif( $this->ItemType == 'reference' )
		{
			$front['url'] = $web->getUrlByReference($this->Objects_Id);
		}

		return (object)$front;
	}
}
