<?php

namespace ScoreMC\ScoreHud;

use ScoreMC\ScoreMC;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TE;

class ScoreHud extends Task {

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
	public function getConfig() : Config {
		return new Config($this->plugin->getDataFolder() . 'config.yml');
	}

	/**
	* @param int $tick
	*/
	public function onRun($tick) : void {
		$worlds = $this->getConfig()->get("worlds", []);
		if (empty($worlds)) {
			$players = Server::getInstance()->getOnlinePlayers();
			$this->sendToPlayers($players);
		}else{
			foreach ($worlds as $world) {
				$level = Server::getInstance()->getLevelByName($world);
				if ($level instanceof Level) {
					$this->sendToPlayers($level->getPlayers());
				}
			}
		}
	}

	/**
	* @param array $players
	*/
	private function sendToPlayers(array $players) : void {
		foreach ($players as $player) {
			$this->sendScoreHud($player);
		}
	}

	/**
	* @param Player $player
	*/
	private function sendScoreHud(Player $player) : void {
		if (($scoreTemp = ScoreMC::getTempScore($player)) !== null) {
			if ($scoreTemp->isExpired()) {
				ScoreMC::removeTempScore($player);
			}else{
				return;
			}
		}
		$title = $this->getConfig()->get("titles");
		if (!empty($title)) {
			$title = $title[array_rand($title)];
		}else{
			$title = TE::GREEN."Server Title";
		}
		$messages = [];
		foreach ($this->getConfig()->get("lines", []) as $message) {
			$messages[] = $this->translateHud($player, $message);
		}
		ScoreMC::createScore($player, $this->translateHud($player, $title));
		ScoreMC::setScoreLines($player, $messages);
	}

	/**
	* @param Player $player
	* @param string $message
	* @return string
	*/
	private function translateHud(Player $player, string $message) : string {
		$playerx = $player->getFloorX();
		$playery = $player->getFloorY();
		$playerz = $player->getFloorZ();
		$onlineplayers = count(Server::getInstance()->getOnlinePlayers());
		$maxplayers = Server::getInstance()->getMaxPlayers();
		$itemname = $player->getInventory()->getItemInHand()->getName();
		$itemmeta = $player->getInventory()->getItemInHand()->getDamage();
		$itemid = $player->getInventory()->getItemInHand()->getId();
		$itemcount = $player->getInventory()->getItemInHand()->getCount();
		$tick = Server::getInstance()->getTickUsage();
		$tps = Server::getInstance()->getTicksPerSecond();
		$ping = $player->getPing();
		$world = $player->getLevel()->getFolderName();
		$motd = Server::getInstance()->getMotd();

		$rank = Server::getInstance()->getPluginManager()->getPlugin("PurePerms");
		$prefix = "???";
		$suffix = "???";
		if($rank !== null){
			$suffix = $rank->getUserDataMgr()->getNode($player, "suffix") ?? "???";
			$prefix = $rank->getUserDataMgr()->getNode($player, "prefix") ?? "???";
			$rank = $rank->getUserDataMgr()->getData($player)['group'] ?? "???";
		}else{
			$rank = "???";
		}

		$prisonrank = Server::getInstance()->getPluginManager()->getPlugin("RankUp");
		if($prisonrank !== null){
			$prisonrank = $rankUp->getRankUpDoesGroups()->getPlayerGroup($player) ?? "???";
		}else{
			$prisonrank = "???";
		}

		$money = Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");
		if($money !== null){
			$money = $money->myMoney($player);
		}else{
			$money = "???";
		}

		$faction = Server::getInstance()->getPluginManager()->getPlugin("FactionsPro");
		if($faction !== null){
			$faction = $faction->getPlayerFaction($player->getName()) ?? "???";
		}else{
			$faction = "???";
		}
		$colors = [TE::GREEN, TE::AQUA, TE::WHITE, TE::RED, TE::YELLOW, TE::GRAY, TE::BLUE, TE::GOLD];
		$message = str_replace("{player}", $player->getName(), $message);
		$message = str_replace("{item_name}", $itemname, $message);
		$message = str_replace("{item_id}", $itemid, $message);
		$message = str_replace("{item_meta}", $itemmeta, $message);
		$message = str_replace("{item_count}", $itemcount, $message);

		$message = str_replace("{x}", $playerx, $message);
		$message = str_replace("{y}", $playery, $message);
		$message = str_replace("{z}", $playerz, $message);

		$message = str_replace("{online}", $onlineplayers, $message);
		$message = str_replace("{players}", $maxplayers, $message);
		$message = str_replace("{tick}", $tick, $message);
		$message = str_replace("{tps}", $tps, $message);
		$message = str_replace("{world}", $world, $message);
		$message = str_replace("{ping}", $ping, $message);
		$message = str_replace("{motd}", $motd, $message);

		$message = str_replace("{prefix}", $prefix, $message);
		$message = str_replace("{suffix}", $suffix, $message);
		$message = str_replace("{faction}", $faction, $message);
		$message = str_replace("{prison_rank}", $prisonrank, $message);
		$message = str_replace("{rank}", $rank, $message);
		$message = str_replace("{money}", $money, $message);
		$message = str_replace("{rainbow}", ($colors[array_rand($colors)]), $message);
		return $message;
	}
}