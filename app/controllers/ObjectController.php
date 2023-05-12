<?php
use Phalcon\Mvc\View;
use App\Forms\PublishForm;

class ObjectController extends ControllerBase
{
	protected $path = [];
	protected $urlpath = '';

	public function initialize()
	{
		parent::initialize();

		$object = $this->web->object;
		$this->urlpath .= '/' . $object->Id;
		array_push($this->path, $object);
		$path = $this->dispatcher->getParams();
		if( count($path) > 1 ) {
			for($i = 1; $i < count($path); $i++)
			{
				$object = $object->getChildById($path[$i]);
				if( is_null($object) )
				{
					//TODO: error - path not exist
				}
				$this->urlpath .= '/' . $object->Id;
				array_push($this->path, $object);
			}
		}
		//Path for URL
		$this->view->urlpath = $this->urlpath;
		//Path
		$this->view->path = $this->path;
	}

	public function listAction()
	{
		//Folders
		$this->view->folders = end($this->path)->getFolders();
		//Files
		$this->view->files = end($this->path)->getFiles();
		//Objeto actual
		$this->view->object = end($this->path);

		//$this->view->disableLevel(View::LEVEL_LAYOUT);
	}

	public function newAction()
	{
		//TODO: es una carpeta el objeto
		$folder = end($this->path);
		$this->view->types = $folder->posibleSubtypes();

		$this->view->disableLevel(View::LEVEL_LAYOUT);
	}

	public function createAction()
	{
		$errors = [];
		$folder = end($this->path);
		//Tipo
		$type = Types::findFirst($this->request->get('type', 'absint'));
		if( is_null($type) )
		{
			array_push($errors, 'Tipo de contenido no encontrado.');
		}
		//Name
		$name = $this->request->get('name', 'alnum', '', true);
		if( empty($name) )
		{
			array_push($errors, 'Nombre solo puede contener letras y número, no puede ser vacío.');
		}
		//Title
		$title = $this->request->get('title', null, '', true);
		if( empty($title) )
		{
			array_push($errors, 'Título no puede ser vacío.');
		}
		if( count($errors) )
		{
			foreach ($errors as $error) {
				$this->flash->error((string)$error);
			}
			$this->dispatcher->forward([
				'controller' => 'object',
				'action'     => 'new',
			]);
			return;
		}

		$object = $folder->newChild($this->user, $type, $name, $title);
		array_push($this->path, $object);
		$this->urlpath .= '/' . $object->Id;
		$this->dispatcher->forward([
			'controller' => 'object',
			'action'     => 'data',
		]);
	}

	public function dataAction()
	{
		$this->view->object = end($this->path);
	}

	public function publishAction()
	{
		$object = end($this->path);
		$this->view->object = $object;

		$this->view->form = new PublishForm($object);
	}

	public function publishsaveAction()
	{
		$object = end($this->path);
		$this->view->object = $object;

		if( $this->request->has('delete') ) {
			if( $object->hide() ) {
				$this->flashSession->success('Eliminado');
				//Remove from urlpath string element to the right from last /
				$parenturlpath = substr($this->urlpath, 0, strrpos($this->urlpath, '/'));
				$this->response->redirect($this->url->get('/object/list' . $parenturlpath), true);
				return;
			}
			$this->flash->error('No se pudo eliminar');
			$this->dispatcher->forward([
				'action' => 'publish'
			]);
			return;
		}
		$data = $this->request->getPost();
		if( empty($data['DisplayBegin']) ) unset($data['DisplayBegin']);
		if( empty($data['DisplayEnd']) ) unset($data['DisplayEnd']);
		if( !isset($data['Published']) ) $data['Published'] = 'no';
		$form = new PublishForm($object);
		if (!$form->isValid($data, $object)) {
			foreach ($form->getMessages() as $message) {
				$this->flash->error((string)$message);
			}
			$this->dispatcher->forward([
				'action' => 'publish'
			]);
			return;
		}

		if (!$object->save()) {
			foreach ($object->getMessages() as $message) {
				$this->flash->error((string)$message);
			}
			$this->dispatcher->forward([
				'action' => 'publish'
			]);
			return;
		}
		$form->clear();
		$this->flash->success('Actualizado');

		Filters::cleanAll();

		$this->dispatcher->forward([
			'action' => 'publish'
		]);
	}

	public function editAction()
	{
		$this->view->object = end($this->path);
	}

	public function mediaAction()
	{
		$this->view->object = end($this->path);
	}

	public function tableAction()
	{
		$this->view->td = $this->request->get('td');
		if( $this->view->td > 0 )
		{
			$this->view->object = end($this->path);
			$this->view->disableLevel(
				[
					View::LEVEL_MAIN_LAYOUT => true,
					View::LEVEL_LAYOUT => true
				]
			);
		}
	}

	public function rowAction()
	{
		$this->view->td = $this->request->get('td');
		if( $this->view->td > 0 )
		{
			$this->view->object = end($this->path);
			$this->view->disableLevel(
				[
					View::LEVEL_MAIN_LAYOUT => true,
					View::LEVEL_LAYOUT => true
				]
			);
		}
	}

	public function saveAction()
	{
		$object = end($this->path);
		$this->view->object = $object;

		//Update version
		if( $this->request->hasPost('version') )
		{
			$version = ObjectsVersion::findFirst([
				'conditions' => "Objects_Id = ?0 and Version = ?1",
				'bind' => [$object->Id, $object->Version]
			]);
			$version->Title = $this->request->getPost('version')['Title'];
			$version->save();
		}
		//Update data
		if( $this->request->hasPost('data') )
		{
			$files = null;
			if ($this->request->hasFiles()) {
				$files = $this->request->getUploadedFiles(true, true);
			}
			foreach($this->request->getPost('data') as $typedataId => $value)
			{
				try {
					if( is_array($files) && array_key_exists('data_' . $typedataId, $files) ) {
						$object->setDataFile($typedataId, $files['data_' . $typedataId]);
					}
					else {
						$object->setData($typedataId, $value);
					}
				} catch (Exception $e) {
					echo 'Excepción capturada: ',  $e->getMessage(), "\n";
					exit;
				}
			}
		}

		$object->addHistory($this->user, 'saved', '');
		$this->flash->success('Actualizado');

		Filters::cleanAll();

		$this->dispatcher->forward([
			'action' => 'edit'
		]);
	}

	public function uploadAction() {
		$object = end($this->path);
		$this->view->object = $object;

		$result = [];
		$typedataId = $this->request->getPost('id');
		if( !$typedataId ) {
			$result[] = [
				'error' => 'Falta campo asociado'
			];
			$this->response->setJsonContent($result)->send();
			exit;
		}
		if( $this->request->hasFiles() ) {
			$files = $this->request->getUploadedFiles(true, true);
			foreach($files as $file) {
				try {
					$error = '';
					$path = $object->uploadData($typedataId, $file);
				} catch (Exception $e) {
					$path = '';
					$error = $e->getMessage();
				}
				$result[] = [
					'url' => $this->config->mediaUrl . $path,
					'path' => $path,
					'error' => $error
				];
			}
		}
		$this->response->setJsonContent($result)->send();
		exit;
	}

	public function versionsAction()
	{
		$this->view->object = end($this->path);
	}

	public function previewAction()
	{
		$url = 'http://' . $this->web->Host; //TODO: http o https
		for($i = 1; $i < count($this->path); $i++)
		{
			$url .= '/' . $this->path[$i]->Name;
			if( $this->path[$i]->Type->Class == 'file' )
			{
				$url .= '.html';
			}
		}
		$this->response->redirect($url, true);
		return;
	}

	public function porticomwAction()
	{
		$object = end($this->path);

		$filename = $object->getValue($this->web, 'code') . '.xml';
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><article></article>');

		$xml->addChild('title', $object->getValue($this->web, 'titulo_en'));

		$journal_title = $xml->addChild('meta');
		$journal_title->addAttribute('name', 'citation_journal_title');
		$journal_title->addAttribute('content', 'Medwave');

		$publisher = $xml->addChild('meta');
		$publisher->addAttribute('name', 'citation_publisher');
		$publisher->addAttribute('content', 'Medwave Estudios Limitada');

		$issn = $xml->addChild('meta');
		$issn->addAttribute('name', 'citation_issn');
		$issn->addAttribute('content', '0717-6384');

		$volume = $xml->addChild('meta');
		$volume->addAttribute('name', 'citation_volume');
		$volume->addAttribute('content', $object->getValue($this->web, 'volume'));

		$issue = $xml->addChild('meta');
		$issue->addAttribute('name', 'citation_issue');
		$issue->addAttribute('content', $object->getValue($this->web, 'issue'));

		$publication_date = $xml->addChild('meta');
		$publication_date->addAttribute('name', 'citation_publication_date');
		$pubdate = $object->getValue($this->web, 'fechapublicacion');
		if( $pubdate ) {
			$publication_date->addAttribute('content', strftime('%Y/%m/%e', $pubdate));
		}

		$title_en = $xml->addChild('meta');
		$title_en->addAttribute('name', 'citation_title');
		$title_en->addAttribute('content', $object->getValue($this->web, 'titulo_en'));

		$title_es = $xml->addChild('meta');
		$title_es->addAttribute('name', 'citation_title');
		$title_es->addAttribute('content', $object->objectversion->Title);

		$doi = $xml->addChild('meta');
		$doi->addAttribute('name', 'citation_doi');
		$doi->addAttribute('content', $object->getValue($this->web, 'doi'));

		$num = 0;
		do {
			$author_name = $object->getValue($this->web, 'autores.nombres', $num);
			$author_lastname = $object->getValue($this->web, 'autores.apellidos', $num);
			if( empty($author_name) && empty($author_lastname) ) {
				break;
			}
			$author = $xml->addChild('meta');
			$author->addAttribute('name', 'citation_author');
			$author->addAttribute('content', $author_name . ', ' . $author_lastname);
			$num++;
		} while(true);

		$abstract = $xml->addChild('meta');
		$abstract->addAttribute('name', 'citation_abstract');
		$abstract_content = $object->getValue($this->web, 'resumen_en');
		if( empty($abstract_content) )
		{
			$num = 0;
			do {
				$abstract_title = $object->getValue($this->web, 'estructura_en.titulo', $num);
				if( empty($abstract_title) ) {
					break;
				}
				$abstract_content .= $abstract_title . ': ' . $object->getValue($this->web, 'estructura_en.texto', $num);
				$num++;
			} while(true);
		}
		$abstract->addAttribute('content', $abstract_content);

		$Keywords = $xml->addChild('meta');
		$Keywords->addAttribute('name', 'Keywords');
		$Keywords->addAttribute('content', $object->getValue($this->web, 'tags'));

		$public_url = $xml->addChild('meta');
		$public_url->addAttribute('name', 'citation_public_url');
		$public_url->addAttribute('content', 'http://doi.org/' . $object->getValue($this->web, 'doi'));

		$PDF_file_name = $xml->addChild('meta');
		$PDF_file_name->addAttribute('name', 'citation_PDF_file_name');
		$PDF_file_name->addAttribute('content', basename($object->getValue($this->web, 'pdf_en')));

		$copyright_statement = $xml->addChild('meta');
		$copyright_statement->addAttribute('name', 'citation_copyright_statement');
		$copyright_statement->addAttribute('content', 'This work is licensed under a Creative Commons Attribution 4.0 International License.');

		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Content-Type: text/xml; charset=UTF-8');

		echo $xml->asXML();
		
		exit;
	}

}

