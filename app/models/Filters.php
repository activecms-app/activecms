<?php
use Phalcon\DI;

/**
 * Filters model
 *
 */

class Filters extends \Phalcon\Mvc\Model
{

	public function initialize()
	{
		$this->setSource("Filters");
		$this->hasOne(
			"Filters_Id", 
			Filters::class, 
			"Id",
			[
				'alias' => 'parent'
			]);
	}

	function getByKey($parent, $key) {
		if( $parent > 0 ) {
			return self::findFirst([
				"Filters_Id = :parent: and FilterKey = :key:",
				'bind' => [
					'parent' => $parent,
					'key' => $key
				]
			]);
		}
		return self::findFirst([
			"Filters_Id is null and FilterKey = :key:",
			'bind' => [
				'key' => $key
			]
		]);	
	}

	function isValid() {
		return $this->Status == 'ready';
	}

	function erase()
	{
		$this->getDi()->getShared('db')->query("delete from Filters_Objects where Filters_Id = " . $this->Id);
	}

	static function getSearch($parent, $search)
	{
		$search = trim($search);
		if( empty($search) )
		{
			return null;
		}
		$search = str_replace("'", "''", $search);
	
		$key = $search;
		$sql = '';
	
		//Busca el filtro
		$filter = self::getByKey($parent, $key);
		if( is_null($filter) ) {
			$filter = new Filters;
			if( $parent > 0 ) {
				$filter->Filters_Id = $parent;
			}
			$filter->FilterKey = $key;
			$filter->Total = 0;
			$filter->Status = 'processing';
			$filter->save();
		} elseif( $filter->isValid() ) {
			return $filter;
		}

		$filter->erase();

		//Copy Filter Parent to Search
		if( intval($parent) == 0 )
		{
			$sql = "insert into Filters_Objects ";
			$sql .= "select " . $filter->Id . ",  O.Id, 0 ";
			$sql .= "from Objects O ";
			$sql .= "where O.Deleted <> 1 and O.Published = 1 ";
			$sql .= "and (O.DisplayBegin <= now() or O.DisplayBegin is null ) ";
			$sql .= "and (O.DisplayEnd > now() or O.DisplayEnd is null ) ";
		}
		else
		{
			$sql = "insert into Filters_Objects ";
			$sql .= "select " . $filter->Id . ",  FO.Objects_Id, 0 ";
			$sql .= "from Filters_Objects FO ";
			$sql .= "where FO.Filters_Id = " . $parent;
		}
		$filter->getDi()->getShared('db')->execute($sql);
		$total = $filter->getDi()->getShared('db')->AffectedRows();
	
		//Search in title
		$sql = "update Objects O, ObjectsVersion OV, Filters_Objects FO ";
		$sql .= "set FO.Position = 5000 ";
		$sql .= "where OV.Objects_Id = O.Id ";
		$sql .= "and OV.Version = O.Version ";
		$sql .= "and OV.Title like '%" . $search . "%' ";
		$sql .= "and FO.Filters_Id = " . $filter->Id . " ";
		$sql .= "and O.Id = FO.Objects_Id ";
		$sql .= "and O.Deleted = 'no' and O.Published = 'yes' ";
		$filter->getDi()->getShared('db')->execute($sql);
		$filter->getDi()->getShared('db')->execute($sql);
		//Search in data
		$sql = "update Objects O, ObjectsData OD, Filters_Objects FO ";
		$sql .= "set FO.Position = FO.Position + 5000 ";
		$sql .= "where OD.Objects_Id = O.Id ";
		$sql .= "and OD.Version = O.Version ";
		$sql .= "and OD.ValueText like '%" . $search . "%'";
		$sql .= "and FO.Position < 10000 ";
		$sql .= "and FO.Filters_Id = " . $filter->Id . " ";
		$sql .= "and O.Id = FO.Objects_Id ";
		$sql .= "and O.Deleted = 'no' and O.Published = 'yes' ";
		$filter->getDi()->getShared('db')->execute($sql);
		//Terminos a buscar
		$terms = explode(' ', $search);
		$divisor = 1;
		for($i = 0; $i < count($terms); $i++ )
		{
			//Search in title
			$sql = "update Objects O, ObjectsVersion OV, Filters_Objects FO ";
			$sql .= "set FO.Position = FO.Position + " . (5000/$divisor) . " ";
			$sql .= "where OV.Objects_Id = O.Id ";
			$sql .= "and OV.Version = O.Version ";
			$sql .= "and OV.Title like '%" . $terms[$i] . "%' ";
			$sql .= "and FO.Position < 10000 ";
			$sql .= "and FO.Filters_Id = " . $filter->Id . " ";
			$sql .= "and O.Id = FO.Objects_Id ";
			$sql .= "and O.Deleted = 'no' and O.Published = 'yes' ";
			$filter->getDi()->getShared('db')->execute($sql);
			//Search in data
			$sql = "update Objects O, ObjectsData OD, Filters_Objects FO ";
			$sql .= "set FO.Position = FO.Position + " . (100/$divisor) . " ";
			$sql .= "where OD.Objects_Id = O.Id ";
			$sql .= "and OD.Version = O.Version ";
			$sql .= "and OD.ValueText like '%" . $terms[$i] . "%'";
			$sql .= "and FO.Position < 10000 ";
			$sql .= "and FO.Filters_Id = " . $filter->Id . " ";
			$sql .= "and O.Id = FO.Objects_Id ";
			$sql .= "and O.Deleted = 'no' and O.Published = 'yes' ";
			$filter->getDi()->getShared('db')->execute($sql);
			$divisor = 2;
		}
		//Delete element without position
		$sql = "delete from Filters_Objects ";
		$sql .= "where Filters_Id = " . $filter->Id . " ";
		$sql .= "and Position = 0";
		$filter->getDi()->getShared('db')->execute($sql);
		$total = $total - $filter->getDi()->getShared('db')->AffectedRows();

		//Calculate validity of filter from the display info of objects
		$sql = "select min(DisplayBegin) from Filters_Objects FO, Objects O ";
		$sql .= "where FO.Filters_Id = " . $filter->Id . " and O.Id = FO.Objects_Id ";
		$sql .= "and O.DisplayBegin > now() ";
		$displayBegin = $filter->getDi()->getShared('db')->fetchColumn($sql);
		$sql = "select min(DisplayEnd) from Filters_Objects FO, Objects O ";
		$sql .= "where FO.Filters_Id = " . $filter->Id . " and O.Id = FO.Objects_Id ";
		$sql .= "and O.DisplayEnd > now() ";
		if( !empty($displayBegin) )
		{
			$sql .= "and DisplayEnd < '" . $displayBegin . "'";
		}
		$displayEnd = $filter->getDi()->getShared('db')->fetchColumn($sql);
		//Set the expiration date
		if( !empty($displayEnd) ) {
			$filter->ExpirationDate = $displayEnd;
		} else {
			$filter->ExpirationDate = $displayBegin;
		}

		$filter->Total = $total;
		$filter->FilterDate = date('Y-m-d H:i:s');
		$filter->Status = 'ready';
		$filter->save();
		return $filter;
	}

	static function getFilter($parent, $field, $comparation, $value) {
	
		$field = strtolower(str_replace("'", "''", $field));
		$value = str_replace("'", "''", $value);

		if( !in_array($comparation, ['=', '<', '>', '<=', '>=', '~']) ) {
			return null; //TODO: exeption
		}

		$key = $field . "\t" . $comparation . "\t" . $value;
	
		//Busca el filtro
		$filter = self::getByKey($parent, $key);
		if( is_null($filter) ) {
			$filter = new Filters;
			if( $parent > 0 ) {
				$filter->Filters_Id = $parent;
			}
			$filter->FilterKey = $key;
			$filter->Total = 0;
			$filter->Status = 'processing';
			$filter->save();
		} elseif( $filter->isValid() ) {
			$parentDate = new DateTime($filter->parent->FilterDate);
			$filterDate = new DateTime($filter->FilterDate);
			if( $parentDate <= $filterDate ) {
				return $filter;
			}
		}

		$filter->erase();
	
		if( $comparation == '~' ) $comparation = 'like';

		if( $field == 'type' ) {
			if( $parent > 0 ) {
				$sql = "select distinct " . $filter->Id . ", O.Id, FO.Position ";
				$sql .= "from Objects O , Filters_Objects FO ";
				$sql .= "where O.Types_Id " . $comparation . " '" . $value . "' ";
				$sql .= "and O.Deleted = 'no' and O.Published = 'yes' ";
				$sql .= "and FO.Filters_Id = " . $parent . " ";
				$sql .= "and O.Id = FO.Objects_Id";
			} else {
				$sql = "select distinct " . $filter->Id . ",  O.Id, 0 ";
				$sql .= "from Objects O ";
				$sql .= "where O.Types_Id " . $comparation . " '" . $value . "' ";
				$sql .= "and O.Deleted = 'no' and O.Published = 'yes' ";
			}
		} elseif( $field == 'displaybegin' ) {
			if( $parent > 0 ) {
				$sql = "select distinct " . $filter->Id . ", O.Id, FO.Position ";
				$sql .= "from Objects O , Filters_Objects FO ";
				$sql .= "where O.DisplayBegin " . $comparation . " '" . $value . "' ";
				$sql .= "and O.Deleted = 'no' and O.Published = 'yes' ";
				$sql .= "and FO.Filters_Id = " . $parent . " ";
				$sql .= "and O.Id = FO.Objects_Id";
			} else {
				$sql = "select distinct " . $filter->Id . ",  O.Id, 0 ";
				$sql .= "from Objects O ";
				$sql .= "where O.DisplayBegin " . $comparation . " '" . $value . "' ";
				$sql .= "and O.Deleted = 'no' and O.Published = 'yes' ";
			}
		} elseif( $field == 'parent' ) {
			if( $parent > 0 )
			{
				$sql = "select distinct " . $filter->Id . ", O.Id, FO.Position ";
				$sql .= "from Objects O, Objects_Parents OP, Filters_Objects FO ";
				$sql .= "where OP.Objects_Parent = " . $value . " ";
				$sql .= "and O.Id = OP.Objects_Id ";
				$sql .= "and O.Deleted = 'no' and O.Published = 'yes' ";
				$sql .= "and FO.Filters_Id = " . $parent . " ";
				$sql .= "and O.Id = FO.Objects_Id";
			}
			else
			{
				$sql = "select distinct " . $filter->Id . ",  O.Id, 0 ";
				$sql .= "from Objects O, Objects_Parents OP ";
				$sql .= "where OP.Objects_Parent = " . $value . " ";
				$sql .= "and O.Id = OP.Objects_Id ";
				$sql .= "and O.Deleted = 'no' and O.Published = 'yes' ";
			}
		} elseif( $field == 'subparent' ) {
			//Search for parents
			$subfolders = $filter->getSubFolders($value);
			if( $parent > 0 )
			{
				$sql = "select distinct " . $filter->Id . ", O.Id, FO.Position ";
				$sql .= "from Objects O, Objects_Parents OP, Filters_Objects FO ";
				$sql .= "where OP.Objects_Parent in (" . implode(',', $subfolders) . ") ";
				$sql .= "and O.Id = OP.Objects_Id ";
				$sql .= "and O.Deleted = 'no' and O.Published = 'yes' ";
				$sql .= "and FO.Filters_Id = " . $parent . " ";
				$sql .= "and O.Id = FO.Objects_Id";
			}
			else
			{
				$sql = "select distinct " . $filter->Id . ",  O.Id, 0 ";
				$sql .= "from Objects O, Objects_Parents OP ";
				$sql .= "where OP.Objects_Parent in (" . implode(',', $subfolders) . ") ";
				$sql .= "and O.Id = OP.Objects_Id ";
				$sql .= "and O.Deleted = 'no' and O.Published = 'yes' ";
			}
		} else { //Search in data
			$sql = "select distinct " . $filter->Id . ", O.Id, ";
			if( $parent > 0 )
			{
				$sql .= "FO.Position";
			}
			else
			{
				$sql .= "0";
			}
			$sql .= " from Objects O ";
			$sql .= "inner join TypesData TD ";
			$sql .= "on (TD.Types_Id = O.Types_Id ";
			$sql .= "and TD.Name = '" . $field . "') ";
			$sql .= "inner join ObjectsData OD ";
			$sql .= "on (OD.Objects_Id = O.Id ";
			$sql .= "and OD.Version = O.Version ";
			$sql .= "and OD.TypesData_Id = TD.Id ) ";
			if( $parent > 0 )
			{
				$sql .= "inner join Filters_Objects FO ";
				$sql .= "on (FO.Filters_Id = " . $parent . " ";
				$sql .= "and O.Id = FO.Objects_Id) ";
			}
			$sql .= "where O.Deleted = 'no' and O.Published = 'yes' ";
			$date = DateTime::createFromFormat('Y-m-d', $value);
			if( $date && $date->format('Y-m-d') == $value ) {
				$sql .= "and OD.ValueDate " . $comparation . " '" . $value . "' ";
			} else {
				$date = DateTime::createFromFormat('Y-m-d H:i:s', $value);
				if( $date && $date->format('Y-m-d H:i:s') == $value ) {
					$sql .= "and OD.ValueDate " . $comparation . " '" . $value . "' ";
				} else {
					$sql .= "and (OD.ValueText " . $comparation . " '" . $value . "' ";
					$sql .= "or OD.ValueNum " . $comparation . " '" . $value . "' ";
					$sql .= ")";
				}
			}
		}

		if( empty($sql) )
		{
			return null;
		}
//echo $sql, '<br>';
		//Load objects to filter
		$filter->getDi()->getShared('db')->execute("insert into Filters_Objects " . $sql);
		//Get the total
		$total = $filter->getDi()->getShared('db')->AffectedRows();
		//Calculate validity of filter from the display info of objects
		$sql = "select min(DisplayBegin) from Filters_Objects FO, Objects O ";
		$sql .= "where FO.Filters_Id = " . $filter->Id . " and O.Id = FO.Objects_Id ";
		$sql .= "and O.DisplayBegin > now() ";
		$displayBegin = $filter->getDi()->getShared('db')->fetchColumn($sql);
		$sql = "select min(DisplayEnd) from Filters_Objects FO, Objects O ";
		$sql .= "where FO.Filters_Id = " . $filter->Id . " and O.Id = FO.Objects_Id ";
		$sql .= "and O.DisplayEnd > now() ";
		if( !empty($displayBegin) )
		{
			$sql .= "and DisplayEnd < '" . $displayBegin . "'";
		}
		$displayEnd = $filter->getDi()->getShared('db')->fetchColumn($sql);
		//Delete objects from filter without display date
		$sql = "delete FO.* from Filters_Objects FO, Objects O ";
		$sql .= "where FO.Filters_Id = " . $filter->Id . " and O.Id = FO.Objects_Id ";
		$sql .= "and ((O.DisplayBegin is not null and O.DisplayBegin > now()) ";
		$sql .= "or (O.DisplayEnd is not null and O.DisplayEnd <= now()) ) ";

		$filter->getDi()->getShared('db')->execute($sql);
		$total = $total - $filter->getDi()->getShared('db')->AffectedRows();
		//Set the expiration date
		if( !empty($displayEnd) ) {
			$filter->ExpirationDate = $displayEnd;
		} else {
			$filter->ExpirationDate = $displayBegin;
		}

/* TODO: recordar que hace esto
		//Set the parent expiration
			if( atoi(filter->getFilters_Id()) > 0 )
			{
				CFilter *filterparent = CFilters::getById(filter->getFilters_Id());
				if( filterparent != NULL )
				{
					if( filterparent->getExpirationDate().IsEmpty() )
					{
						filter->setColumnValue("ExpirationDate", "", true);
					}
					else if( filter->getExpirationDate().IsEmpty() )
					{
						filter->setColumnValue("ExpirationDate", filterparent->getExpirationDate(), true);
					}
					else
					{
						CDateTime filterDate(filter->getExpirationDate());
						CDateTime parentDate(filterparent->getExpirationDate());
						if( parentDate.getTimestamp() <= filterDate.getTimestamp() )
						{
							filter->setColumnValue("ExpirationDate", filterparent->getExpirationDate(), true);
						}
					}
				}
			}
*/
		$filter->Total = $total;
		$filter->FilterDate = date('Y-m-d H:i:s');
		$filter->Status = 'ready';
		$filter->save();
		return $filter;
	}

	function getObjects($offset = 0, $limit = 0, $sort = '', $sortAsc = true) {

		$sortObject = [
			'displaybegin' => 'DisplayBegin',
			'lastchange' => 'LastChange'
		];
		$sortDir = $sortAsc ? 'asc' : 'desc';
		$bind = ['filter' => $this->Id];

		$phql = Objects::query()->innerJoin('Filters_Objects', 'Filters_Objects.Objects_Id = Objects.Id');
		$phql = $phql->where('Filters_Objects.Filters_Id = :filter:');

		//Order
		if( in_array($sort, $sortObject) ) {
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
			$phql = $phql->orderBy('Filters_Objects.Position desc, Objects.Id ');
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

		//print_r($phql->bind($bind)->createBuilder()->getQuery()->getSql()); exit;
		return $phql->bind($bind)->execute();

	}

	function getSubFolders($parent)
	{
		$folders = [$parent];
		$sql = "select Objects_Id Id from Objects_Parents OP ";
		$sql .= "inner join Objects O on (O.Id = OP.Objects_Id) ";
		$sql .= "inner join Types T on (T.Id = O.Types_Id) ";
		$sql .= "where OP.Objects_Parent = :parent and O.Published = 'yes' and O.Deleted = 'no' ";
		$sql .= "and T.Class = 'folder' ";
		$results = $this->getDi()->getShared('db')->fetchAll(
			$sql,
			\Phalcon\Db\Enum::FETCH_ASSOC,
			[
				'parent' => $parent
			]
		);
		if( $results ) {
			foreach ($results as $result) {
				$folders[] = $result['Id']; //TODO: buscar mas adentro
			}
		}
		return $folders;
	}


	static function cleanAll() {
		$container = DI::getDefault();
		$manager = $container->getShared("modelsManager");
		$manager->executeQuery("update Filters set Status = 'outdated' where Filters_Id is null");
	}
}
