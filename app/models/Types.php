<?php

class Types extends \Phalcon\Mvc\Model
{
	static $classes = array(
		'file'   => 'Ficha',
		'folder' => 'Carpeta'
	);

	public function initialize()
	{
		$this->setSource("Types");
		$this->hasManyToMany(
			'Id',
			Types_Parents::class,
			'Types_Id',
			'Types_Parent',
			Types::class,
			'Id',
			[
				'reusable' => true,
				'alias' => 'parents'
			]
		);
		$this->hasManyToMany(
			'Id',
			Types_Parents::class,
			'Types_Parent',
			'Types_Id',
			Types::class,
			'Id',
			[
				'reusable' => true,
				'alias' => 'childrens'
			]
		);
		$this->hasMany(
			'Id',
			'TypesGroups',
			'Types_Id',
			[
				'alias' => 'groups'
			]
		);
		$this->hasMany(
			'Id',
			'TypesData',
			'Types_Id',
			[
				'alias' => 'typesdata'
			]
		);
		$this->hasMany(
			'Id',
			'Objects',
			'Types_Id',
			[
				'alias' => 'objects'
			]
		);
	}

	function getParentsText()
	{
		$parents = "";
		if($this->getParents())
		{
			$first = true;
			foreach($this->getParents() as $parent)
			{
				if($first)
				{
					$first = false;
				}
				else
				{
					$parents .= ", ";
				}

				$parents .= $parent->Name;
			}
		}
		return $parents;
	}

	function isFolder()
	{
		return $this->Class == 'folder';
	}

	function getTotal($deleted = false)
	{
		if( $deleted )
		{
			return Types::count();
		}
		return Types::count(
			[
				'TypeStatus != ?0',
				'bind' => [ 'deleted'],
			]
		);
	}

	function getTypesClass($class)
	{
		return Types::find(['conditions' => 'Class = :class: and TypeStatus != :status:', 'bind' => ['class' => $class, 'status' => 'deleted']]);
	}

	function getAll()
	{
		return Types::find(['conditions' => 'TypeStatus != :status:', 'bind' => ['status' => 'deleted'], 'order' => 'Name']);
	}

	function toFront()
	{
		return (object)[
			'id' => $this->Id,
			'name' => $this->Name,
			'class' => $this->Class
		];
	}

}
