<?php
namespace App\Elements;

use ObjectsData;

class text
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
		if( $this->typedata->Options )
		{
			$options = json_decode($this->typedata->Options);
			if( isset($options->format) && $options->format == 'html' )
			{
				$class = 'formathtml';
			}
		}
		if( !is_null($this->objectsdata) )
		{
			$value = $this->objectsdata->ValueText;
		}
		return '<div class="data-field" class="' . $class . '">' . $value . '</div>';
	}

	function renderTableView()
	{
		$value = '';
		if( !is_null($this->objectsdata) )
		{
			$value = htmlspecialchars($this->objectsdata->ValueText);
		}
		return $value;
	}

	function renderEdit($value = null)
	{
		$rows = 3;
		if( $this->typedata->Options )
		{
			$options = json_decode($this->typedata->Options);
			if( isset($options->format) && $options->format == 'html' )
			{
				$class = 'formathtml';
			}
			if( isset($options->rows) )
			{
				$rows = $options->rows;
			}
		}
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
			return '<textarea id="data_' . $this->typedata->Id . '" rows="' . $rows . '" name="data[' . $this->typedata->Id . ']" class="form-control ' . $class . '">' . $value . '</textarea>';
		}
		else {
			return '<textarea rows="' . $rows . '" name="' . $this->typedata->Name . '" id="' . substr($this->typedata->Name, 0, $pos) . '_' . substr($this->typedata->Name, $pos + 1) . '" class="form-control ' . $class . '">' . $value . '</textarea>';
		}
	}

	function renderOptions()
	{
		$options = json_decode($this->typedata->Options);

		$cols_value       = '';
		$rows_value       = '';
		$format_value     = '';
		$plain_selected   = '';
		$html_selectd     = '';
		$markdown_selectd = '';

		if( isset($options->cols) )
		{
			$cols_value = $options->cols;
		}
		if( isset($options['rows']) )
		{
			$rows_value = $options['rows'];
		}
		if( isset($options->format) )
		{
			$format_value = $options->format;
			if($format_value == "plain")
			{
				$plain_selected = 'selected';
			}
			if($format_value == "html")
			{
				$html_selected = 'selected';
			}
			if($format_value == "markdown")
			{
				$markdown_selected = 'selected';
			}
		}

		$cols = '<div class="row">
					<div class="col-2">
						<label for="name" class="form-label">Columnas </label>
						<input type="text" id="option_cols" name="options[cols]" value="' . $cols_value . '" class="form-control">
					</div>
				</div>';

		$rows = '<div class="row">
					<div class="col-2">
						<label for="name" class="form-label">Filas </label>
						<input type="text" id="option_rows" name="options[rows]" value="' . $rows_value . '" class="form-control">
					</div>
				</div>';
		$format = '<div class="row">
					<div class="col-2">
						<label for="name" class="form-label">Formato </label>
						<select name="options[format]" class="form-select">
							<option value="">[Seleccione]</option>
							<option value="plain" '.$plain_selected.'>Texto plano</option>
							<option value="html" '.$html_selected.'>Html</option>
							<option value="markdown" '.$markdown_selected.'>Reducción</option>
						</select>
					</div>
				</div>';
		return $cols.$rows.$format;
	}

	public function toFront(\Webs $web, \Objects $object, $data)
	{
		return $data->ValueText;
	}
}
