<?php

use pocketmine\scheduler\PluginTask;

class PercentageTask extends PluginTask{

	/** @var UniqueBossBar */
	private $plugin;

	public function __construct(UniqueBossBar $owner){
		parent::__construct($owner);
		$this->plugin = $owner;
	}

	public function onRun($currentTick){
		$seconds = time() - $this->plugin->time;
		API::setPercentage($seconds * 10, $this->plugin->entityRuntimeId);
		if($seconds >= 10){
			$this->plugin->getServer()->getScheduler()->cancelTask($this->getTaskId());
		}
	}
}