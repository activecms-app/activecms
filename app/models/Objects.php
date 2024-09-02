<?php
use Phalcon\DI;
use Phalcon\Paginator\Adapter\QueryBuilder;

class Objects extends \Phalcon\Mvc\Model
{
	protected $LastChange;

	static $published = [
		'no' => 'No',
		'yes' => 'Si' //TODO: language
	];

	public function initialize()
	{
		$this->setSource("Objects");
		$this->hasMany(
			 'Id',
			 ObjectsVersion::class,
			 'Objects_Id',
			 ['alias' => 'versions']
		 );
		$this->hasOne(
			['Id', 'Version'],
			ObjectsVersion::class,
			['Objects_Id', 'Version'],
			['alias' => 'objectversion']
		);
		$this->hasOne(
			'Types_Id',
			Types::class,
			'Id',
			['alias' => 'type']
		);
		$this->hasMany(
			'Id',
			ObjectsData::class,
			'Objects_Id',
			['alias' => 'data']
		);
		$this->hasManyToMany(
			'Id',
			Objects_Parents::class,
			'Objects_Id', 'Objects_Parent',
			Objects::class,
			'Id',
			['alias' => 'parent']
		);
		$this->hasOne(
			'LastUser_Id',
			Users::class,
			'Id',
			['alias' => 'lastuser']
		);
	}

	public function getLastChange()
	{
		if( is_null($this->LastChange) )
		{
			return null;
		}
		return new DateTime($this->LastChange);
	}

	public function searchObjects($text, $class, $type, $user, $deep, $page, $item_per_page)
	{
		//Count results
		$queryBuilder = new \Phalcon\Mvc\Model\Query\Builder();
		$queryBuilder
			->from("Objects")
			->columns(['count' => "COUNT(*)"])
			->innerJoin("ObjectsVersion", "ObjectsVersion.Objects_Id = Objects.Id and ObjectsVersion.Version = Objects.Version");
		if( !empty($class) )
		{
			$queryBuilder->innerJoin("Types", "Types.Id = Objects.Types_Id");
		}
		$queryBuilder
			->where("Objects.Deleted = 'no'")
			->andwhere("ObjectsVersion.Title like :title:", ['title' => '%' . $text . '%']);
		if( !empty($class) )
		{
			$queryBuilder->andwhere("Types.Class = :class:", ['class' => $class]);
		}
		$result = $queryBuilder->getQuery()->getSingleResult();
		if( $result && $result['count'] > 0)
		{
			$total = $result['count'];
			$paginator = new Paginator($total, $item_per_page, $page);
			$query = Objects::query()->innerJoin('ObjectsVersion', 'ObjectsVersion.Objects_Id = Objects.Id and ObjectsVersion.Version = Objects.Version');
			if( !empty($class) )
			{
				$query->innerJoin("Types", "Types.Id = Objects.Types_Id");
			}
			$query->where("Objects.Deleted = 'no'")
			->andWhere("ObjectsVersion.Title like :title:", ['title' => '%' . $text . '%']);
			if( !empty($class) )
			{
				$query->andwhere("Types.Class = :class:", ['class' => $class]);
			}
			$paginator->setItems($query->limit($paginator->getLimitStart(), $paginator->getItemsPerPage())->execute());

			return $paginator;
		}
		return null;
	}

	public function getChildByName($name, $class)
	{
		//TODO: add class
		return $this->modelsManager->createBuilder()
		->from("Objects")
		->join("Objects_Parents", "Objects_Parents.Objects_Id = Objects.Id")
		->where("Objects_Parents.Objects_Parent = :id:", [ 'id' => $this->Id])
		->andWhere("Objects.Name = :name:", ['name' => $name])
		->andWhere("Objects.Deleted = 'no'")
		->getQuery()
		->getSingleResult();
	}

	public function getFolderByName($foldername)
	{
		return $this->getChildByName($foldername, 'folder');
	}

	public function getFileByName($foldername)
	{
		return $this->getChildByName($foldername, 'file');
	}

	public function getChildById($id)
	{
		$op = Objects_Parents::findFirst([
			'Objects_Id = :id: and Objects_Parent = :parent:',
			'bind' => [
				'id' => $id,
				'parent' => $this->Id
			]
		]);
		return Objects::findFirst($op->Objects_Id);
	}

	public function getChilds($classtype = null, $published = false, $offset = 0, $limit = 0, $sort = '', $sortAsc = null)
	{
		$sortObject = [
			'id' => 'Id',
			'displaybegin' => 'DisplayBegin',
			'lastchange' => 'LastChange'
		];

		$bind = ['parent' => $this->Id];
		$sortDir = $sortAsc ? 'asc' : 'desc';
		$phql = Objects::query()
			->innerJoin('Objects_Parents', 'Objects_Parents.Objects_Id = Objects.Id')
			->innerJoin('Types', 'Types.Id = Objects.Types_Id')
			->where('Objects_Parents.Objects_Parent = :parent:')
			->andwhere('Objects.Deleted = \'no\'');
		if( !is_null($classtype) ) {
			$phql = $phql->andwhere('Types.Class = :class:');
			$bind['class'] = $classtype;
		}
		if( $published ) {
			$phql = $phql->andwhere("Published = 'yes'");
		}
		//Order
		if( array_key_exists($sort, $sortObject) ) {
			$phql = $phql->orderBy('Objects.' . $sortObject[$sort] . ' ' . $sortDir);
		}
		elseif( $sort == 'title' ) {
			$phql = $phql->innerJoin('ObjectsVersion', 'ObjectsVersion.Objects_Id = Objects.Id and ObjectsVersion.Version = Objects.Version');
			$phql = $phql->orderBy('ObjectsVersion.Title ' . $sortDir);
		}
		elseif( !empty($sort) ) {
			$phql = $phql->innerJoin('TypesData', 'TypesData.Types_Id = Objects.Types_Id');
			$phql = $phql->innerJoin('ObjectsData', 'ObjectsData.Objects_Id = Objects.Id and ObjectsData.Version = Objects.Version and ObjectsData.TypesData_Id = TypesData.Id and ObjectsData.Num = 0');
			$bind['sort'] = $sort;
			$phql = $phql->andWhere('TypesData.Name = :sort:')
				->orderBy('ObjectsData.ValueText ' . $sortDir . ', ObjectsData.ValueNum ' . $sortDir . ', ObjectsData.ValueDate ' . $sortDir);
		}
		else {
			$phql = $phql->orderBy('Objects_Parents.Position asc, DisplayBegin ' . $sortDir);
		}

		if( $limit ) {
			if( $offset ) {
				$phql = $phql->limit($limit, $offset);
			} else {
				$phql = $phql->limit($limit);
			}
		}
		elseif( $offset ) {
			$phql = $phql->limit(0, $offset);
		}
		return $phql->bind($bind)->execute();
	}

	public function isPublished()
	{
		return $this->Published == 'yes' && $this->Deleted == 'no';
	}

	public function isFolder()
	{
		return $this->type->isFolder();
	}

	public function getFolders($published = false, $offset = 0, $limit = 0, $sort = '', $sortAsc = null)
	{
		return $this->getChilds('folder', $published, $offset, $limit, $sort, $sortAsc);
	}

	public function getFiles($published = false, $offset = 0, $limit = 0, $sort = '', $sortAsc = null)
	{
		return $this->getChilds('file', $published, $offset, $limit, $sort, $sortAsc);
	}

	public function getObjects($published = false, $offset = 0, $limit = 0, $sort = '', $sortAsc = null)
	{
		return $this->getChilds(null, $published, $offset, $limit, $sort, $sortAsc);
	}

	public function countChilds($classtype = null, $published = false)
	{
		$bind = ['parent' => $this->Id];

		$phql = "select count(*) count
			from Objects
			inner join Objects_Parents on Objects_Parents.Objects_Id = Objects.Id
			inner join Types on Types.Id = Objects.Types_Id
			where Objects_Parents.Objects_Parent = :parent
			and Objects.Deleted = 'no'";
		if( !is_null($classtype) ) {
			$phql .= " and Types.Class = :class";
			$bind['class'] = $classtype;
		}
		if( $published ) {
			$phql .= " and Published = 'yes'";
		}

		$row = $this->getDI()->get('db')->fetchOne($phql, \Phalcon\Db\Enum::FETCH_OBJ, $bind);
		if( $row ) {
			return $row->count;
		}
		return 0;
	}

	public function countFiles($published = false)
	{
		return $this->countChilds('file', $published);
	}

	public function getPath($path = null)
	{
		if( !is_null($path) )
		{
			return $path . '/' . $this->Id;
		}
	}

	public function hide()
	{
		$this->Deleted = 'yes';
		if( $this->save() === false )
		{
			return false;
		}
		return true;
	}

	public function getPathByName()
	{
		$objectParent = $this->getFirstParent();
		if( is_null($objectParent) )
		{
			return '';
		}
		if( $this->isFolder() )
		{
			return $objectParent->getPathByName() . $this->Name . '/';
		}
		return $objectParent->getPathByName() . $this->Name;
	}

	function posibleSubtypes()
	{
		//TODO: falta las modificaciones que se hagan en relacion a Objects_Types
		return $this->type->childrens;
	}

	function newChild(Users $user, Types $type, string $name, string $title)
	{
		$object = new Objects;
		$object->Version = 1;
		$object->Types_Id = $type->Id;
		$object->Name = $name;
		$object->Published = 'no';
		$object->LastChange = date('Y-m-d H:i:s');
		$object->LastUser_Id = $user->Id;
		$object->Deleted = 'no';
		if( $object->save() )
		{
			$objectversion = new ObjectsVersion;
			$objectversion->Objects_Id = $object->Id;
			$objectversion->Version = $object->Version;
			$objectversion->Title = $title;
			$objectversion->save();
			$object_parent = new Objects_Parents;
			$object_parent->Objects_Id = $object->Id;
			$object_parent->Objects_Parent = $this->Id;
			$object_parent->Position = 0; //TODO: calcular posicion
			$object_parent->save();
			return $object;
		}
		return null;
	}

	function hasTypesGroups()
	{
		return $this->type->countGroups() > 0;
	}

	function getTypesGroups()
	{
		return $this->type->getGroups(['order' => 'Position']);
	}

	function hasTypesData($groupId)
	{
		return $this->type->countTypesData([
			'conditions' => "TypesGroups_Id = :group: and Deleted = 'no'",
			'bind' => [ 'group' => $groupId]
		]);
	}

	function getTypesData($groupId = null)
	{
		//TODO: omit deleted groups
		if( !is_null($groupId) ) 
		{
			return $this->type->getTypesData([
				'conditions' => "TypesGroups_Id = :group: and Name not like '%.%' and Deleted = 'no'",
				'bind' => [ 'group' => $groupId],
				'order' => 'Position'
			]);
		}
		return $this->type->getTypesData([
			'conditions' => "TypesGroups_Id is null and Name not like '%.%'",
			'order' => 'Position'
		]);
	}

	function getTypesDataTable($name)
	{
		return $this->type->getTypesData([
			'conditions' => "Name like '" . $name . ".%' and Deleted = 'no'",
			'order' => 'Position'
		]);
	}

	function getData(TypesData $typesData, $num = 0, $version = 0)
	{
/*		if( $this->TypesElements_Code == 'table' )
		{
			
		}*/
		//Rescue object data
		$data = ObjectsData::findFirst([
			'conditions' => 'Objects_Id = :objectid: and TypesData_Id = :typeid: and Version = :version: and Num = :num:',
			'bind' => ['objectid' => $this->Id, 'typeid' => $typesData->Id, 'version' => ($version > 0 ? $version : $this->Version), 'num' => $num]
		]);
		return $data;
	}

	function getTableMaxNum(TypesData $typesData, $version)
	{
		$phql = "select Max(ObjectsData.Num + 1) Num 
			from Objects 
			inner join ObjectsData on ObjectsData.Objects_Id = Objects.Id
			inner join TypesData on TypesData.Id = ObjectsData.TypesData_Id
			where Objects.Id = {$this->Id}
			and TypesData.Name like '{$typesData->Name}.%'
			and ObjectsData.Version = {$version}
		";
		$results = $this->getDI()->get('db')->fetchOne($phql);
		if( $results )
		{
			return $results['Num'];
		}
		return 0;
	}

	function getFirstParent($published = false)
	{
		$objectsparents = $this->getParent([
			"Deleted = 'no'",
			'limit' => 1
		]);
		if( $objectsparents && count($objectsparents) )
		{
			return $objectsparents[0];
		}
		return null;
	}

	function getFirstPublishedParent()
	{
		$objectsparents = $this->getParent([
			"Deleted = 'no'",
			'limit' => 1
		]);
		if( $objectsparents && count($objectsparents) )
		{
			return $objectsparents[0];
		}
		return null;
	}

	function setData($typesDataId, $value, $version = 0)
	{
		//Rescue type data
		$typeData = TypesData::findFirst([
			'conditions' => 'Id = :id:',
			'bind' => ['id' => $typesDataId]
		]);
		if( $typeData )
		{
			if( is_array($value) )
			{
				//Delete all data from the type
				$dataset = ObjectsData::find([
					'conditions' => 'Objects_Id = :objectid: and TypesData_Id = :typeid: and Version = :version:',
					'bind' => ['objectid' => $this->Id, 'typeid' => $typeData->Id, 'version' => ($version > 0 ? $version : $this->Version)]
				]);
				$dataset->delete();
				//Add new data
				$elementclass = "App\\Elements\\" . $typeData->TypesElements_Code;
				$element = new $elementclass($typeData, null, $this, null, $this->getDI());
				foreach ($value as $num => $valuenum) {
					$data = new ObjectsData();
					$data->Version = $version > 0 ? $version : $this->Version;
					$data->Objects_Id = $this->Id;
					$data->TypesData_Id = $typeData->Id;
					$data->Num = $num;
					$data->ValueText = $element->parseValueText($valuenum);
					$data->ValueNum = $element->parseValueNum($valuenum);
					$data->ValueDate = $element->parseValueDate($valuenum);
					$data->save();
				}
			}
			else
			{
				//Rescue object data
				$data = ObjectsData::findFirst([
					'conditions' => 'Objects_Id = :objectid: and TypesData_Id = :typeid: and Version = :version: and Num = 0',
					'bind' => ['objectid' => $this->Id, 'typeid' => $typeData->Id, 'version' => ($version > 0 ? $version : $this->Version)]
				]);
				if( is_null($data) )
				{
					$data = new ObjectsData();
					$data->Version = $version > 0 ? $version : $this->Version;
					$data->Objects_Id = $this->Id;
					$data->TypesData_Id = $typeData->Id;
					$data->Num = 0;
				}
				$elementclass = "App\\Elements\\" . $typeData->TypesElements_Code;
				$element = new $elementclass($typeData, null, $this, $data, $this->getDI());
				$data->ValueText = $element->parseValueText($value);
				$data->ValueNum = $element->parseValueNum($value);
				$data->ValueDate = $element->parseValueDate($value);
				$data->save();
			}
		}
	}

	function uploadData($typesDataId, $file) {
		if( is_numeric($typesDataId) ) {
			$typeData = TypesData::findFirst($typesDataId);
		} else {
			$typeData = TypesData::findFirstByName($typesDataId);
		}
		if( !$typeData ) {
			throw new Exception('Campo de datos desconocido');
		}
		$elementclass = "App\\Elements\\" . $typeData->TypesElements_Code;
		$element = new $elementclass($typeData, null, $this, null, $this->getDI());
		return $element->uploadData($file);
	}

	function toFront($web, $with_data = false, $parent_path = null)
	{
		//TODO: type.id, type.name
		$displaybegin = '';
		if( $this->DisplayBegin )
		{
			$displaybeginDT = DateTime::createFromFormat("Y-m-d H:i:s", $this->DisplayBegin);
			if( $displaybeginDT )
				$displaybegin = $displaybeginDT->getTimestamp();
		}
		$displayend = '';
		if( $this->DisplayEnd )
		{
			$displayEndDT = DateTime::createFromFormat("Y-m-d H:i:s", $this->DisplayEnd);
			if( $displayEndDT )
				$displayend = $displayEndDT->getTimestamp();
		}
		if( is_null($parent_path) )
		{
			try {
				$path = $web->getUrlByReference($this->Id);
			}
			catch( Exception $e)
			{
				$path = '';
			}
		}
		elseif( $this->isFolder() )
		{
			$path = $parent_path . $this->Name . '/';
		}
		else
		{
			$path = $parent_path . $this->Name . '.html';
		}
		$front = [
			'id' => $this->Id,
			'version' => $this->Version,
			'name' => $this->Name,
			'title' => $this->objectversion->Title,
			'displaybegin' => $displaybegin,
			'displayend' => $displayend,
			'path' => $path,
			'url' => $web->Url . $path,
			'type' => $this->type->toFront()
		];
		if( $web->Objects_Id != $this->Id )
		{
			$front['parent'] = $this->getFirstPublishedParent()->toFront($web, false);
		}

		//TODO: optimization and process element class
		if( $with_data ) {
			$sql = "select TD.Name, TD.TypesElements_Code, OD.Num, OD.ValueText, OD.ValueNum, OD.ValueDate
			from ObjectsData OD
			inner join TypesData TD on TD.Id = OD.TypesData_Id
			where OD.Objects_Id = " . $this->Id . " order by OD.Num";
			$objectdatas = $this->getDI()->get('db')->fetchAll($sql, \Phalcon\Db\Enum::FETCH_OBJ);
			if( $objectdatas ) {
				foreach($objectdatas as $data) {
					if( $data->TypesElements_Code != 'table' ) {
						$elementclass = "App\\Elements\\" . $data->TypesElements_Code;
						if( strpos($data->Name, '.') > 0 ) {
							list($list, $name) = explode('.', $data->Name);
							if( !isset($front[$list]) ) {
								$front[$list] = [];
								$front[$list][$data->Num] = [];
							}
							elseif( !isset($front[$list][$data->Num]) ) {
								$front[$list][$data->Num] = [];
							}
							$front[$list][$data->Num][$name] = $elementclass::toFront($web, $this, $data, $this->getDI());
						}
						else {
							$front[$data->Name] = $elementclass::toFront($web, $this, $data, $this->getDI());
						}
					}
				}
			}
		}
		return (object)$front;
	}

	function getValue($web, $name, $num = 0) {
		$typeData = TypesData::findFirst([
			'conditions' => 'Name = :name: and Types_Id = :typeid:',
			'bind' => [
				'name' => $name,
				'typeid' => $this->Types_Id
			]
		]);
		if( !isset($typeData->Id) ) {
			return '';
		}
		$data = ObjectsData::findFirst([
			'conditions' => 'Objects_Id = :objectid: and TypesData_Id = :typeid: and Version = :version: and Num = :num:',
			'bind' => [
				'objectid' => $this->Id,
				'typeid' => $typeData->Id,
				'version' => $this->Version,
				'num' => $num
			]
		]);
		$elementclass = "App\\Elements\\" . $typeData->TypesElements_Code;
		if( strpos($data->Name, '.') > 0 ) {
			list($list, $name) = explode('.', $data->Name);
			return $elementclass::toFront($web, $this, $data, $this->getDI());
		}
		return $elementclass::toFront($web, $this, $data, $this->getDI());
	}

	function addHistory(Users $user, $action, $comment = '')
	{
		$history = ObjectsHistory::createHistory($this->Id, $this->Version, $action, $user->Id, $comment);
	}

	static function findRecent(Users $user)
	{
		return Objects::query()->innerJoin('ObjectsHistory', 'ObjectsHistory.Objects_Id = Objects.Id and ObjectsHistory.Version = Objects.Version')
		->where("ObjectsHistory.Users_Id = :user:")
		->bind(['user' => $user->Id])
		->orderBy('ObjectsHistory.ActionDate desc')
		->limit(5)
		->execute();
	}

	function log()
	{
		$log = new ObjectsLogs;
		$log->Objects_Id = $this->Id;
		$log->Version = $this->Version;
		$log->Date = date('Y-m-d H:i:s');
		$log->save();
	}
}
