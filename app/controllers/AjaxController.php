<?php
use Phalcon\Http\Request;
use Phalcon\Mvc\Controller;

use App\Forms\DataForm;
use App\Forms\GroupsForm;

class AjaxController extends ControllerBase
{

	public function initialize()
	{
		parent::initialize();
	}

	public function upAction()
	{
		if(isset($_REQUEST["t"]) && $_REQUEST["t"] != "" && isset($_REQUEST["g"]) && $_REQUEST["g"] != "" && isset($_REQUEST["d"]) && $_REQUEST["d"] != "")
		{
			$type = \Types::findFirst($_REQUEST["t"]);
			if(isset($type->Id))
			{
				$group = \TypesGroups::findFirst($_REQUEST["g"]);
				if(isset($group->Id))
				{
					$data = \TypesData::findFirst([
								'conditions' => 'Id = :id: and Types_Id = :type: and TypesGroups_Id = :group:', 
								'bind'       => ['id' => $_REQUEST["d"], 'type' => $type->Id, 'group' => $group->Id]
							]);


					$aux = \TypesData::findFirst([
								'conditions' => 'Id != :id: and Types_Id = :type: and TypesGroups_Id = :group: and Position < :position: and Deleted = "no"', 
								'bind'       => ['id' => $_REQUEST["d"], 'type' => $type->Id, 'group' => $group->Id, 'position' => $data->Position],
								'order'      => 'Position desc'
							]);

					if(isset($aux->Id))
					{
						$position_up   = $data->Position;
						$position_down = $aux->Position;

						$data->Position = $position_down;
						$aux->Position  = $position_up;
						if($data->save() && $aux->save())
						{
							echo '[{"exito": "1", "aux" : "'.$aux->Id.'"}]';
						}
						else
						{
							echo '[{"exito": "0"}]';
						}
					}
					else
					{
						echo '[{"exito": "0"}]';
					}
				}
				else
				{
					echo '[{"exito": "0"}]';
				}
			}
			else
			{
				echo '[{"exito": "0"}]';
			}
		}
		else
		{
			echo '[{"exito": "0"}]';
		}
		return false;
	}

	public function downAction()
	{
		if(isset($_REQUEST["t"]) && $_REQUEST["t"] != "" && isset($_REQUEST["g"]) && $_REQUEST["g"] != "" && isset($_REQUEST["d"]) && $_REQUEST["d"] != "")
		{
			$type = \Types::findFirst($_REQUEST["t"]);
			if(isset($type->Id))
			{
				$group = \TypesGroups::findFirst($_REQUEST["g"]);
				if(isset($group->Id))
				{
					$data = \TypesData::findFirst([
								'conditions' => 'Id = :id: and Types_Id = :type: and TypesGroups_Id = :group:', 
								'bind'       => ['id' => $_REQUEST["d"], 'type' => $type->Id, 'group' => $group->Id]
							]);


					$aux = \TypesData::findFirst([
								'conditions' => 'Id != :id: and Types_Id = :type: and TypesGroups_Id = :group: and Position > :position: and Deleted = "no"', 
								'bind'       => ['id' => $_REQUEST["d"], 'type' => $type->Id, 'group' => $group->Id, 'position' => $data->Position],
								'order'      => 'Position asc'
							]);

					if(isset($aux->Id))
					{
						$position_down = $aux->Position;
						$position_up   = $data->Position;

						$data->Position = $position_down;
						$aux->Position  = $position_up;
						if($data->save() && $aux->save())
						{
							echo '[{"exito": "1", "aux" : "'.$aux->Id.'"}]';
						}
						else
						{
							echo '[{"exito": "0"}]';
						}
					}
					else
					{
						echo '[{"exito": "0"}]';
					}
				}
				else
				{
					echo '[{"exito": "0"}]';
				}
			}
			else
			{
				echo '[{"exito": "0"}]';
			}
		}
		else
		{
			echo '[{"exito": "0"}]';
		}
		return false;
	}

}