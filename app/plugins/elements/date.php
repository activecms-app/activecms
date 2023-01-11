<?php
namespace App\Elements;

class date
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

	function parseValueText($value)
	{
		return null;
	}

	function parseValueNum($value)
	{
		return null;
	}

	function parseValueDate($value)
	{
		if( empty($value) )
			return null;
		return $value;
	}

	function renderView()
	{
		$value = '';
		if( !is_null($this->objectsdata) )
		{
			$type = 'datetime-local';
			if( $this->typedata->Options ) {
				$options = json_decode($this->typedata->Options);
				if( isset($options->type) && $options->type == 'date' ) {
					$type = 'date';
					$value = $this->objectsdata->ValueDate;
				}
			}
			$dateToFrontDT = \DateTime::createFromFormat("Y-m-d H:i:s", $this->objectsdata->ValueDate);
			if( $dateToFrontDT ) {
				if( $type == 'date' )
					$value = $dateToFrontDT->format('d-m-Y'); //TODO: local format
				else
					$value = $dateToFrontDT->format('d-m-Y H:i'); //TODO: local format
			}
		}
		return '<div class="data-field">' . $value . '</div>';
	}

	function renderTableView()
	{
		$value = '';
		if( !is_null($this->objectsdata) )
		{
			$value = $this->objectsdata->ValueDate;
		}
		return $value;
	}

	function renderEdit($value = null)
	{
		$value = '';
		$type = 'datetime-local';
		if( $this->typedata->Options )
		{
			$options = json_decode($this->typedata->Options);
			if( isset($options->type) && $options->type == 'date' )
			{
				$type = 'date';
			}
		}

		if( !is_null($this->objectsdata) ) {
			if( $this->objectsdata->ValueDate ) {
				$dateToFrontDT = \DateTime::createFromFormat("Y-m-d H:i:s", $this->objectsdata->ValueDate);
				if( $dateToFrontDT ) {
					if( $type == 'date' ) {
						$value = $dateToFrontDT->format('Y-m-d');
					} else {
						$value = $dateToFrontDT->format('Y-m-dTH:i');
					}
				}
			}
		}
		return '<input type="' . $type . '" name="data[' . $this->typedata->Id . ']" value="' . $value . '" class="form-control" />';
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
		$dateToFront = '';
		if( $data->ValueDate )
		{
			$dateToFrontDT = \DateTime::createFromFormat("Y-m-d H:i:s", $data->ValueDate);
			if( $dateToFrontDT )
				$dateToFront = $dateToFrontDT->getTimestamp();
		}
		return $dateToFront;
	}
}
