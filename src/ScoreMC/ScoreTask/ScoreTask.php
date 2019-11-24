<?php

namespace ScoreMC\ScoreTask;

use ScoreMC\ScoreMC;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TE;

class ScoreTask extends Task {

	/** @var ScoreMC */
	private $plugin;

	/**
	* @param ScoreMC $plugin
	*/
	public function __construct(ScoreMC $plugin) {
		$this->plugin = $plugin;
	}

	/**
	* @return Config
	*/
	public function getConfig() : Config{
		return new Config($this->plugin->getDataFolder() . 'config.yml');
	}

	/**
	* @param int $tick
	*/
	public function onRun($tick) : void{
		$worlds = $this->getConfig()->get("worlds", []);

		if (empty($worlds)) {
			$this->prepareHud(Server::getInstance()->getOnlinePlayers(), $this->getConfig()->get('default', []));
		}else{
			foreach ($worlds as $world => $title) {
				$level_world = Server::getInstance()->getLevelByName($world);
				if ($level_world instanceof Level) {
					$this->prepareHud($level_world->getPlayers(), $title);
				}
			}
		}
	}

	/**
	* @param array $players
	* @param array $config_title
	*/
	public function prepareHud(array $players, array $config_title) : void{
		foreach ($players as $player) {
			$this->broadcastHud($player, $config_title['title'], $config_title['lines']);
		}
	}

	/**
	* @param Player $player
	* @param array $titles
	* @param array $lines
	*/
	public function broadcastHud(Player $player, array $titles, array $lines) : void{
		$title = $this->getTitle($titles);
		$this->plugin->createScore($player, $this->plugin->translate($player, $title));
		$this->plugin->setScoreLines($player, $lines, true);
	}

	/**
	* @param array $titles
	* @return string
	*/
	public function getTitle(array $titles) : ?string{
		shuffle($titles);
		shuffle($titles);
		return array_shift($titles);
	}
}