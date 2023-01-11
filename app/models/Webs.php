<?php

class Webs extends \Phalcon\Mvc\Model
{
	static $status = array(
		'yes' => 'Publicado',
		'no'  => 'No Publicado'
	);

    public function initialize()
    {
        $this->setSource("Webs");
        $this->hasOne(
			'Objects_Id',
			Objects::class,
			'Id',
			['alias' => 'object']
		);
        $this->hasOne(
			'Themes_Id',
			Themes::class,
			'Id',
			['alias' => 'theme']
		);
	}

	public static function getPublished($host)
	{
		return self::findFirst([
			"Host = :host: and Published = 'yes'",
			'bind' => [
				'host' => $host
			]
		]);
	}

	//Web object functions
	public function adminUrl($controller = 'object')
	{
		return $this->url('/object/' . $this->Id);
	}

	public function getTheme()
	{
		if( isset($this->Themes_Id) )
		{
			return Themes::findFirst($this->Themes_Id);
		}
		return null;
	}

	public function getObjectsPath($path)
	{
		$pathPart = array_filter(explode('/', $path));
		$objectspath = [];
		$object = $this->object;

		if( count($pathPart) > 0 )
		{
			foreach($pathPart as $name)
			{
				if( substr($name, -5) == '.html' )
				{
					$object = $object->getFileByName(substr($name, 0, -5));
					if( is_null($object) )
					{
						throw new Exception('No se encontró la página de contenido "' . $name . '"');
					}
				}
				else
				{
					$object = $object->getFolderByName($name);
					if( is_null($object) )
					{
						throw new Exception('No se encontró la carpeta de contenido "' . $name . '"');
					}
				}
				array_push($objectspath, $object);
			}
		}
		return $objectspath;
	}

	public function getPathByReference($object_id)
	{
		if( $object_id == $this->Objects_Id )
		{
			return $this->Id . '/';
		}

		$object = Objects::findFirst($object_id);
		if( !isset($object->Id) )
		{
			throw new Exception('No se encontró información de la referencia que incluye el objeto con id ' . $object_id);
		}

		$objectParent = $object->getFirstPublishedParent();
		if( !isset($objectParent->Id) )
		{
			throw new Exception('No se encontró carpetas publicadas de la ruta del objeto con id ' . $object_id);
		}

		$path = $this->getPathByReference($objectParent->Id);
		if( $object->isFolder() )
		{
			$path .= $object->Id . '/';
		}
		else
		{
			$path .= $object->Id;
		}
		if( strlen($path) > 2048 )
		{
			throw new Exception('Posible referencia circular');
		}
		return $path;
	}

	public function getUrlByReference($object_id)
	{
		if( $object_id == $this->Objects_Id )
		{
			return '/';
		}

		$object = Objects::findFirst($object_id);
		if( !isset($object->Id) )
		{
			throw new Exception('No se encontró información de la referencia que incluye el objeto con id ' . $object_id);
		}

		$objectParent = $object->getFirstPublishedParent();
		if( !isset($objectParent->Id) )
		{
			throw new Exception('No se encontró carpetas publicadas de la ruta del objeto con id ' . $object_id);
		}

		$path = $this->getUrlByReference($objectParent->Id);
		if( $object->isFolder() )
		{
			$path .= $object->Name . '/';
		}
		else
		{
			$path .= $object->Name . '.html';
		}
		if( strlen($path) > 2048 )
		{
			throw new Exception('URL sobrepasa el largo maximo, posible referencia circular');
		}
		return $path;
	}

	function toFront()
	{
		return (object)[
			'id' => $this->object->Id,
			'title' => $this->object->objectversion->Title,
			'url' => $this->Url
		];
	}
}
