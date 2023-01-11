<?php

/**
 * Plugins model
 *
 */

class Plugins extends \Phalcon\Mvc\Model
{
	var $main;

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

	function getMain() {
		if( is_null($this->main) ) {
			include_once($this->getDI()->getConfig()->application->pluginsDir . $this->Code . '.php');
			$this->main = new $this->Code;
		}
		return $this->main;
	}

	function import_formats() {
		return $this->getMain()->import_formats();
	}

	function import($folder, $type, $format, $content, $user) {
		return $this->getMain()->import($folder, $type, $format, $content, $user);
	}
}
