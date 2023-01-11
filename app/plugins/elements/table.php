<?php
namespace App\Elements;

class table
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
		return $value;
	}

	function parseValueDate($value)
	{
		return null;
	}

	function renderView()
	{
		return '';
	}

	function renderEdit($value = null)
	{
		return '';
	}

	function renderOptions()
	{
		
	}

	public function toFront(\Webs $web, \Objects $object, $data)
	{
		return $data->ValueNum;
	}
}
