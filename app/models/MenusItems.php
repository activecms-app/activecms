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
	}

	function hasChilds()
	{
		return $this->countItems() > 0;
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
