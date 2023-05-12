<?php
namespace App\Elements;

class media
{
	var $typedata = null;
	var $web = null;
	var $object = null;
	var $objectsdata = null;
	var $container = null;

	function __construct($typesdata, $web, $object, $objectsdata, $container)
	{
		$this->typedata = $typesdata;
		$this->web = $web;
		$this->object = $object;
		$this->objectsdata = $objectsdata;
		$this->container = $container;
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

	function uploadData($file) {
		//Media destination dir
		if( $this->typedata->Options )
		{
			$options = json_decode($this->typedata->Options);
			if( isset($options->path) ) {
				$path = $options->path;
			} else {
				$path = $this->object->getPathByName();
			}
		}
		else {
			$path = $this->object->getPathByName();
		}
		if( substr($path, -1) != '/' ) {
			$path .= '/';
		}
		$mediaDir = $this->container->getConfig()->application->mediaDir;
		if( substr($mediaDir, 0, -1) != '/' ) {
			$mediaDir .= '/';
		}
		$mediaDir .= $path; //TODO: tomar directorio desde la configuración
		if( !is_dir($mediaDir) ) {
			if( !mkdir($mediaDir, 0777, true) ) {
				return ''; //TODO: exception
			}
		}
		//Copy file to destination
		if( !$file->moveTo($mediaDir . $file->getName()) ) {
			return '';  //TODO: exception
		}
		return $path . $file->getName();
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
		if( is_null($value) ) {
			if( !is_null($this->objectsdata) ) {
				$value = $this->objectsdata->ValueText;
			} else {
				$value = '';
			}
		}
		$pos = strpos($this->typedata->Name, '.');
		if( $pos === false ) {
			return '<input type="text" name="data[' . $this->typedata->Id . ']" value="' . $value . '" class="form-control media" />';
		} else {
			return '<input type="text" name="' . $this->typedata->Name . '" id="' . substr($this->typedata->Name, 0, $pos) . '_' . substr($this->typedata->Name, $pos + 1) . '"  value="' . $value . '" class="form-control media" />';
		}

	}

	function renderOptions()
	{
		
	}

	static public function toFront(\Webs $web, \Objects $object, $data, $container)
	{
		if( $data->ValueText == '' ) {
			return '';
		}
		$mediaDir = $container->getConfig()->application->mediaDir;
		if( substr($mediaDir, 0, -1) != '/' ) {
			$mediaDir .= '/';
		}

		if( file_exists($mediaDir . $data->ValueText) ) {
			$mediaUrl = $container->getConfig()->application->mediaUrl;
			if( substr($mediaUrl, 0, -1) != '/' ) {
				$mediaUrl .= '/';
			}
			return $mediaUrl . $data->ValueText;
		}
		return '';
	}
}
