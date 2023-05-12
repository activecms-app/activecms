<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class TypesData extends \Phalcon\Mvc\Model
{
	public function initialize()
	{
		$this->setSource("TypesData");
	}

	public function validation()
	{
		$validation = new Validation();

    	$validation->add(
    		'TypesGroups_Id',
    		new PresenceOf(
    			[
    				'message' => 'Debe seleccionar Grupo.',
    			]
    		)
    	);

    	$validation->add(
    		'TypesElements_Code',
    		new PresenceOf(
    			[
    				'message' => 'Debe seleccionar Tipo.',
    			]
    		)
    	);

    	$validation->add(
    		'Name',
    		new PresenceOf(
    			[
    				'message' => 'Debe ingresar Nombre.',
    			]
    		)
    	);

    	$validation->add(
    		'Title',
    		new PresenceOf(
    			[
    				'message' => 'Debe ingresar Titulo.',
    			]
    		)
    	);

    	return $this->validate($validation);
	}

	public function renderEdit(\Webs $web, \Objects $object, $num = 0, $value = null, $version = 1)
	{
		if( $this->TypesElements_Code == 'table' ) //Is table render header and rows
		{
			$result = '<table class="table"><thead><tr><th width="16"></th>';
			$typesdatas = $object->getTypesDataTable($this->Name);
			foreach($typesdatas as $typesdata)
			{
				$result .= '<th>' . $typesdata->Title . '</th>';
			}
			$result .= '<td width="20"></td></tr></thead>';
			$result .= '<tbody id="tb_' . $this->Id . '">';

			$rows = $object->getTableMaxNum($this, $version);
			if( $rows > 0 )
			{
				for($row = 0; $row < $rows; $row++)
				{
					$result .= '<tr ondragstart="dragstart()"  ondragover="dragover()" onclick="showRow(' . $this->Id . ', this)" draggable="true"><td class="drag" style="cursor:move"><i class="fa-solid fa-ellipsis-vertical"></i></td>';
					foreach($typesdatas as $typesdata)
					{
						$elementclass = "App\\Elements\\" . $typesdata->TypesElements_Code;
						$element = new $elementclass($typesdata, $web, $object, $object->getData($typesdata, $row), $this->getDI());
						$result .= '<td><input type="hidden" name="data[' . $typesdata->Id . '][]" value="' . htmlspecialchars($element->value()) . '">';
						$result .= $element->renderTableView() . '</td>';
					}
					$result .= '<td><button type="button" onclick="removeRow(event)">-</button></td></tr>';
				}
			}
			$result .= '</tbody>';
			$result .= '<tfoot><tr><th colspan="' . (count($typesdatas) + 1) . '"><button type="button" onclick="addRow(' . $this->Id . ')">+</button></tfoot></table>';
			return $result;
		}
		//Recover data
		//Load element class
		$elementclass = "App\\Elements\\" . $this->TypesElements_Code;
		if( $num >= 0 )
		{
			$element = new $elementclass($this, $web, $object, $object->getData($this, $num), $this->getDI());
		}
		else
		{
			$element = new $elementclass($this, $web, $object, null, $this->getDI());
		}
		return $element->renderEdit($value);
	}

	public function renderView(\Webs $web, \Objects $object, $version = 1)
	{
		if( $this->TypesElements_Code == 'table' ) //Is table render header and rows
		{
			$result = '<table class="table"><thead><tr>';
			$typesdatas = $object->getTypesDataTable($this->Name);
			foreach($typesdatas as $typesdata)
			{
				$result .= '<th>' . $typesdata->Title . '</th>';
			}
			$result .= '</tr></thead>';

			$rows = $object->getTableMaxNum($this, $version);
			if( $rows > 0 )
			{
				$result .= '<tbody>';
				for($row = 0; $row < $rows; $row++)
				{
					$result .= '<tr>';
					foreach($typesdatas as $typesdata)
					{
						$elementclass = "App\\Elements\\" . $typesdata->TypesElements_Code;
						$element = new $elementclass($typesdata, $web, $object, $object->getData($typesdata, $row, $version), $this->getDI());
						$result .= '<td>' . $element->renderTableView() . '</td>';
					}
					$result .= '</tr>';
				}
				$result .= '</tbody>';
			}
			$result .= '</table>';
			return $result;
		}
		//Load element class
		$elementclass = "App\\Elements\\" . $this->TypesElements_Code;
		$element = new $elementclass($this, $web, $object, $object->getData($this), $this->getDI());
		return $element->renderView();
	}

	public function renderOptions()
	{
		//Load element class
		$elementclass = "App\\Elements\\" . $this->TypesElements_Code;
		$element = new $elementclass($this, null, null, null, $this->getDI());
		return $element->renderOptions();
	}

	public function getTypeElement()
	{
		return \TypesElements::findFirst(["conditions" => "Code = '".$this->TypesElements_Code."'"]);
	}

	public function getTypesDataTable() {
		if( $this->TypesElements_Code != 'table' )
			return [];
		return \TypesData::find(
			[
				'conditions' => "Name like :typeName: and Deleted = 'no'",
				'bind' => [
					'typeName' => $this->Name . '.%'
				],
				'order' => 'Position asc'
			]
		);
	}

}
