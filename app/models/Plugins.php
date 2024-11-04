<?php

/**
 * Plugins model
 *
 */


class Plugins extends \Phalcon\Mvc\Model
{
	private $isloaded = false;
	public $main = null;
	protected $di;

	public function initialize()
	{
		$this->setSource("Plugins");
		$this->hasMany('Code', 'PluginsRegisters', 'Plugins_Code', ['alias' => 'Registers']);
	}

	static function getRegisterFor($register) {
		return Plugins::query()->innerJoin('PluginsRegisters', 'PluginsRegisters.Plugins_Code = Plugins.Code')
			->where("Plugins.PluginStatus = 'enabled'")
			->andwhere("PluginsRegisters.Register = :register:")
			->bind(['register' => $register])
			->execute();
	}

	function load(\Phalcon\DI $di)
	{
		$file = $di->getConfig()->application->pluginsDir . strtolower($this->Code) . '/' . strtolower($this->Code) . '.php';
		if( !file_exists($file) ) {
			return false;
		}
		include_once($file);
		$this->main = new $this->Code($di);
		$this->isloaded = true;
		return true;
	}

	function action($action, $params = []) {
		if( is_null($this->main) ) {
			return false;
		}
		if( $action != 'init' ) {
			$action = 'action_' . $action;
		}
		if( method_exists($this->main, $action) ) {
			return call_user_func_array([$this->main, $action], [0 => $params]);
		}
	}

	function getMain() {
		return $this->main;
	}

	function import_formats() {
		return $this->getMain()->import_formats();
	}

	function import($folder, $type, $format, $content, $user) {
		return $this->getMain()->import($folder, $type, $format, $content, $user);
	}
}
