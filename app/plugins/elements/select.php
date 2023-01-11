<?php
namespace App\Elements;

class select
{
	var $typedata = null;
	var $web = null;
	var $object = null;
	var $objectsdata = null;

	function __construct($typesdata, $web, $object, $objectsdata)
	{
		$this->typedata = $typesdata;
		$this->web = $web;
		$this->objects = $object;
		$this->objectsdata = $objectsdata;
	}

	function value()
	{
		if( !is_null($this->objectsdata) )
		{
			return $this->objectsdata->ValueText;
		}
		return '';
	}

	function parseValueText($value)
	{
		return $value;
	}

	function parseValueNum($value)
	{
		return null;
	}

	function parseValueDate($value)
	{
		return null;
	}

	function renderView()
	{
		$value = '';
		if( !is_null($this->objectsdata) )
		{
			$value = $this->objectsdata->ValueText;
		}
		return '<div class="data-field">' . $value . '</div>';
	}

	function renderTableView()
	{
		$value = '';
		if( !is_null($this->objectsdata) )
		{
			$value = $this->objectsdata->ValueText;
		}
		return $value;
	}

	function renderEdit($value = null)
	{
		$pos = strpos($this->typedata->Name, '.');
		if( $pos === false ) {
			$name = 'data[' . $this->typedata->Id . ']';
		}
		else {
			$name = $this->typedata->Name;
		}
		$options = json_decode($this->typedata->Options);
		
		if( !is_null($options) )
		{
			$select = '<select name="' . $name . '" class="form-select">';
			$select .= '<option value="">Seleccione...</option>';
			if( $options->type == 'list' ) {
				if( is_array($options->values) ) {
					foreach($options->values as $val) {
						$select .= '<option';
						if( 
							(is_null($value) && $this->objectsdata->ValueText == $val) 
							|| (!is_null($value) && $value == $val)
						) {
							$select .= ' selected';
						}
						$select .= '>' . $val . '</option>';
					}
				} else {
					foreach($options->values as $key => $val) {
						$select .= '<option value="' . $key . '"';
						if( 
							(is_null($value) && $this->objectsdata->ValueText == $key) 
							|| (!is_null($value) && $value == $key)
						) {
							$select .= ' selected';
						}
						$select .= '>' . $val . '</option>';
					}
				}

			}
			$select .= '</select>';
		}
		return $select;
	}

	function renderOptions()
	{
		$options = json_decode($this->typedata->Options);

		$list_selected 	= "";
		$tuple_selected = "";
		$sql_selected 	= "";
		$api_selected 	= "";
		$false_selected = "";
		$true_selected 	= "";
		$valores 		= "";

		if( isset($options->type) )
		{
			if($options->type == "list")
			{
				$list_selected = 'selected';
			}
			if($options->type == "tuple")
			{
				$tuple_selected = 'selected';
			}
			if($options->type == "sql")
			{
				$sql_selected = 'selected';
			}
			if($options->type == "api")
			{
				$api_selected = 'selected';
			}
		}
		if( isset($options->multiple) )
		{
			if($options->multiple == "false")
			{
				$false_selected = 'selected';
			}
			if($options->multiple == "true")
			{
				$true_selected = 'selected';
			}
			
		}
		if( isset($options->values) )
		{
			$valores = $options->values;
		}

		$types = '<div class="row">
					<div class="col-2">
						<label for="name" class="form-label">Formato </label>
						<select name="options[type]" class="form-select">
							<option value="">[Seleccione]</option>
							<option value="list" ' . $list_selected . '>Lista de valores</option>
							<option value="tuple" ' . $tuple_selected . '>Tuplas</option>
							<option value="sql" ' . $sql_selected . '>Consulta base de datos</option>
							<option value="api" ' . $api_selected . '>API Remota</option>
						</select>
					</div>
				</div>';

		$multiple = '<div class="row">
						<div class="col-2">
							<label for="name" class="form-label">Multiple </label>
							<select name="options[type]" class="form-select">
								<option value="">[Seleccione]</option>
								<option value="false" ' . $false_selected . '>No</option>
								<option value="true" ' . $tuple_selected . '>Si</option>
							</select>
						</div>
					</div>';

		$values = '<div class="row">
					<div class="col-6">
						<label for="name" class="form-label">Valores</label>
						<textarea name="options[values]" class="form-control" cols="50" rows="7">' . $valores . '</textarea>
					</div>
				</div>';

		return $types.$multiple.$values;
	}

	public function toFront(\Webs $web, \Objects $object, $data)
	{
		return $data->ValueText;
	}
}
