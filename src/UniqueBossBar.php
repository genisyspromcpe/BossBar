<?php

use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class UniqueBossBar extends PluginBase implements Listener{

	public $entityRuntimeId = null, $messages = [], $time;

	/** @var API */
	public $api;

	public function onEnable(){
		if(!is_dir($this->getDataFolder())){
			mkdir($this->getDataFolder());
		}
		$this->saveDefaultConfig();
		$this->messages = $this->getConfig()->getAll()["messages"];
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new SendTask($this), 20 * 11);
	}

	public function onPlayerJoin(PlayerJoinEvent $ev){
		if($this->entityRuntimeId === null){
			$this->entityRuntimeId = API::addBossBar([$ev->getPlayer()], "Загрузка..");
		}else{
			API::sendBossBarToPlayer($ev->getPlayer(), $this->entityRuntimeId, $this->getText($ev->getPlayer()));
		}
	}

	public function onLevelChange(EntityLevelChangeEvent $ev){
		if($ev->isCancelled() || !$ev->getEntity() instanceof Player) return;
		if($this->entityRuntimeId === null){
			$this->entityRuntimeId = API::addBossBar([$ev->getEntity()], 'Загрузка..');
		}else{
			API::removeBossBar([$ev->getEntity()], $this->entityRuntimeId);
			API::sendBossBarToPlayer($ev->getEntity(), $this->entityRuntimeId, $this->getText($ev->getEntity()));
		}
	}

	public function sendBossBar(){
		if ($this->entityRuntimeId === null) return;
		foreach($this->getServer()->getOnlinePlayers() as $player){
			API::setTitle($this->getText($player), $this->entityRuntimeId);
		}
	}

	public function getText(Player $player){
		$message = current($this->messages);
		if(!is_string($message)) {
			reset($this->messages);
			$message = current($this->messages);
		}
		API::setTitle($message, $this->entityRuntimeId);
		$this->time = time();
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new PercentageTask($this), 20);
		next($this->messages);
		return $message;
	}
}