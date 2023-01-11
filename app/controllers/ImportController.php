<?php

use Phalcon\Mvc\View;

class ImportController extends ControllerBase
{
	function indexAction()
	{
		$this->view->types = Types::getTypesClass('file');
		$this->view->webs = Webs::find();

		//Plugins with import registers
		$plugins = Plugins::getRegisterFor('import');

		$formats = [];
		foreach ($plugins as $plugin) {
			$formats = array_merge($formats, $plugin->import_formats());
		}

		$this->view->formats = $formats;
	}

	function processAction()
	{
		if (!$this->request->isPost()) {
			$this->dispatcher->forward([
				'controller' => 'index',
				'action'     => 'index',
			]);
			return;
		}

		$web = Webs::findFirst($this->request->get('web', 'absint'));
		if( is_null($web) )
		{
			$this->flash->error('Web destino no encontrada.');
			$this->dispatcher->forward([
				'controller' => 'import',
				'action'     => 'index',
			]);
			return;
		}

		$folder = Objects::findFirst($this->request->get('folder', 'absint'));
		if( is_null($folder) )
		{
			$this->flash->error('Carpeta contenedora no encontrada.');
			$this->dispatcher->forward([
				'controller' => 'import',
				'action'     => 'index',
			]);
			return;
		}
		$type = Types::findFirst($this->request->get('type', 'absint'));
		if( is_null($type) )
		{
			$this->flash->error('Tipo de contenido no encontrado.');
			$this->dispatcher->forward([
				'controller' => 'import',
				'action'     => 'index',
			]);
			return;
		}

		if( !$this->request->hasFiles() ) {
			$this->flash->error('Falta archivo a importar');
			$this->dispatcher->forward([
				'controller' => 'import',
				'action'     => 'index',
			]);
			return;
		}

		$files = $this->request->getUploadedFiles();
		if( count($files) == 0 )
		{
			$this->flash->error('Falta archivo a importar');
			$this->dispatcher->forward([
				'controller' => 'import',
				'action'     => 'index',
			]);
			return;
		}

		$format = $this->request->get('format', 'string');

		$pluginname = substr($format, 0, strpos($format, '-',0));
		$plugin = Plugins::findFirst(
			[
				'conditions' => "Code = :code:",
				'bind' => [
					'code' => $pluginname
				]
			]
		);

		if( !isset($plugin->Code) ) {
			$this->flash->error('No se encontro plugin ' . $pluginname);
			$this->dispatcher->forward([
				'controller' => 'import',
				'action'     => 'index',
			]);
			return;
		}

		$this->flash->setAutoescape(false);
		foreach ($files as $file) {
			$file_content = file_get_contents($file->getTempName());
			try {
				$object = $plugin->import($folder, $type, $format, $file_content, $this->user);
				$this->flash->success('Contenido importado <a href="' . $this->url->get('object/data/') . $web->getPathByReference($object->Id) . '">' . $object->objectversion->Title . '</a>.');
			} catch (Exception $exception) {
				$this->flash->error($file->getName() . " - " . $exception->getMessage());
				return;
			}
		}
	}

}
