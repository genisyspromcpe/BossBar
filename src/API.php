<?php

use pocketmine\entity\Entity;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\BossEventPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\network\protocol\UpdateAttributesPacket;
use pocketmine\Player;
use pocketmine\Server;

class API{

	/**
	 * @param Player[]
	 * @param $title
	 * @return int|null
	 */
	public static function addBossBar($players, $title){
		if(empty($players)) return null;

		$eid = Entity::$entityCount++;

		$packet = new AddEntityPacket();
		$packet->eid = $eid;
		$packet->type = 52;
		$packet->yaw = 0;
		$packet->pitch = 0;
		$packet->metadata = [Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1], Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE], Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title], Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0], Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]];
		foreach($players as $player){
			$pk = clone $packet;
			$pk->x = $player->x;
			$pk->y = $player->y - 10;
			$pk->z = $player->z;
			$player->dataPacket($pk);
		}

		$bpk = new BossEventPacket();
		$bpk->eid = $eid;
		$bpk->state = 0;
		Server::getInstance()->broadcastPacket($players, $bpk);

		return $eid;
	}

	/**
	 * @param Player $player
	 * @param        $eid
	 * @param        $title
	 */
	public static function sendBossBarToPlayer(Player $player, $eid, $title){
		$packet = new AddEntityPacket();
		$packet->eid = $eid;
		$packet->type = 52;
		$packet->yaw = 0;
		$packet->pitch = 0;
		$packet->metadata = [Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1], Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE], Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title], Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0], Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]];
		$packet->x = $player->x;
		$packet->y = $player->y - 10;
		$packet->z = $player->z;
		$player->dataPacket($packet);

		$bpk = new BossEventPacket();
		$bpk->eid = $eid;
		$bpk->state = 0;
		$player->dataPacket($bpk);
	}

	/**
	 * @param $percentage
	 * @param $eid
	 */
	public static function setPercentage($percentage, $eid){
		if(!count(Server::getInstance()->getOnlinePlayers()) > 0) return;

		$upk = new UpdateAttributesPacket();
		$upk->entries[] = new BossBarValues(0, 300, max(0.5, min([$percentage, 100])) / 100 * 300, 'minecraft:health');
		$upk->entityId = $eid;
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $upk);

		$bpk = new BossEventPacket();
		$bpk->eid = $eid;
		$bpk->state = 0;
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $bpk);
	}

	/**
	 * @param $title
	 * @param $eid
	 */
	public static function setTitle($title, $eid){
		if(!count(Server::getInstance()->getOnlinePlayers()) > 0) return;

		$npk = new SetEntityDataPacket();
		$npk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title]];
		$npk->eid = $eid;
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $npk);

		$bpk = new BossEventPacket();
		$bpk->eid = $eid;
		$bpk->state = 0;
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $bpk);
	}

	/**
	 * @param Player[]
	 * @param $eid
	 * @return bool
	 */
	public static function removeBossBar($players, $eid){
		if(empty($players)) return false;

		$pk = new RemoveEntityPacket();
		$pk->eid = $eid;
		Server::getInstance()->broadcastPacket($players, $pk);
		return true;
	}
}