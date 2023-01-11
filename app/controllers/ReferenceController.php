<?php
use Phalcon\Mvc\View;

class ReferenceController extends ControllerBase
{
	public function indexAction()
	{
		$q = $this->request->get('q');
		$class = $this->request->get('c');

		$data = [];
		
		$paginator = Objects::searchObjects($q, $class, null, null, null, 1, 10);
		if( $paginator && $paginator->hasItems() )
		{
			foreach($paginator->getItems() as $object)
			{
				$data[] = [
					'value' => $object->Id,
					'label' => $object->objectversion->Title
				];
			}
		}
		
		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');
		$this->response->setContent(json_encode($data));
		return $this->response;
		exit;
		
	}
}
