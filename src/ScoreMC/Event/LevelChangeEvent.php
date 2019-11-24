<?php

namespace ScoreMC\Event;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityLevelChangeEvent;

class LevelChangeEvent implements Listener{

	/** @var ScoreMC $plugin */
	private $plugin;

	/**
	* @param ScoreMC $plugin
	*/
	public function __construct($plugin){
		$this->plugin = $plugin;
	}

	/**
	* @param BlockBreakEvent $event
	*/
	public function onChange(EntityLevelChangeEvent $event) {
		$player = $event->getEntity();
		if ($player instanceof Player) {
			$this->plugin->removeScore($player);
		}
	}
}