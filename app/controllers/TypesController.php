<?php
use Phalcon\Http\Request;
use Phalcon\Mvc\Controller;

use App\Forms\DataForm;
use App\Forms\GroupsForm;

class TypesController extends ControllerBase
{

	public function initialize()
	{
		parent::initialize();
		$this->tag->setTitle('Gestión de tipos de contenido');
	}

	public function indexAction()
	{
		if( $this->request->has('class') )
		{
			$this->view->types = Types::find(['conditions' => 'Class = :class: and TypeStatus != :status:', 'bind' => ['class' => $_REQUEST["class"], 'status' => 'deleted']]);
		}
		else
		{
			$this->view->types = Types::find();
		}

		$this->view->classes = Types::$classes;
		$this->view->total   = Types::getTotal(); //Todo pasar si se quiere los eliminados o no
	}

	public function editAction()
	{

	}

	public function dataAction($id = null)
	{
		if($id)
		{
			$type = \Types::findFirst($id);
			if(!isset($type->Id))
			{
				$this->flashSession->error('Tipo no encontrado.');
				$this->response->redirect($this->url->get('types'));
			}
		}
		else
		{
			$this->flashSession->error('Debe especificar tipo.');
			$this->response->redirect($this->url->get('types'));
		}

		$this->view->type   = $type;

		$this->view->groups = \TypesGroups::find(
			[
				'conditions' => 'Types_Id = :type:',
				'bind'       => ['type' => $type->Id],
				'order'      => 'Position asc',
			]
		);

		$this->view->typesdata = \TypesData::find(
			[
				'conditions' => "TypesGroups_Id is null and Name not like '%.%' and Types_Id = :type:",
				'bind' => [
					'type' => $type->Id
				],
				'order' => 'Position asc'
			]
		);
	}

	public function groupAction($id = null)
	{
		if($id)
        {
            $type = \Types::findFirst($id);
            if(!isset($type->Id))
            {
                $this->flashSession->error('Tipo no encontrado.');
                $this->response->redirect($this->url->get('types'));
            }
        }
        else
        {
            $this->flashSession->error('Debe especificar tipo.');
            $this->response->redirect($this->url->get('types'));
        }

        if(isset($_REQUEST["g"]) && $_REQUEST["g"] != "")
        {
        	$group = \TypesGroups::findFirst($_REQUEST["g"]);
        	if(!isset($group->Id))
        	{
        		$this->flashSession->error('Grupo no encontrado.');
                $this->response->redirect($this->url->get('types/data/'.$type->Id));
        	}
        }
        elseif(isset($_REQUEST["Id"]) && $_REQUEST["Id"] != "")
        {
        	$group = \TypesGroups::findFirst($_REQUEST["Id"]);
        	if(!isset($group->Id))
        	{
        		$this->flashSession->error('Grupo no encontrado.');
                $this->response->redirect($this->url->get('types/data/'.$type->Id));
        	}
        }
        else
        {
        	$sql = "select Position 
					from TypesGroups 
					where Types_Id = '".$type->Id."' 
					order by Position desc
					limit 1";
            $position = $this->db->fetchOne($sql);

        	$group = new \TypesGroups();
			$group->Types_Id = $type->Id;
			$group->Position = $position["Position"] + 1;
        }

        $form  = new GroupsForm($group);

        if(isset($_REQUEST["save"]))
        {
        	$data = $this->request->getPost();
			if (!$form->isValid($data, $group))
			{
				foreach ($form->getMessages() as $message)
				{
					$this->flash->error((string)$message);
				}
			}
			else
			{
				if (!$group->save())
				{
					foreach ($group->getMessages() as $message)
					{
						$this->flash->error((string)$message);
					}
				}
				else
				{
					$form->clear();
					$this->flash->success('Grupo guardado');
					$this->response->redirect($this->url->get('types/data/'.$type->Id));
				}
			}
        }
        elseif(isset($_REQUEST["delete"]))
        {
        	if(count($group->getTypesData()) > 0)
        	{
				$this->flash->error('No se puede eliminar el grupo, ya que tiene campos asociados.');
        	}
        	else
        	{
	    		if($group->delete())
				{
					$this->flash->success('Grupo eliminado.');
					$this->response->redirect($this->url->get('types/data/'.$type->Id));
				}	
        	}
        }

		$this->view->type  = $type;
		$this->view->form  = $form;
		$this->view->group = $group;
	}

	public function typedataAction($id = null)
	{
		if($id)
        {
            $type = \Types::findFirst($id);
            if(!isset($type->Id))
            {
                $this->flashSession->error('Tipo no encontrado.');
                $this->response->redirect($this->url->get('types'));
            }
        }
        else
        {
            $this->flashSession->error('Debe especificar tipo.');
            $this->response->redirect($this->url->get('types'));
        }

        if(isset($_REQUEST["d"]) && $_REQUEST["d"] != "")
        {
        	$data = \TypesData::findFirst($_REQUEST["d"]);
        	if(!isset($data->Id))
        	{
        		$this->flashSession->error('Campo no encontrado.');
                $this->response->redirect($this->url->get('types/data/'.$type->Id));
        	}
        }
        elseif(isset($_REQUEST["Id"]) && $_REQUEST["Id"] != "")
        {
        	$data = \TypesData::findFirst($_REQUEST["Id"]);
        	if(!isset($data->Id))
        	{
        		$this->flashSession->error('Campo no encontrado.');
                $this->response->redirect($this->url->get('types/data/'.$type->Id));
        	}
        }
        else
        {
        	$data = new \TypesData();
			$data->Types_Id = $type->Id;
        }

        $form = new DataForm($data);

        if(isset($_REQUEST["save"]))
        {
        	$request = $this->request->getPost();
			if (!$form->isValid($request, $data))
			{
				foreach ($form->getMessages() as $message)
				{
					$this->flash->error((string)$message);
				}
			}
			else
			{
				if(isset($_REQUEST['TypesGroups_Id']) && $_REQUEST['TypesGroups_Id'] != "")
				{
					if(!isset($data->Position))
					{
						$sql = "select Position 
								from TypesData 
								where Types_Id = '".$type->Id."' 
								and TypesGroups_Id = '".$_REQUEST['TypesGroups_Id']."'
								order by Position desc
								limit 1";
			            $position = $this->db->fetchOne($sql);

			            $data->Position = $position["Position"] + 1;
					}

					$data->TypesGroups_Id = $_REQUEST['TypesGroups_Id'];
				}
				if(isset($_REQUEST['TypesElements_Code']) && $_REQUEST['TypesElements_Code'] != "")
				{
					$data->TypesElements_Code = $_REQUEST['TypesElements_Code'];
				}

				if (!$data->save())
				{
					foreach ($data->getMessages() as $message)
					{
						$this->flash->error((string)$message);
					}
				}
				else
				{
					$form->clear();
					$this->flash->success('Campo guardado');
					$this->response->redirect($this->url->get('types/data/'.$type->Id));
				}
			}
        }
        elseif(isset($_REQUEST["delete"]))
        {
    		$data->Deleted = 'yes';
    		if($data->save())
			{
				$this->flash->success('Campo eliminado.');
				$this->response->redirect($this->url->get('types/data/'.$type->Id));
			}	
        }

        $groups = \TypesGroups::find(
    			[
					'conditions' => 'Types_Id = :type:',
					'bind'       => ['type' => $type->Id],
					'order'      => 'Position asc',
    			]
    		);

        $elements = \TypesElements::find(
    			[
					'conditions' => 'ElementStatus = :status:',
					'bind'       => ['status' => 'active'],
					'order'      => 'Name asc',
    			]
    		);

		$this->view->type     = $type;
		$this->view->data     = $data;
		$this->view->form     = $form;
		$this->view->groups   = $groups;
		$this->view->elements = $elements;
	}
}