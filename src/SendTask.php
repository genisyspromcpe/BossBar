<?php

use pocketmine\scheduler\PluginTask;

class SendTask extends PluginTask{

	/** @var UniqueBossBar */
	private $plugin;

	public function __construct(UniqueBossBar $owner){
		parent::__construct($owner);
		$this->plugin = $owner;
	}

	public function onRun($currentTick){
		$this->plugin->sendBossBar();
	}
}