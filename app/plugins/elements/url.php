<?php
namespace App\Elements;

class url
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
		if( is_null($value) )
		{
			if( !is_null($this->objectsdata) ) {
				$value = $this->objectsdata->ValueText;
			}
			else
			{
				$value = '';
			}
		}
		$pos = strpos($this->typedata->Name, '.');
		if( $pos === false ) {
			return '<input type="text" name="data[' . $this->typedata->Id . ']" value="' . $value . '" class="form-control" />';
		}
		else {
			return '<input type="text" name="' . $this->typedata->Name . '" id="' . substr($this->typedata->Name, 0, $pos) . '_' . substr($this->typedata->Name, $pos + 1) . '"  value="' . $value . '" class="form-control" />';
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
		return $data->ValueText;
	}
}
