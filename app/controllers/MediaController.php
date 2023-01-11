<?php
use Phalcon\Mvc\Controller;

class MediaController extends ControllerBase
{

	/**
	 * List media folders and files
	 */
	public function indexAction($mediafolder = '/')
	{
		//Path the active folder
		//Folder under the active folder
		$this->view->folders = self::getFolders($mediafolder);
		$this->view->files = self::getFiles($mediafolder);
	}

	function getFolders($parent)
	{
		$folders = [];
		$dir = $this->config->application->mediaDir . $parent;
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					if( is_dir($dir . $file) && !in_array($file, ['.', '..', 'thumbnail']))
					{
						array_push($folders, new MediaFolder($file, $parent . $file . '/'));
					}
				}
				closedir($dh);
			}
		}
		return $folders;
	}

	function getFiles($parent)
	{
		$files = [];
		$dir = $this->config->application->mediaDir . $parent;
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					if( is_file($dir . $file) )
					{
						array_push($files, new MediaFile($file, $parent . $file));
					}
				}
				closedir($dh);
			}
		}
		return $files;
	}

}

class MediaFolder
{
	public $name;
	public $path;
	public $files = 0;
	public $folders = 0;

	function __construct($name, $path)
	{
		$this->name = $name;
		$this->path = $path;
	}
}

class MediaFile
{
	public $name;
	public $path;

	function __construct($name, $path)
	{
		$this->name = $name;
		$this->path = $path;
	}

}
