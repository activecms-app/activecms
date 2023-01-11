<?php
namespace App\Elements;

use Objects;

class reference
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
			return $this->objectsdata->ValueNum;
		}
		return '';
	}

	function parseValueText($value)
	{
		return null;
	}

	function parseValueNum($value)
	{
		if( empty($value) )
			return null;
		return $value;
	}

	function parseValueDate($value)
	{
		return null;
	}

	function renderView()
	{
		$reference_object = null;
		$value = '';
		if( !is_null($this->objectsdata) )
		{
			if( $this->value() > 0 ) {
				$reference_object = Objects::findFirst($this->value());
				if( isset($reference_object->Id) )
				{
					$value = $reference_object->objectversion->Title;
				}
			}
		}
		if( is_null($this->web) || is_null($reference_object) )
		{
			return '<div class="data-field">' . $value . '</div>';
		}
		return '<div class="data-field"><a href="/active/object/data/' . $this->web->getPathByReference($reference_object->Id) . '">' . $value . '</a></div>';
	}

	function renderTableView()
	{
		$reference_object = null;
		$value = '';
		if( !is_null($this->objectsdata) )
		{
			if( !$this->value() )
			{
				return '';
			}
			$reference_object = Objects::findFirst($this->value());
			if( isset($reference_object->Id) )
			{
				$value = $reference_object->objectversion->Title;
			}
		}
		if( is_null($this->web) || is_null($reference_object) )
		{
			return $value;
		}
		return '<a href="/active/object/data/' . $this->web->getPathByReference($reference_object->Id) . '">' . $value . '</a>';
	}

	function renderEdit($value = null)
	{

		$options = json_decode($this->typedata->Options);

		if( isset($options->type) && $options->type == 'list' ) {
			if( is_null($value) ) {
				if( !is_null($this->objectsdata) ) {
					$value = $this->objectsdata->ValueNum;
				}
			}
			$selector = '<select id="data_' . $this->typedata->Id . '" name="data[' . $this->typedata->Id . ']" class="form-select">';
			$selector .= '<option value="">Seleccione...</option>';
			//Select object fron type
			if( isset($options->filter->type) ) {
				$type = \Types::findFirst($options->filter->type);
				if( $type->Id ) {
					foreach ($type->getObjects() as $object) {
						$selector .= '<option value="' . $object->Id .'"';
						if( $object->Id == $value ) {
							$selector .= ' selected';
						}
						$selector .= '>' . $object->objectversion->Title . '</option>';
					}
				}
			}
			$selector .= '</select>';
			return $selector;
		}

		if( is_null($value) )
		{
			if( !is_null($this->objectsdata) ) {
				$value = $this->objectsdata->ValueNum;
				$reference_object = Objects::findFirst($this->value());
				if( isset($reference_object->Id) )
				{
					$title = $reference_object->objectversion->Title;
				}
			}
			else
			{
				$value = '';
			}
		}
		else
		{
			$reference_object = Objects::findFirst($value);
			if( isset($reference_object->Id) )
			{
				$title = $reference_object->objectversion->Title;
			}
		}
		$pos = strpos($this->typedata->Name, '.');
		if( $pos === false ) {
			return '<input type="text" class="form-control reference" id="data_' . $this->typedata->Id . '" name="data[' . $this->typedata->Id . ']" placeholder="Buscar ficha contenido..." autocomplete="off" value="' . $value . '" data-title="' . $title . '">';
		}
		else {
			return '<input type="text" name="' . $this->typedata->Name . '" id="' . substr($this->typedata->Name, 0, $pos) . '_' . substr($this->typedata->Name, $pos + 1) . '"  value="' . $value . '" class="form-control reference" data-title="' . $title . '">';
		}
	}

	function renderOptions()
	{
		$options = json_decode($this->typedata->Options);
		if( isset($options->width) )
		{
			$width = $options->width;
		}
		else
		{
			$width = '';
		}
		return '<div class="row">
					<div class="col-2">
						<label for="name" class="form-label">Largo</label>
						<input type="text" id="option_width" name="options[width]" value="' . $width . '" class="form-control">
					</div>
				</div>';
	}

	public function toFront(\Webs $web, \Objects $object, $data)
	{
		if( $data->ValueNum == 0 )
		{
			return '';
		}
		$reference_object = Objects::findFirst($data->ValueNum);
		if( !isset($reference_object->Id) )
		{
			return '';
		}
		return $reference_object->toFront($web);
	}
}
